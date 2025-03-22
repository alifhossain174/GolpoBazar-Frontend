<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function placeOrder(Request $request){

        if(!session('cart') || (session('cart') && count(session('cart')) <= 0)){
            Toastr::error('No Products Found in Checkout', 'Failed to Place Order');
            return redirect('/');
        }

        // common tasks to do for every order (not dependent on any payment gateway)
        date_default_timezone_set("Asia/Dhaka");
        $total = 0;
        foreach((array) session('cart') as $id => $details){
            if($details['discount_price'] > 0 && $details['discount_price'] < $details['price']){
                $total += $details['discount_price'] * $details['quantity'];
            } else {
                $total += $details['price'] * $details['quantity'];
            }
        }
        $discount = session('discount') ? session('discount') : 0;
        $deliveryCost = session('delivery_cost') ? session('delivery_cost') : 0;
        $couponCode = session('coupon') ? session('coupon') : null;


        $orderId = DB::table('orders')->insertGetId([
            'order_no' => time().rand(100,999),
            'user_id' => auth()->user()->id,
            'order_date' => date("Y-m-d H:i:s"),
            'estimated_dd' => date('Y-m-d', strtotime("+7 day", strtotime(date("Y-m-d")))),
            'payment_method' => $request->payment_method == 'sslcommerz' ? 4 : 2,
            'payment_status' => 0,
            'trx_id' => time().str::random(5),
            'order_status' => 0,
            'sub_total' => $total,
            'coupon_code' => $couponCode,
            'discount' => $discount,
            'delivery_fee' => $deliveryCost,
            'vat' => 0,
            'tax' => 0,
            'total' => $total + $deliveryCost - $discount,
            'order_note' => '',
            'delivery_method' => null,
            'slug' => str::random(5) . time(),
            'created_at' => Carbon::now()
        ]);

        DB::table('order_progress')->insert([
            'order_id' => $orderId,
            'order_status' => 0,
            'created_at' => Carbon::now()
        ]);

        foreach (session('cart') as $id => $details) {
            $productInfo = DB::table('products')->where('id', $id)->first();
            if ($productInfo->stock >= $details['quantity']) {
                DB::table('order_details')->insert([
                    'order_id' => $orderId,
                    'product_id' => $id,

                    // VARIANT
                    'color_id' => null,
                    'region_id' => null,
                    'sim_id' => null,
                    'size_id' => null,
                    'storage_id' => null,
                    'warrenty_id' => null,
                    'device_condition_id' => null,

                    'qty' => $details['quantity'],
                    'unit_id' => $productInfo->unit_id,
                    'unit_price' => $details['discount_price'] > 0 ? $details['discount_price'] : $details['price'],
                    'total_price' => ($details['discount_price'] > 0 ? $details['discount_price'] : $details['price']) * $details['quantity'],
                    'created_at' => Carbon::now()
                ]);
            }
        }

        if ($request->payment_method == 'sslcommerz') {
            session([
                'order_id' => $orderId,
                'customer_name' => Auth::user()->name,
                'customer_email' => Auth::user()->phone,
            ]);
            return redirect('sslcommerz/order');
        }

        if($request->payment_method == 'bkash'){
            session([
                'order_id' => $orderId,
                'customer_phone' => Auth::user()->phone,
            ]);
            return redirect('get/token');
        }

    }

    public static function sendOrderSms($phone, $orderInfo){
        if($phone){

            $orderSmsString = "Dear Customer, Your Order #".$orderInfo->order_no." placed successfully at ".env('APP_NAME').". Total amount: ".$orderInfo->total."TK";

            $smsGateway = DB::table('sms_gateways')->where('status', 1)->first();
            if($smsGateway && $smsGateway->provider_name == 'Reve'){
                Http::get($smsGateway->api_endpoint, [
                    'apikey' => $smsGateway->api_key,
                    'secretkey' => $smsGateway->secret_key,
                    "callerID" => $smsGateway->sender_id,
                    "toUser" => $phone,
                    "messageContent" => $orderSmsString
                ]);
            }
            if($smsGateway && $smsGateway->provider_name == 'ElitBuzz'){
                Http::get($smsGateway->api_endpoint, [
                    'api_key' => $smsGateway->api_key,
                    "type" => "text",
                    "contacts" => $phone,
                    "senderid" => $smsGateway->sender_id,
                    "msg" => $orderSmsString
                ]);
            }
        }
    }
}
