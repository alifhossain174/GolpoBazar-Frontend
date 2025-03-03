<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PolicyController extends Controller
{
    public function termsAndConditions(){
        $pageTitle = "Terms And Conditions";
        $pageUrl = url('/terms/and/conditions');
        $policy = DB::table('terms_and_policies')->select('terms as policy')->first();
        return view('policy', compact('pageTitle', 'pageUrl', 'policy'));
    }

    public function privacyPolicy(){
        $pageTitle = "Privacy Policy";
        $pageUrl = url('/privacy/policy');
        $policy = DB::table('terms_and_policies')->select('privacy_policy as policy')->first();
        return view('policy', compact('pageTitle', 'pageUrl', 'policy'));
    }

    public function shippingPolicy(){
        $pageTitle = "Shipping Policy";
        $pageUrl = url('/shipping/policy');
        $policy = DB::table('terms_and_policies')->select('shipping_policy as policy')->first();
        return view('policy', compact('pageTitle', 'pageUrl', 'policy'));
    }

    public function returnPolicy(){
        $pageTitle = "Return Policy";
        $pageUrl = url('/return/policy');
        $policy = DB::table('terms_and_policies')->select('return_policy as policy')->first();
        return view('policy', compact('pageTitle', 'pageUrl', 'policy'));
    }

}
