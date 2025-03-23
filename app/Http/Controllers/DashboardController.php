<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function orderPreview($slug){
        $orderInfo = DB::table('orders')->where('slug', $slug)->first();
        return view('order_success', compact('orderInfo'));
    }

    public function userProfile(){
        return view('dashboard.home');
    }

    public function updateProfile(Request $request){

        // $request->validate([
        //     'name' => 'required',
        //     'email' => 'email',
        //     'image' => 'mimes:jpeg,jpg,png,gif'
        // ]);

        $user = Auth::user();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->dob = $request->dob;
        $user->address = $request->address;
        $user->gender = $request->gender;

        if($request->hasFile('image')){
            $image = $request->file('image');
            $imageName = time().'.'.$image->extension();
            $image->move(public_path('uploads/users/'), $imageName);
            $user->image = $imageName;
        }

        $user->save();

        Toastr::success('Profile Updated Successfully', 'Success');
        return back();
    }

    public function userCart(){
        return view('dashboard.home');
    }
    public function changePassword(){
        return view('dashboard.home');
    }

    public function updatePassword(Request $request){

        $user = Auth::user();

        if($request->current_password && $request->new_password){
            if(Hash::check($request->current_password, $user->password)){
                $user->password = Hash::make($request->new_password);
                $user->save();
            }else{
                Toastr::error('Current Password is not correct', 'Error');
                return back();
            }
        }

        Toastr::success('Password Changed Successfully', 'Success');
        return back();
    }

    public function userOrders(){
        return view('dashboard.home');
    }

    public function orderDetails($slug){

        $orderInfo = DB::table('orders')->where('order_no', $slug)->first();

        $orderDetails = DB::table('order_details')
                            ->leftJoin('products', 'order_details.product_id', 'products.id')
                            ->leftJoin('users', 'products.author_id', 'users.id')
                            ->select('order_details.*', 'products.name', 'products.image', 'products.slug as book_slug', 'users.name as author_name')
                            ->where('order_id', $orderInfo->id)
                            ->get();

        $orderPayment = DB::table('order_payments')->where('order_id', $orderInfo->id)->first();

        return view('dashboard.order_details', compact('orderInfo', 'orderDetails', 'orderPayment'));
    }
}
