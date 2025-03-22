<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
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
}
