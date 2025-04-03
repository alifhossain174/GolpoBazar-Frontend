<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PromoCodeResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\OrderProgress;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\WishList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Resources\CartResource;
use App\Http\Resources\WishListResource;
use App\Models\User;

class CartController extends Controller
{
    const AUTHORIZATION_TOKEN = 'GolpoBazar-SBW7583837NUDD82';

    public function checkCartStatus(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $productId = $request->product_id;
            $userUniqueCartNo = $request->user_unique_cart_no;
            $cartInfo = Cart::where([['user_unique_cart_no', $userUniqueCartNo], ['product_id', $productId]])->first();

            if($cartInfo){
                return response()->json([
                    'success' => true,
                    'data' => $cartInfo->id
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Not Found In Cart'
                ], 200);
            }

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function addToCart(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $productInfo = Product::where('id', $request->product_id)->first();

            $cartInfo = Cart::where([['user_unique_cart_no', $request->user_unique_cart_no], ['product_id', $request->product_id]])->first();

            if($cartInfo){

                return response()->json([
                    'success' => false,
                    'message' => 'Already Added'
                ], 200);

            } else{
                $cartId = Cart::insertGetId([
                    'user_unique_cart_no' => $request->user_unique_cart_no,
                    'product_id' => $productInfo->id,
                    'qty' => $request->qty,
                    'created_at' => Carbon::now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully Added'
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function deleteCartItem(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            Cart::where('id', $request->cart_id)->delete();

            return response()->json([
                'success' => true,
                'message' => "Item has removed from Cart"
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getCartItems(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            // $data = DB::table('carts')
            //             ->join('products', 'carts.product_id', '=', 'products.id')
            //             ->leftJoin('units', 'products.unit_id', '=', 'units.id')
            //             ->where('carts.user_unique_cart_no', $request->user_unique_cart_no)
            //             ->select('carts.*', 'units.name as unit_name', 'products.image', 'products.name as product_name', 'products.discount_price', 'products.price as product_price')
            //             ->get();

            // return response()->json([
            //     'success' => true,
            //     'date' => $data
            // ], 200);

            $data = DB::table('carts')
                ->join('products', 'carts.product_id', '=', 'products.id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'carts.id as cart_id', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name',
                'product_warrenties.name as product_warrenty')
                ->where('carts.user_unique_cart_no', $request->user_unique_cart_no)
                ->orderBy('carts.id', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => CartResource::collection($data)
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function applyCoupon(Request $request){

        $promoInfo = PromoCode::where('code', $request->coupon_code)->where('status', 1)->where('effective_date', '<=', date("Y-m-d"))->where('expire_date', '>=', date("Y-m-d"))->first();
        $data = PromoCode::where('status', 100)->first();

        if($promoInfo){

            $alreadyUsed = Order::where('user_id', auth()->user()->id)->where('coupon_code', $request->coupon_code)->count();

            if($promoInfo->no_of_usage && $promoInfo->no_of_usage > 0){
                if($alreadyUsed >= $promoInfo->no_of_usage){
                    return response()->json([
                        'success' => false,
                        'data' => $data,
                        'message' => "Promo Code is used maximum time"
                    ], 200);
                }
            }

            if($alreadyUsed > 0){
                return response()->json([
                    'success' => false,
                    'data' => $data,
                    'message' => "Promo Code is already used"
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'data' => new PromoCodeResource($promoInfo),
                    'message' => "Successfully Applied Coupon Code"
                ], 200);
            }

        } else {
            return response()->json([
                'success' => false,
                'data' => $data,
                'message' => "Promo Code is Inactive or Out of Date Range"
            ], 200);
        }

    }

    public function getAllCoupons(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = PromoCode::where('status', 1)->where('expire_date', '>=', date("Y-m-d"))->get();

            return response()->json([
                'success' => true,
                'date' => $data
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function cartCheckout(Request $request){

        $orderId = Order::insertGetId([
            'order_no' => time().rand(100,999),
            'user_id' => auth()->user()->id,
            'order_date' => date("Y-m-d H:i:s"),
            'estimated_dd' => date('Y-m-d', strtotime("+7 day", strtotime(date("Y-m-d")))),
            'payment_method' => NULL,
            'trx_id' => time().str::random(5),
            'order_status' => 0,
            'sub_total' => 0,
            'coupon_code' => NULL,
            'discount' => 0, //will be count at the bottom
            'delivery_fee' => 0,
            'vat' => 0,
            'tax' => 0,
            'total' => 0,
            'order_note' => isset($request->special_note) ? $request->special_note : '',
            'delivery_method' => isset($request->delivery_method) ? $request->delivery_method : '',
            'slug' => str::random(5) . time(),
            'created_at' => Carbon::now()
        ]);

        OrderProgress::insert([
            'order_id' => $orderId,
            'order_status' => 0,
            'created_at' => Carbon::now()
        ]);

        $totalOrderAmount = 0;
        $cartItems = Cart::where('user_unique_cart_no', $request->user_unique_cart_no)->orderBy('id', 'desc')->get();

        foreach($cartItems as $item){

            $prodInfo = Product::where('id', $item->product_id)->first();
            $price = $prodInfo->discount_price > 0 ? $prodInfo->discount_price : $prodInfo->price;

            // $productInfo = Product::where('id', $item->product_id)->first();
            // if($productInfo && $productInfo->author_id){
            //     $userInfo = User::where('id', $productInfo->author_id)->first();
            //     if($userInfo){
            //         $userInfo->balance = $userInfo->balance + (($price * $productInfo->comission) / 100);
            //         $userInfo->save();
            //     }
            // }

            OrderDetails::insert([
                'order_id' => $orderId,
                'product_id' => $item->product_id,
                'qty' => 1,
                'unit_id' => $prodInfo->unit_id,
                'unit_price' => $price,
                'total_price' => $price,
                'author_comission' => ($price * $prodInfo->comission) / 100,
                'created_at' => Carbon::now()
            ]);
            $totalOrderAmount = $totalOrderAmount + (1 * $price);
        }

        Cart::where('user_unique_cart_no', $request->user_unique_cart_no)->delete();


        // calculating coupon discount
        $discount = 0;
        $promoInfo = PromoCode::where('code', $request->coupon_code)->where('status', 1)->where('effective_date', '<=', date("Y-m-d"))->where('expire_date', '>=', date("Y-m-d"))->first();
        if($promoInfo){

            $alreadyUsed = Order::where('user_id', auth()->user()->id)->where('coupon_code', $request->coupon_code)->count();

            if($promoInfo->no_of_usage && $promoInfo->no_of_usage > 0){
                if($alreadyUsed <= $promoInfo->no_of_usage){

                    if($promoInfo->type == 1){
                        $discount = $promoInfo->value;
                    } else {
                        $discount = ($totalOrderAmount*$promoInfo->value)/100;
                    }

                }
            } else {

                if($promoInfo->type == 1){
                    $discount = $promoInfo->value;
                } else {
                    $discount = ($totalOrderAmount*$promoInfo->value)/100;
                }

            }

        }
        // calculating coupon discount


        Order::where('id', $orderId)->update([
            'sub_total' => $totalOrderAmount,
            'coupon_code' => $request->coupon_code,
            'discount' => $discount,
            'total' => $totalOrderAmount - $discount,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Order is Submitted",
            'data' => new OrderResource(Order::where('id', $orderId)->first())
        ], 200);
    }



    public function giftCartCheckout(Request $request){

        $userInfo = User::where('email', $request->username)->orWhere('phone', $request->username)->first();
        if(!$userInfo){
            return response()->json([
                'success' => false,
                'message' => "Sorry No User Found",
            ], 200);
        }

        $orderId = Order::insertGetId([
            'order_no' => time().rand(100,999),
            'user_id' => $userInfo->id,
            'order_date' => date("Y-m-d H:i:s"),
            'estimated_dd' => date('Y-m-d', strtotime("+7 day", strtotime(date("Y-m-d")))),
            'payment_method' => NULL,
            'trx_id' => time().str::random(5),
            'order_status' => 0,
            'sub_total' => 0,
            'coupon_code' => NULL,
            'discount' => 0, //will be count at the bottom
            'delivery_fee' => 0,
            'vat' => 0,
            'tax' => 0,
            'total' => 0,
            'order_note' => isset($request->special_note) ? $request->special_note : '',
            'delivery_method' => isset($request->delivery_method) ? $request->delivery_method : '',
            'slug' => str::random(5) . time(),
            'created_at' => Carbon::now()
        ]);

        OrderProgress::insert([
            'order_id' => $orderId,
            'order_status' => 0,
            'created_at' => Carbon::now()
        ]);

        $totalOrderAmount = 0;
        $cartItems = Cart::where('user_unique_cart_no', $request->user_unique_cart_no)->orderBy('id', 'desc')->get();

        foreach($cartItems as $item){

            $productInfo = Product::where('id', $item->product_id)->first();
            $price = $productInfo->discount_price > 0 ? $productInfo->discount_price : $productInfo->price;

            // if($productInfo && $productInfo->author_id){
            //     $userInfo = User::where('id', $productInfo->author_id)->first();
            //     if($userInfo){
            //         $userInfo->balance = $userInfo->balance + (($price * $productInfo->comission) / 100);
            //         $userInfo->save();
            //     }
            // }

            OrderDetails::insert([
                'order_id' => $orderId,
                'product_id' => $item->product_id,
                'qty' => 1,
                'unit_id' => $productInfo->unit_id,
                'unit_price' => $price,
                'total_price' => $price,
                'author_comission' => ($price * $productInfo->comission) / 100,
                'created_at' => Carbon::now()
            ]);
            $totalOrderAmount = $totalOrderAmount + (1 * $price);
        }

        Cart::where('user_unique_cart_no', $request->user_unique_cart_no)->delete();


        // calculating coupon discount
        $discount = 0;
        $promoInfo = PromoCode::where('code', $request->coupon_code)->where('status', 1)->where('effective_date', '<=', date("Y-m-d"))->where('expire_date', '>=', date("Y-m-d"))->first();
        if($promoInfo){
            $alreadyUsed = Order::where('user_id', auth()->user()->id)->where('coupon_code', $request->coupon_code)->count();
            if($alreadyUsed == 0){
                if($promoInfo->type == 1){
                    $discount = $promoInfo->value;
                } else {
                    $discount = ($totalOrderAmount*$promoInfo->value)/100;
                }
            }
        }
        // calculating coupon discount


        Order::where('id', $orderId)->update([
            'sub_total' => $totalOrderAmount,
            'coupon_code' => $request->coupon_code,
            'discount' => $discount,
            'total' => $totalOrderAmount - $discount,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Order is Submitted",
            'data' => new OrderResource(Order::where('id', $orderId)->first())
        ], 200);
    }


    public function guestCartCheckout(Request $request){

        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $orderId = Order::insertGetId([
                'order_no' => time().rand(100,999),
                'order_date' => date("Y-m-d H:i:s"),
                'estimated_dd' => date('Y-m-d', strtotime("+7 day", strtotime(date("Y-m-d")))),
                'payment_method' => NULL,
                'trx_id' => time().str::random(5),
                'order_status' => 0,
                'sub_total' => 0,
                'coupon_code' => NULL,
                'discount' => 0, //will be count at the bottom
                'delivery_fee' => 0,
                'vat' => 0,
                'tax' => 0,
                'total' => 0,
                'order_note' => isset($request->special_note) ? $request->special_note : '',
                'delivery_method' => isset($request->delivery_method) ? $request->delivery_method : '',
                'slug' => str::random(5) . time(),
                'created_at' => Carbon::now()
            ]);

            OrderProgress::insert([
                'order_id' => $orderId,
                'order_status' => 0,
                'created_at' => Carbon::now()
            ]);

            $totalOrderAmount = 0;
            $cartItems = Cart::where('user_unique_cart_no', $request->user_unique_cart_no)->orderBy('id', 'desc')->get();

            foreach($cartItems as $item){

                Product::where('id', $item->product_id)->decrement("stock", $item->qty);

                OrderDetails::insert([
                    'order_id' => $orderId,
                    'product_id' => $item->product_id,

                    'color_id' => $item->color_id,
                    'size_id' => $item->size_id,
                    'region_id' => $item->region_id,
                    'sim_id' => $item->sim_id,
                    'storage_id' => $item->storage_id,
                    'warrenty_id' => $item->warrenty_id,
                    'device_condition_id' => $item->device_condition_id,

                    'qty' => $item->qty,
                    'unit_id' => $item->unit_id,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'created_at' => Carbon::now()
                ]);
                $totalOrderAmount = $totalOrderAmount + ($item->qty * $item->unit_price);
            }

            Cart::where('user_unique_cart_no', $request->user_unique_cart_no)->delete();

            // calculating coupon discount
            $discount = 0;
            // $promoInfo = PromoCode::where('code', $request->coupon_code)->where('status', 1)->where('effective_date', '<=', date("Y-m-d"))->where('expire_date', '>=', date("Y-m-d"))->first();
            // if($promoInfo){
            //     $alreadyUsed = Order::where('user_id', auth()->user()->id)->where('coupon_code', $request->coupon_code)->count();
            //     if($alreadyUsed == 0){
            //         if($promoInfo->type == 1){
            //             $discount = $promoInfo->value;
            //         } else {
            //             $discount = ($totalOrderAmount*$promoInfo->value)/100;
            //         }
            //     }
            // }
            // calculating coupon discount

            Order::where('id', $orderId)->update([
                'sub_total' => $totalOrderAmount,
                // 'coupon_code' => $request->coupon_code,
                'discount' => $discount,
                'total' => $totalOrderAmount - $discount,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Order is Submitted",
                'data' => new OrderResource(Order::where('id', $orderId)->first())
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function checkoutBuyNow(Request $request){

        $orderId = Order::insertGetId([
            'order_no' => time().rand(100,999),
            'user_id' => auth()->user()->id,
            'order_date' => date("Y-m-d H:i:s"),
            'estimated_dd' => date('Y-m-d', strtotime("+7 day", strtotime(date("Y-m-d")))),
            'payment_method' => NULL,
            'trx_id' => time().str::random(5),
            'order_status' => 0,
            'sub_total' => 0,
            'coupon_code' => NULL,
            'discount' => 0, //will be count at the bottom
            'delivery_fee' => 0,
            'vat' => 0,
            'tax' => 0,
            'total' => 0,
            'order_note' => isset($request->special_note) ? $request->special_note : '',
            'delivery_method' => isset($request->delivery_method) ? $request->delivery_method : '',
            'slug' => str::random(5) . time(),
            'created_at' => Carbon::now()
        ]);

        OrderProgress::insert([
            'order_id' => $orderId,
            'order_status' => 0,
            'created_at' => Carbon::now()
        ]);

        $totalOrderAmount = 0;

        $productInfo = Product::where('id', $request->product_id)->first();
        $productPrice = $productInfo->discount_price > 0 ? $productInfo->discount_price : $productInfo->price;

        // if($productInfo && $productInfo->author_id){
        //     $userInfo = User::where('id', $productInfo->author_id)->first();
        //     if($userInfo){
        //         $userInfo->balance = $userInfo->balance + (($productPrice * $productInfo->comission) / 100);
        //         $userInfo->save();
        //     }
        // }

        OrderDetails::insert([
            'order_id' => $orderId,
            'product_id' => $request->product_id,
            'color_id' => null,
            'size_id' => null,
            'region_id' => null,
            'sim_id' => null,
            'storage_id' => null,
            'warrenty_id' => null,
            'device_condition_id' => null,
            'qty' => $request->qty,
            'unit_id' => $request->unit_id,
            'unit_price' => $request->unit_price,
            'total_price' => $request->total_price,
            'author_comission' => ($productPrice * $productInfo->comission) / 100,
            'created_at' => Carbon::now()
        ]);
        $totalOrderAmount = $totalOrderAmount + ($request->qty * $request->unit_price);




        // calculating coupon discount
        $discount = 0;
        $promoInfo = PromoCode::where('code', $request->coupon_code)->where('status', 1)->where('effective_date', '<=', date("Y-m-d"))->where('expire_date', '>=', date("Y-m-d"))->first();
        if($promoInfo){
            $alreadyUsed = Order::where('user_id', auth()->user()->id)->where('coupon_code', $request->coupon_code)->count();
            if($alreadyUsed == 0){
                if($promoInfo->type == 1){
                    $discount = $promoInfo->value;
                } else {
                    $discount = ($totalOrderAmount*$promoInfo->value)/100;
                }
            }
        }
        // calculating coupon discount

        Order::where('id', $orderId)->update([
            'sub_total' => $totalOrderAmount,
            'coupon_code' => $request->coupon_code,
            'discount' => $discount,
            'total' => $totalOrderAmount - $discount,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Order is Submitted",
            'data' => new OrderResource(Order::where('id', $orderId)->first())
        ], 200);

    }

    public function guestCartCheckoutBuyNow(Request $request){

        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $orderId = Order::insertGetId([
                'order_no' => time().rand(100,999),
                'order_date' => date("Y-m-d H:i:s"),
                'estimated_dd' => date('Y-m-d', strtotime("+7 day", strtotime(date("Y-m-d")))),
                'payment_method' => NULL,
                'trx_id' => time().str::random(5),
                'order_status' => 0,
                'sub_total' => 0,
                'coupon_code' => NULL,
                'discount' => 0, //will be count at the bottom
                'delivery_fee' => 0,
                'vat' => 0,
                'tax' => 0,
                'total' => 0,
                'order_note' => isset($request->special_note) ? $request->special_note : '',
                'delivery_method' => isset($request->delivery_method) ? $request->delivery_method : '',
                'slug' => str::random(5) . time(),
                'created_at' => Carbon::now()
            ]);

            OrderProgress::insert([
                'order_id' => $orderId,
                'order_status' => 0,
                'created_at' => Carbon::now()
            ]);

            $totalOrderAmount = 0;


            $productInfo = Product::where('id', $request->product_id)->first();
            $productPrice = $productInfo->discount_price > 0 ? $productInfo->discount_price : $productInfo->price;

            // if($productInfo && $productInfo->author_id){
            //     $userInfo = User::where('id', $productInfo->author_id)->first();
            //     if($userInfo){
            //         $userInfo->balance = $userInfo->balance + (($productPrice * $productInfo->comission) / 100);
            //         $userInfo->save();
            //     }
            // }

            OrderDetails::insert([
                'order_id' => $orderId,
                'product_id' => $request->product_id,
                'color_id' => $request->color_id,
                'size_id' => $request->size_id,
                'region_id' => $request->region_id,
                'sim_id' => $request->sim_id,
                'storage_id' => $request->storage_id,
                'warrenty_id' => $request->warrenty_id,
                'device_condition_id' => $request->device_condition_id,
                'qty' => $request->qty,
                'unit_id' => $request->unit_id,
                'unit_price' => $request->unit_price,
                'total_price' => $request->total_price,
                'author_comission' => ($productPrice * $productInfo->comission) / 100,
                'created_at' => Carbon::now()
            ]);
            $totalOrderAmount = $totalOrderAmount + ($request->qty * $request->unit_price);

            // calculating coupon discount
            $discount = 0;
            $promoInfo = PromoCode::where('code', $request->coupon_code)->where('status', 1)->where('effective_date', '<=', date("Y-m-d"))->where('expire_date', '>=', date("Y-m-d"))->first();
            if($promoInfo){
                if($promoInfo->type == 1){
                    $discount = $promoInfo->value;
                } else {
                    $discount = ($totalOrderAmount*$promoInfo->value)/100;
                }
            }
            // calculating coupon discount

            Order::where('id', $orderId)->update([
                'sub_total' => $totalOrderAmount,
                'coupon_code' => $request->coupon_code,
                'discount' => $discount,
                'total' => $totalOrderAmount - $discount,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Order is Submitted",
                'data' => new OrderResource(Order::where('id', $orderId)->first())
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }

    }

    public function addToWishList(Request $request){

        $wishList = WishList::where([['user_id', auth()->user()->id], ['product_id', $request->product_id]])->first();

        if($wishList){
            return response()->json([
                'success' => false,
                'message' => "Already Added to WishList",
            ], 200);
        } else {
            WishList::insert([
                'user_id' => auth()->user()->id,
                'product_id' => $request->product_id,
                'slug' => str::random(5) . time(),
                'created_at' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Added to WishList",
            ], 200);
        }
    }

    public function getMyWishList(){

        $data = DB::table('wish_lists')
                ->join('products', 'wish_lists.product_id', '=', 'products.id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'wish_lists.id as wish_lists_id', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name',
                'product_warrenties.name as product_warrenty')
                ->where('wish_lists.user_id', auth()->user()->id)
                ->orderBy('wish_lists.id', 'desc')
                ->paginate(15);

        // $wishLists = DB::table('wish_lists')
        //                 ->join('products', 'wish_lists.product_id', '=', 'products.id')
        //                 ->leftJoin('units', 'products.unit_id', '=', 'units.id')
        //                 ->where('wish_lists.user_id', auth()->user()->id)
        //                 ->select('wish_lists.*', 'units.id as unit_id', 'units.name as unit_name', 'products.image', 'products.name as product_name', 'products.name', 'products.discount_price', 'products.price as product_price', 'products.price')
        //                 ->orderBy('wish_lists.id', 'desc')
        //                 ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => WishListResource::collection($data)->resource
        ], 200);

    }

    public function deleteMyWishList(Request $request){

        WishList::where('id', $request->wishlist_id)->where('user_id', auth()->user()->id)->delete();
        return response()->json([
            'success' => true,
            'message' => "Item has removed from WishList",
        ], 200);

    }

}
