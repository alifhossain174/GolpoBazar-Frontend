<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;

class BkashPaymentController extends Controller
{
    public function getToken(){

        $paymentGateways = DB::table('payment_gateways')->get();

        $request_data = array(
            'app_key' => $paymentGateways[2]->api_key,
            'app_secret' => $paymentGateways[2]->secret_key,
        );

        $sandboxApiEndPoint = "https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/token/grant";
        $productionApiEndPoint = "https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/token/grant";

        $url = curl_init($productionApiEndPoint);
        $request_data_json=json_encode($request_data);
        $header = array(
            'Content-Type:application/json',
            'username:'.$paymentGateways[2]->username,
            'password:'.$paymentGateways[2]->password,
        );
        curl_setopt($url,CURLOPT_HTTPHEADER, $header);
        curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url,CURLOPT_POSTFIELDS, $request_data_json);
        curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $response = curl_exec($url);
        curl_close($url);
        $result = json_decode($response);
        $token = $result->id_token;
        session(['bkash_token' => $token]);

        return redirect('/create/agreement');
    }

    public function createAgreement(){

        $paymentGateways = DB::table('payment_gateways')->get();
        $mode = '0000';
        $payerReference = '01';
        $callbackURL = url('/execute/agreement');

        $createagreementbody=array(
            'payerReference' => $payerReference,
            'callbackURL' => $callbackURL,
            'mode' => $mode,
        );

        $sandboxApiEndPoint = "https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/create";
        $productionApiEndPoint = "https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/create";

        $url=curl_init($productionApiEndPoint);
        $createagreementbodyx=json_encode($createagreementbody);
        $header=array(
            'Content-Type:application/json',
            'authorization:'.session('bkash_token'),
            'x-app-key:'.$paymentGateways[2]->api_key
        );

        curl_setopt($url,CURLOPT_HTTPHEADER, $header);
        curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url,CURLOPT_POSTFIELDS, $createagreementbodyx);
        curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $resultdata=curl_exec($url);
        $obj = json_decode($resultdata);
        curl_close($url);

        $paymentID = $obj->paymentID;
        session(['paymentID' => $paymentID]);
        $bkashURL = $obj->bkashURL;
        return redirect($bkashURL);
    }

    public function executeAgreement(Request $request){

        if(isset($request->status) && $request->status == 'cancel'){
            Toastr::error('Failed to Place Order', 'Failed in bKash Payment');
            return redirect('view/cart');
        }

        $paymentGateways = DB::table('payment_gateways')->get();
        $requestbody = array(
            'paymentID' => session('paymentID')
        );

        $sandboxApiEndPoint = "https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/execute";
        $productionApiEndPoint = "https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/execute";

        $url = curl_init($productionApiEndPoint);
        $requestbodyJson = json_encode($requestbody);

        $header = array(
            'Content-Type:application/json',
            'authorization:'.session('bkash_token'),
            'x-app-key:'.$paymentGateways[2]->api_key
        );

        curl_setopt($url,CURLOPT_HTTPHEADER, $header);
        curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $requestbodyJson);
        curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
        $resultdatax=curl_exec($url);
        $obj = json_decode($resultdatax);
        curl_close($url);

        $paymentID = $obj->paymentID;
        $agreementID = $obj->agreementID;

        session(['paymentID' => $paymentID]);
        session(['agreementID' => $agreementID]);

        return redirect('create/payment');
    }

    public function createPayment(){

        $agreementID = session('agreementID');
        $auth = session('bkash_token');
        $callbackURL= url('/execute/payment');
        $orderInfo = DB::table('orders')->where('id', session('order_id'))->first();
        $paymentGateways = DB::table('payment_gateways')->get();

        $requestbody = array(
            'agreementID' => $agreementID,
            'mode' => '0001',
            'amount' => $orderInfo->total,
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => 'commonPayment001',
            'callbackURL' => $callbackURL
        );

        $sandboxApiEndPoint = "https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/create";
        $productionApiEndPoint = "https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/create";

        $url = curl_init($productionApiEndPoint);
        $requestbodyJson = json_encode($requestbody);
        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $auth,
            'X-APP-Key:'.$paymentGateways[2]->api_key
        );

        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $requestbodyJson);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);
        curl_close($url);
        $obj = json_decode($resultdata);

        $paymentID = $obj->paymentID;
        session(['paymentID' => $paymentID]);

        $bkashURL = $obj->bkashURL;
        return redirect($bkashURL);
    }

    public function executePayment(){

        $paymentID = session('paymentID');
        $auth = session('bkash_token');
        $paymentGateways = DB::table('payment_gateways')->get();

        $request_body = array(
            'paymentID' => $paymentID
        );

        $sandboxApiEndPoint = "https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/execute";
        $productionApiEndPoint = "https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/execute";

        $url = curl_init($productionApiEndPoint);

        $request_body_json = json_encode($request_body);
        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $auth,
            'X-APP-Key:'.$paymentGateways[2]->api_key
        );
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $request_body_json);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);
        $obj = json_decode($resultdata);
        curl_close($url);


        if($obj->statusMessage == 'Successful'){

            DB::table('orders')->where('id', session('order_id'))->update([
                'payment_method' => 2,
                'payment_status' => 1,
                'order_status' => 1,
                'updated_at' => Carbon::now()
            ]);

            DB::table('order_progress')->insert([
                'order_id' => session('order_id'),
                'order_status' => 1,
                'created_at' => Carbon::now()
            ]);

            $orderInfo = DB::table('orders')->where('id', session('order_id'))->first();
            DB::table('order_payments')->insert([
                'order_id' => $orderInfo->id,
                'payment_through' => "bkash",
                'tran_id' => $orderInfo->trx_id,
                'val_id' => NULL,
                'amount' => $orderInfo->total,
                'card_type' => NULL,
                'store_amount' => $orderInfo->total,
                'card_no' => NULL,
                'status' => "VALID",
                'tran_date' => date("Y-m-d H:i:s"),
                'currency' => "BDT",
                'card_issuer' => NULL,
                'card_brand' => NULL,
                'card_sub_brand' => NULL,
                'card_issuer_country' => NULL,
                'created_at' => Carbon::now()
            ]);

            CheckoutController::sendOrderSms(session('customer_phone'), $orderInfo);

            session()->forget('coupon');
            session()->forget('discount');
            session()->forget('delivery_cost');
            session()->forget('cart');
            // bkash session
            session()->forget('order_id');
            session()->forget('customer_phone');

            return redirect('order/'.$orderInfo->slug);

        } else {

            $orderInfo = DB::table('orders')->where('id', session('order_id'))->first();
            DB::table('order_payments')->insert([
                'order_id' => $orderInfo->id,
                'payment_through' => "COD",
                'tran_id' => $orderInfo->trx_id,
                'val_id' => NULL,
                'amount' => $orderInfo->total,
                'card_type' => NULL,
                'store_amount' => $orderInfo->total,
                'card_no' => NULL,
                'status' => "VALID",
                'tran_date' => date("Y-m-d H:i:s"),
                'currency' => "BDT",
                'card_issuer' => NULL,
                'card_brand' => NULL,
                'card_sub_brand' => NULL,
                'card_issuer_country' => NULL,
                'created_at' => Carbon::now()
            ]);

            CheckoutController::sendOrderSms(session('customer_phone'), $orderInfo);

            session()->forget('coupon');
            session()->forget('discount');
            session()->forget('delivery_cost');
            session()->forget('cart');
            // bkash session
            session()->forget('order_id');
            session()->forget('customer_phone');

            Toastr::error('Order Placed under Cash On Delivery', 'Failed in bKash Payment');
            return redirect('order/'.$orderInfo->slug);
        }
    }
}
