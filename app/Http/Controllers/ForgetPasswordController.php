<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserVerificationMail;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ForgetPasswordController extends Controller
{
    public function userForgetPassword(){
        return view('forget_password');
    }

    public function sendForgetPasswordCode(Request $request){

        $request->validate([
            'username' => ['required', 'regex:/^(?:\+8801[3-9]\d{8}|01[3-9]\d{8})$/'],
        ]);

        $randomCode = rand(100000, 999999);
        $userInfo = User::where('phone', $request->username)->first();
        if($userInfo){
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
                    "messageContent" => "Your ".env("APP_NAME")." Verification Code is : ". $randomCode
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
                    "msg" => $randomCode . " is your OTP verification code for ".env("APP_NAME")
                ]);

                if($response->status() != 200){
                    Toastr::error('Something Went Wrong', 'Failed to send SMS');
                    return back();
                }

            } else {
                Toastr::error('No SMS Gateway is Active Now', 'Failed to send SMS');
                return back();
            }

            session(['username' => $request->username]);

            Toastr::success('Password Reset Code Sent', 'Code Sent Successfully');
            // return view('frontend.change_password');
            return redirect('/new/password');
        } else {
            Toastr::error('No Account Found', '! Failed');
            return back();
        }

    }

    public function newPasswordPage(){
        return view('change_password');
    }

    public function changeForgetPassword(Request $request){

        $request->validate([
            'code' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255', 'min:8'],
        ]);

        $username = session('username');
        $code = $request->code;
        $password = $request->password;

        $userInfo = User::where('email', $username)->where('verification_code', $code)->first();
        if($userInfo){
            $userInfo->password = Hash::make($password);
            $userInfo->email_verified_at = Carbon::now();
            $userInfo->save();
            Auth::login($userInfo);

            Toastr::success('Successfully Changed the Password', 'Password Changed');
            return redirect('/home');
        } else {

            $userInfo = User::where('phone', $username)->where('verification_code', $code)->first();
            if($userInfo){
                $userInfo->password = Hash::make($password);
                $userInfo->email_verified_at = Carbon::now();
                $userInfo->save();
                Auth::login($userInfo);

                Toastr::success('Successfully Changed the Password', 'Password Changed');
                return redirect('/home');
            } else {
                Toastr::error('Wrong Verification Code', 'Try Again');
                return back();
            }
        }

    }

    public function formatBangladeshiPhoneNumber($phoneNumber) {
        // Remove any non-numeric characters from the phone number
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

        // Check if the number starts with '88'
        if (substr($phoneNumber, 0, 2) !== '88') {
            // If not, prepend '88' to the number
            $phoneNumber = '88' . $phoneNumber;
        }

        return $phoneNumber;
    }
}
