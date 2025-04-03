<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailConfigure;
use App\Models\UserDevice;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\SocialLoginResource;
use App\Models\User;
use App\Models\SmsGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserVerificationEmail;
use App\Mail\ForgetPasswordEmail;
use App\Models\SocialLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Laravel\Socialite\Facades\Socialite;

class AuthenticationController extends Controller
{
    const AUTHORIZATION_TOKEN = 'GolpoBazar-SBW7583837NUDD82';

    public function userRegistration(Request $request){
        if ($request->header('Authorization') == AuthenticationController::AUTHORIZATION_TOKEN) {

            if(!$request->name || !$request->username || !$request->password){
                return response()->json([
                    'success' => false,
                    'message' => 'All the Fields are required'
                ]);
            }

            $name = $request->name;
            $username = $request->username;
            $password = $request->password;
            $data = array();

            // check its a email or phone
            if (filter_var($username, FILTER_VALIDATE_EMAIL)) { // email

                User::where('email', $username)->where('email_verified_at', null)->delete();
                $checkExistingEmail = User::where('email', $username)->where('email_verified_at', '!=', null)->first();
                if($username != '' && $checkExistingEmail){
                    return response()->json([
                        'success'=> false,
                        'message'=> 'Email already used ! Please use another Email',
                        'data' => $data
                    ]);
                } else {

                    $randomCode = rand(100000, 999999);
                    User::insert([
                        'name' => $name,
                        'email' => $username,
                        'password' => Hash::make($password),
                        'email_verified_at' => null,
                        'verification_code' => $randomCode,
                        'user_type' => 3,
                        'status' => 0,
                        'delete_request_submitted' => 0,
                        'delete_request_submitted_at' => NULL,
                        'created_at' => Carbon::now(),
                    ]);
                    
                    $data['name'] = $name;
                    $data['email'] = $username;
                    $data['phone'] = null;
                    $data['email_verified_at'] = null;
                    // $data['user_type'] = 3;
                    $data['status'] = 0;
                    $data['image'] = null;
                    $data['balance'] = 0;

                    try {

                        $emailConfig = EmailConfigure::where('status', 1)->orderBy('id', 'desc')->first();
                        if($emailConfig){

                            $decryption = "";
                            if($emailConfig){
                                $ciphering = "AES-128-CTR";
                                $options = 0;
                                $decryption_iv = '1234567891011121';
                                $decryption_key = "GolpoBazar";
                                $decryption = openssl_decrypt ($emailConfig->password, $ciphering, $decryption_key, $options, $decryption_iv);
                            }

                            config([
                                'mail.mailers.smtp.host' => $emailConfig ? $emailConfig->host : '',
                                'mail.mailers.smtp.port' => $emailConfig ? $emailConfig->port : '',
                                'mail.mailers.smtp.username' => $emailConfig ? $emailConfig->email : '',
                                'mail.mailers.smtp.password' => $decryption != "" ? $decryption : '',
                                'mail.mailers.smtp.encryption' => $emailConfig ? ($emailConfig->encryption == 1 ? 'tls' : ($emailConfig->encryption == 2 ? 'ssl' : '')) : '',
                            ]);

                            $mailData = array();
                            $mailData['code'] = $randomCode;
                            Mail::to(trim($username))->send(new UserVerificationEmail($mailData));

                            return response()->json([
                                'success' => true,
                                'message' => "Verification Email Sent",
                                'data' => $data
                            ], 200);

                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => "No Mail Server is Active Yet",
                                'data' => $data
                            ], 200);
                        }

                    } catch(\Exception $e) {
                        return response()->json([
                            'success' => false,
                            'message' => "Something Went Wrong while Sending Email",
                            'data' => $data
                        ], 200);
                    }
                }

            } else { // phone

                User::where('phone', $username)->where('email_verified_at', null)->delete();
                $checkExistingPhone = User::where('phone', $username)->where('email_verified_at', '!=', null)->first();
                if($username != '' && $checkExistingPhone){
                    return response()->json([
                        'success' => false,
                        'message' => 'Phone already used ! Please use another Mobile No',
                        'data' => $data
                    ]);
                } else {

                    $randomCode = rand(100000, 999999);
                    User::insert([
                        'name' => $name,
                        'email' => null,
                        'phone' => $username,
                        'password' => Hash::make($password),
                        'email_verified_at' => null,
                        'verification_code' => $randomCode,
                        'user_type' => 3,
                        'status' => 0,
                        'delete_request_submitted' => 0,
                        'delete_request_submitted_at' => NULL,
                        'created_at' => Carbon::now(),
                    ]);
                    
                   
                    $data['name'] = $name;
                    $data['email'] = null;
                    $data['phone'] = $username;
                    $data['email_verified_at'] = null;
                    // $data['user_type'] = 3;
                    $data['status'] = 0;
                    $data['image'] = null;
                    $data['balance'] = 0;

                    $smsGateway = SmsGateway::where('status', 1)->first();
                    if($smsGateway && $smsGateway->provider_name == 'Reve'){
                        $response = Http::get($smsGateway->api_endpoint, [
                            'apikey' => $smsGateway->api_key,
                            'secretkey' => $smsGateway->secret_key,
                            "callerID" => $smsGateway->sender_id,
                            "toUser" => $username,
                            "messageContent" => "Golpo Bazar Verification Code is : ". $randomCode
                        ]);

                        if($response->status() == 200){
                            return response()->json([
                                'success' => true,
                                'message' => "Verification SMS Sent Successfully", //$response
                                'data' => $data
                            ], 200);

                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => "Failed to Send SMS",
                                'data' => $data
                            ], 200);
                        }

                    } elseif($smsGateway && $smsGateway->provider_name == 'ElitBuzz'){

                        $response = Http::get($smsGateway->api_endpoint, [
                            'api_key' => $smsGateway->api_key,
                            "type" => "text",
                            "contacts" => $username, //“88017xxxxxxxx,88018xxxxxxxx”
                            "senderid" => $smsGateway->sender_id,
                            "msg" => "Golpo Bazar Verification Code is : ". $randomCode
                        ]);

                        if($response->status() == 200){
                            return response()->json([
                                'success' => true,
                                'message' => "SMS Sent Successfully",
                                'data' => $data
                            ], 200);
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => "Failed to Send SMS",
                                'data' => $data
                            ], 200);
                        }

                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => "No SMS Gateway is Active Yet",
                            'data' => $data
                        ], 200);
                    }
                }

            }

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function userVerification(Request $request){
        if ($request->header('Authorization') == AuthenticationController::AUTHORIZATION_TOKEN) {

            if(!$request->code || !$request->username || !$request->password){
                return response()->json([
                    'success' => false,
                    'message' => 'All the Fields are required'
                ]);
            }

            if(Auth::attempt(['email' => $request->username, 'password' => $request->password, 'verification_code' => $request->code]) || Auth::attempt(['phone' => $request->username, 'password' => $request->password, 'verification_code' => $request->code])){

                $user = Auth::user();

                User::where('id', $user->id)->update([
                    'email_verified_at' => Carbon::now(),
                    'status' => 1,
                    'updated_at' => Carbon::now(),
                ]);

                $data['token'] = $user->createToken('GolpoBazar')->plainTextToken;
                $data['id'] = $user->id;
                $data['name'] = $user->name;
                $data['email'] = $user->email;
                $data['phone'] = $user->phone;
                $data['email_verified_at'] = date("Y-m-d H:i:s", strtotime($user->email_verified_at));
                // $data['user_type'] = 3;
                $data['status'] = 1;
                $data['image'] = $user->image;
                $data['balance'] = $user->balance;

                return response()->json([
                    'success'=> true,
                    'message'=> 'Successfully Verified And Logged In',
                    'data' => $data
                ]);

            }
            else{
                $data = array();
                return response()->json([
                    'success'=> false,
                    'message'=> 'Wrong Verification Code',
                    'data' => $data
                ]);
            }

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function userLogin(Request $request){

        if ($request->header('Authorization') == AuthenticationController::AUTHORIZATION_TOKEN) {

            if(!$request->username || !$request->password){
                return response()->json([
                    'success' => false,
                    'message' => 'All the Fields are required'
                ]);
            }

            if(Auth::attempt(['email' => $request->username, 'password' => $request->password]) || Auth::attempt(['phone' => $request->username, 'password' => $request->password])){

                $user = Auth::user();

                if($user->email_verified_at == null){
                    return response()->json([
                        'success' => false,
                        'message' => 'User verification is not done yet',
                        'data' => null,
                    ]);
                }

                // check how many device are already logged in
                $totalLoggedInDevices = UserDevice::where('user_id', $user->id)->where('login_status', 1)->count();
                if($totalLoggedInDevices >= 3){
                    return response()->json([
                        'success' => false,
                        'message' => "Maximum Device Logged In Limit Exceeded",
                    ]);
                }

                User::where('id', $user->id)->update([
                    'delete_request_submitted' => 0,
                    'delete_request_submitted_at' => NULL,
                ]);

                $data['token'] = $user->createToken('GolpoBazar')->plainTextToken;
                $data['id'] = $user->id;
                
                $data['name'] = $user->name;
                $data['email'] = $user->email;
                $data['phone'] = $user->phone;
                $data['email_verified_at'] = date("Y-m-d H:i:s", strtotime($user->email_verified_at));
                // $data['user_type'] = 3;
                $data['status'] = 1;
                $data['image'] = $user->image;
                $data['balance'] = $user->balance;

                return response()->json([
                    'success'=> true,
                    'message'=> 'Successfully Logged In',
                    'data' => $data
                ]);

            }
            else{
                return response()->json([
                    'success' => false,
                    'message' => 'Wrong Login Credentials',
                    'data' => null,
                ]);
            }

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function updateDeviceToken(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $user_id = $request->user_id;
            $device_id = $request->device_id;
            $token = $request->token;
            $login_status = $request->login_status;

            $userDeviceInfo = UserDevice::where('user_id', $user_id)->where('device_id', $device_id)->first();
            if($userDeviceInfo){

                UserDevice::where('user_id', $user_id)->where('device_id', $device_id)->update([
                    'token' => $token,
                    'login_status' => $login_status,
                    'updated_at' => Carbon::now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Successfully Updated",
                ]);

            } else {

                UserDevice::insert([
                    'user_id' => $user_id,
                    'device_id' => $device_id,
                    'token' => $token,
                    'login_status' => $login_status,
                    'created_at' => Carbon::now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Successfully Inserted",
                ]);
            }

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function forgetPassword(Request $request){
        if ($request->header('Authorization') == AuthenticationController::AUTHORIZATION_TOKEN) {

            if(!$request->username){
                return response()->json([
                    'success' => false,
                    'message' => 'Please Write Your Username'
                ]);
            }

            $username = $request->username;

            // check its a email or phone
            if (filter_var($username, FILTER_VALIDATE_EMAIL)) { // email

                $userInfo = User::where('email', $username)->first();
                if(!$userInfo){
                    return response()->json([
                        'success' => false,
                        'message' => 'Email does not Exist',
                        'data' => null
                    ]);
                } else {

                    $randomCode = rand(100000, 999999);
                    $userInfo->verification_code = $randomCode;
                    $userInfo->save();

                    try {

                        $emailConfig = EmailConfigure::where('status', 1)->orderBy('id', 'desc')->first();
                        if($emailConfig){

                            $decryption = "";
                            if($emailConfig){
                                $ciphering = "AES-128-CTR";
                                $options = 0;
                                $decryption_iv = '1234567891011121';
                                $decryption_key = "GolpoBazar";
                                $decryption = openssl_decrypt ($emailConfig->password, $ciphering, $decryption_key, $options, $decryption_iv);
                            }

                            config([
                                'mail.mailers.smtp.host' => $emailConfig ? $emailConfig->host : '',
                                'mail.mailers.smtp.port' => $emailConfig ? $emailConfig->port : '',
                                'mail.mailers.smtp.username' => $emailConfig ? $emailConfig->email : '',
                                'mail.mailers.smtp.password' => $decryption != "" ? $decryption : '',
                                'mail.mailers.smtp.encryption' => $emailConfig ? ($emailConfig->encryption == 1 ? 'tls' : ($emailConfig->encryption == 2 ? 'ssl' : '')) : '',
                            ]);

                            $mailData = array();
                            $mailData['code'] = $randomCode;
                            Mail::to(trim($username))->send(new ForgetPasswordEmail($mailData));

                            return response()->json([
                                'success' => true,
                                'message' => "Password Reset Email Sent",
                                'data' => null
                            ], 200);

                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => "No Mail Server is Active Yet",
                                'data' => null
                            ], 200);
                        }

                    } catch(\Exception $e) {
                        return response()->json([
                            'success' => false,
                            'message' => "Something Went Wrong while Sending Email",
                            'data' => null
                        ], 200);
                    }
                }

            } else {

                $userInfo = User::where('phone', $username)->first();
                if(!$userInfo){
                    return response()->json([
                        'success' => false,
                        'message' => 'Phone No. Not Found',
                        'data' => null
                    ]);
                } else {

                    $randomCode = rand(100000, 999999);
                    $userInfo->verification_code = $randomCode;
                    $userInfo->save();

                    $smsGateway = SmsGateway::where('status', 1)->first();
                    if($smsGateway && $smsGateway->provider_name == 'Reve'){
                        $response = Http::get($smsGateway->api_endpoint, [
                            'apikey' => $smsGateway->api_key,
                            'secretkey' => $smsGateway->secret_key,
                            "callerID" => $smsGateway->sender_id,
                            "toUser" => $username,
                            "messageContent" => "Golpo Bazar Verification Code is : ". $randomCode
                        ]);

                        if($response->status() == 200){
                            return response()->json([
                                'success' => true,
                                'message' => "Password Reset SMS Sent Successfully",
                                'data' => null
                            ], 200);

                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => "Failed to Send SMS",
                                'data' => null
                            ], 200);
                        }

                    } elseif($smsGateway && $smsGateway->provider_name == 'ElitBuzz'){

                        $response = Http::get($smsGateway->api_endpoint, [
                            'api_key' => $smsGateway->api_key,
                            "type" => "text",
                            "contacts" => $username, //“88017xxxxxxxx,88018xxxxxxxx”
                            "senderid" => $smsGateway->sender_id,
                            "msg" => "Golpo Bazar Verification Code is : ". $randomCode
                        ]);

                        if($response->status() == 200){
                            return response()->json([
                                'success' => true,
                                'message' => "Password Reset SMS Sent Successfully",
                                'data' => null
                            ], 200);
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => "Failed to Send SMS",
                                'data' => null
                            ], 200);
                        }

                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => "No SMS Gateway is Active Yet",
                            'data' => null
                        ], 200);
                    }
                }
            }

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function verifyResetCode(Request $request){
        if ($request->header('Authorization') == AuthenticationController::AUTHORIZATION_TOKEN) {

            if(!$request->username && !$request->code){
                return response()->json([
                    'success' => false,
                    'message' => 'Please Provide Username with Verification Code',
                    'data' => null
                ]);
            }

            $username = $request->username;
            $code = $request->code;
            $data = array();

            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {

                $userInfo = User::where('email', $username)->where('verification_code', $code)->first();
                if(!$userInfo){
                    return response()->json([
                        'success' => false,
                        'message' => 'Wrong Password Reset Code',
                        'data' => null
                    ]);
                } else {

                    $data['name'] = $userInfo->name;
                    $data['email'] = $userInfo->email;
                    $data['phone'] = $userInfo->phone;
                    $data['email_verified_at'] = date("Y-m-d H:i:s", strtotime($userInfo->email_verified_at));
                    $data['status'] = $userInfo->status;
                    $data['image'] = $userInfo->image;
                    $data['balance'] = $userInfo->balance;

                    return response()->json([
                        'success' => true,
                        'message' => "Code is Verified. Now Change Your Password",
                        'data' => $data
                    ], 200);

                }

            } else {

                $userInfo = User::where('phone', $username)->where('verification_code', $code)->first();
                if(!$userInfo){
                    return response()->json([
                        'success' => false,
                        'message' => 'Wrong Password Reset Code',
                        'data' => null
                    ]);
                } else {

                    $data['name'] = $userInfo->name;
                    $data['email'] = $userInfo->email;
                    $data['phone'] = $userInfo->phone;
                    $data['email_verified_at'] = date("Y-m-d H:i:s", strtotime($userInfo->email_verified_at));
                    $data['status'] = $userInfo->status;
                    $data['image'] = $userInfo->image;
                    $data['balance'] = $userInfo->balance;

                    return response()->json([
                        'success' => true,
                        'message' => "Code is Verified. Now Change Your Password",
                        'data' => $data
                    ], 200);

                }
            }

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function changePassword(Request $request){
        if ($request->header('Authorization') == AuthenticationController::AUTHORIZATION_TOKEN) {

            if(!$request->username && !$request->code){
                return response()->json([
                    'success' => false,
                    'message' => 'Please Provide Username with Verification Code',
                    'data' => null
                ]);
            }

            $username = $request->username;
            $password = $request->password;

            if (filter_var($username, FILTER_VALIDATE_EMAIL)) {

                $userInfo = User::where('email', $username)->first();
                $userInfo->password = Hash::make($password);
                $userInfo->save();

                $data['name'] = $userInfo->name;
                $data['email'] = $userInfo->email;
                $data['phone'] = $userInfo->phone;
                $data['email_verified_at'] = date("Y-m-d H:i:s", strtotime($userInfo->email_verified_at));
                $data['status'] = $userInfo->status;
                $data['image'] = $userInfo->image;
                $data['balance'] = $userInfo->balance;

                return response()->json([
                    'success' => true,
                    'message' => "Password Changed Successfully",
                    'data' => $data
                ], 200);

            } else {

                $userInfo = User::where('phone', $username)->first();
                $userInfo->password = Hash::make($password);
                $userInfo->save();

                $data['name'] = $userInfo->name;
                $data['email'] = $userInfo->email;
                $data['phone'] = $userInfo->phone;
                $data['email_verified_at'] = date("Y-m-d H:i:s", strtotime($userInfo->email_verified_at));
                $data['status'] = $userInfo->status;
                $data['image'] = $userInfo->image;
                $data['balance'] = $userInfo->balance;

                return response()->json([
                    'success' => true,
                    'message' => "Password Changed Successfully",
                    'data' => $data
                ], 200);

            }

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }


    public function userSocialLogin(Request $request){
        if ($request->header('Authorization') == AuthenticationController::AUTHORIZATION_TOKEN) {


            $url='https://oauth2.googleapis.com/tokeninfo?id_token='.$request->gmail_token;
            $checkAuth=false;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            $result = curl_exec($ch);
            // Check HTTP status code
            if (!curl_errno($ch)) {
              switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200:
                    if (!curl_errno($ch)) {
                        $checkAuth=true;
                    }else{
                        $checkAuth=false;
                    }
                  break;
                default:
                  $checkAuth=false;
              }
            }
            // Close handle
            curl_close($ch);


            if ($checkAuth == true) {

                $userInfo = User::where('email', $request->email)->first();

                // check how many device are already logged in
                if($userInfo){
                    $totalLoggedInDevices = UserDevice::where('user_id', $userInfo->id)->where('login_status', 1)->count();
                    if($totalLoggedInDevices >= 3){
                        return response()->json([
                            'success' => false,
                            'message' => "Maximum Device Logged In Limit Exceeded",
                        ]);
                    }
                }


                if ($userInfo) {

                    if(Auth::loginUsingId($userInfo->id)){

                        $user = Auth::user();
                        User::where('id', $user->id)->update([
                            'delete_request_submitted' => 0,
                            'delete_request_submitted_at' => NULL,
                        ]);

                        $data['token'] = $user->createToken('GolpoBazar')->plainTextToken;
                        $data['id'] = $user->id;
                        $data['name'] = $user->name;
                        $data['email'] = $user->email;
                        $data['phone'] = $user->phone;
                        $data['email_verified_at'] = date("Y-m-d H:i:s", strtotime($user->email_verified_at));
                        // $data['user_type'] = 3;
                        $data['status'] = 1;
                        $data['image'] = $user->image;
                        $data['balance'] = $user->balance;
                        // $data['address'] = $user->address;
                        // $data['dob'] = $user->dob;
                        // $data['gender'] = $user->gender;

                        return response()->json([
                            'success'=> true,
                            'message'=> 'Successfully Logged In',
                            'data' => $data
                        ]);

                    }

                } else {

                    $userID = User::insertGetId([
                        'name' => $request->name,
                        'email' => $request->email,
                        'email_verified_at' => date("Y-m-d H:i:s"),
                        'status' => 1,
                        'created_at' => Carbon::now()
                    ]);

                    Auth::loginUsingId($userID);
                    $user = Auth::user();

                    User::where('id', $user->id)->update([
                        'delete_request_submitted' => 0,
                        'delete_request_submitted_at' => NULL,
                    ]);

                    $data['token'] = $user->createToken('GolpoBazar')->plainTextToken;
                    $data['id'] = $user->id;
                    $data['name'] = $user->name;
                    $data['email'] = $user->email;
                    $data['phone'] = $user->phone;
                    $data['email_verified_at'] = date("Y-m-d H:i:s", strtotime($user->email_verified_at));
                    // $data['user_type'] = 3;
                    $data['status'] = 1;
                    $data['image'] = $user->image;
                    $data['balance'] = $user->balance;
                    $data['address'] = $user->address;
                    $data['dob'] = $user->dob;
                    $data['gender'] = $user->gender;

                    return response()->json([
                        'success'=> true,
                        'message'=> 'Successfully Registered',
                        'data' => $data
                    ]);

                }

            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Unauthorized Token"
                ], 422);
            }


        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }

    }

}
