<?php

namespace App\Http\Controllers;

use DGvai\SSLCommerz\SSLCommerz;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function order()
    {
        //  DO YOU ORDER SAVING PROCESS TO DB OR ANYTHING
        $orderData = DB::table('orders')->where('id', session('order_id'))->first();
        $sslc = new SSLCommerz();
        $sslc->amount($orderData->total)
            ->trxid($orderData->trx_id)
            ->product('Products From Ecommerce')
            ->customer(session('customer_name'), session('customer_email'));
        return $sslc->make_payment();

        /**
         *
         *  USE:  $sslc->make_payment(true) FOR CHECKOUT INTEGRATION
         *
         * */
    }

    public function success(Request $request)
    {
        $validate = SSLCommerz::validate_payment($request);
        if ($validate) {
            $bankID = $request->bank_tran_id;   //  KEEP THIS bank_tran_id FOR REFUNDING ISSUE
            //  Do the rest database saving works
            //  take a look at dd($request->all()) to see what you need

            $orderInfo = DB::table('orders')->where('trx_id', $request->tran_id)->first();

            DB::table('orders')->where('id', $orderInfo->id)->update([
                'payment_method' => 4,
                'payment_status' => 1,
                'order_status' => 1
            ]);

            DB::table('order_progress')->insert([
                'order_id' => $orderInfo->id,
                'order_status' => 1,
                'created_at' => Carbon::now()
            ]);

            DB::table('order_payments')->insert([
                'order_id' => $orderInfo->id,
                'payment_through' => "SSLCommerz",
                'tran_id' => $orderInfo->trx_id,
                'bank_tran_id' => $bankID,
                'val_id' => $request->val_id,
                'amount' => $orderInfo->total,
                'card_type' => $request->card_type,
                'store_amount' => $orderInfo->total,
                'card_no' => $request->card_no,
                'status' => "VALID",
                'tran_date' => date("Y-m-d H:i:s"),
                'currency' => "BDT",
                'card_issuer' => $request->card_issuer,
                'card_brand' => $request->card_brand,
                'card_sub_brand' => $request->card_sub_brand,
                'card_issuer_country' => $request->card_issuer_country,
                'created_at' => Carbon::now()
            ]);

            $userInfo = DB::table('users')->where('id', $orderInfo->user_id)->first();
            if($userInfo && $userInfo->phone){
                CheckoutController::sendOrderSms($userInfo->phone, $orderInfo);
            }

            session()->forget('customer_email');
            session()->forget('customer_name');
            session()->forget('coupon');
            session()->forget('discount');
            session()->forget('delivery_cost');
            session()->forget('cart');
            session()->forget('order_data');
            return redirect('order/'.$orderInfo->slug);

        }
    }

    public function failure(Request $request)
    {
        //  do the database works
        //  also same goes for cancel()
        //  for IPN() you can leave it untouched or can follow
        //  official documentation about IPN from SSLCommerz Panel
        Toastr::error('Something went wrong');
        return redirect('/checkout');
    }

    public function cancel(Request $request)
    {
        //  do the database works
        //  also same goes for cancel()
        //  for IPN() you can leave it untouched or can follow
        //  official documentation about IPN from SSLCommerz Panel
        Toastr::error('Something went wrong');
        return redirect('/checkout');
    }

    public function refund($bankID)
    {
        /**
         * SSLCommerz::refund($bank_trans_id, $amount [,$reason])
         */

        $refund = SSLCommerz::refund($bankID, 1500); // 1500 => refund amount

        if ($refund->status) {
            /**
             * States:
             * success : Refund request is initiated successfully
             * failed : Refund request is failed to initiate
             * processing : The refund has been initiated already
             */

            $state  = $refund->refund_state;

            /**
             * RefID will be used for post-refund status checking
             */

            $refID  = $refund->ref_id;

            /**
             *  To get all the outputs
             */

            dd($refund->output);
        } else {
            return $refund->message;
        }
    }

    public function check_refund_status($refID)
    {
        $refund = SSLCommerz::query_refund($refID);

        if ($refund->status) {
            /**
             * States:
             * refunded : Refund request has been proceeded successfully
             * processing : Refund request is under processing
             * cancelled : Refund request has been proceeded successfully
             */

            $state  = $refund->refund_state;

            /**
             * RefID will be used for post-refund status checking
             */

            $refID  = $refund->ref_id;

            /**
             *  To get all the outputs
             */

            dd($refund->output);
        } else {
            return $refund->message;
        }
    }

    public function get_transaction_status($trxID)
    {
        $query = SSLCommerz::query_transaction($trxID);

        if ($query->status) {
            dd($query->output);
        } else {
            $query->message;
        }
    }
}
