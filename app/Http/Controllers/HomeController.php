<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }


    public function userVerification(){
        $randomCode = rand(100000, 999999);
        $userInfo = Auth::user();

        if(!$userInfo->email_verified_at && !$userInfo->verification_code){

            User::where('id', $userInfo->id)->update([
                'verification_code' => $randomCode
            ]);

            $smsGateway = DB::table('sms_gateways')->where('status', 1)->first();
            if($smsGateway && $smsGateway->provider_name == 'Reve'){

                $response = Http::get($smsGateway->api_endpoint, [
                    'apikey' => $smsGateway->api_key,
                    'secretkey' => $smsGateway->secret_key,
                    "callerID" => $smsGateway->sender_id,
                    "toUser" => $userInfo->phone,
                    "messageContent" => env('APP_NAME')." Verification Code is : ". $randomCode
                ]);

                if($response->status() != 200){
                    Toastr::error('Something Went Wrong', 'Failed to send SMS');
                    return back();
                }

            } elseif($smsGateway && $smsGateway->provider_name == 'ElitBuzz'){

                $response = Http::get($smsGateway->api_endpoint, [
                    'api_key' => $smsGateway->api_key,
                    "type" => "text",
                    "contacts" => $userInfo->phone, //“88017xxxxxxxx,88018xxxxxxxx”
                    "senderid" => $smsGateway->sender_id,
                    "msg" => $randomCode . " is your OTP verification code for shadikorun.com"
                ]);

                if($response->status() != 200){
                    Toastr::error('Something Went Wrong', 'Failed to send SMS');
                    return back();
                }

            } else {
                Toastr::error('No SMS Gateway is Active Now', 'Failed to send SMS');
                return back();
            }

            return view('auth.verification');

        } elseif(!$userInfo->email_verified_at && $userInfo->verification_code){
            return view('auth.verification');
        }
         else {
            return redirect('/home');
        }

    }


    public function userVerificationResend(){
        $randomCode = rand(100000, 999999);
        $userInfo = Auth::user();

        if(!$userInfo->email_verified_at){

            User::where('id', $userInfo->id)->update([
                'verification_code' => $randomCode
            ]);


            $smsGateway = DB::table('sms_gateways')->where('status', 1)->first();
            if($smsGateway && $smsGateway->provider_name == 'Reve'){
                $response = Http::get($smsGateway->api_endpoint, [
                    'apikey' => $smsGateway->api_key,
                    'secretkey' => $smsGateway->secret_key,
                    "callerID" => $smsGateway->sender_id,
                    "toUser" => $userInfo->phone,
                    "messageContent" => env('APP_NAME')." Verification Code is : ". $randomCode
                ]);

                if($response->status() != 200){
                    Toastr::error('Something Went Wrong', 'Failed to send SMS');
                    return back();
                }

            } elseif($smsGateway && $smsGateway->provider_name == 'ElitBuzz'){

                $response = Http::get($smsGateway->api_endpoint, [
                    'api_key' => $smsGateway->api_key,
                    "type" => "text",
                    "contacts" => $userInfo->phone, //“88017xxxxxxxx,88018xxxxxxxx”
                    "senderid" => $smsGateway->sender_id,
                    "msg" => $randomCode . " is your OTP verification code for shadikorun.com"
                ]);

                if($response->status() != 200){
                    Toastr::error('Something Went Wrong', 'Failed to send SMS');
                    return back();
                }

            } else {
                Toastr::error('No SMS Gateway is Active Now', 'Failed to send SMS');
                return back();
            }

            Toastr::success('Verification Code Sent', 'Resend Verification Code');
            return back();

        } else {
            return redirect('/home');
        }

    }


    public function userVerifyCheck(Request $request){

        $verificationCode = $request->code;
        $userInfo = Auth::user();

        if($userInfo->verification_code == $verificationCode){
            User::where('id', $userInfo->id)->update([
                'email_verified_at' => Carbon::now()
            ]);
            Toastr::success('User Verification Complete', 'Successfully Verified');

            if(session('cart') && count(session('cart')) > 0){
                return redirect('/view/cart');
            } else {
                return redirect('/home');
            }

        } else {
            Toastr::error('Wrong Verification Code', 'Failed');
            return back();
        }
    }
}
