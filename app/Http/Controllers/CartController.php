<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function addToCart($id){

        $product = DB::table('products')
                        ->leftJoin('users', 'products.author_id', 'users.id')
                        ->select('products.*', 'users.name as author_name')
                        ->where('products.id', $id)
                        ->first();

        $cart = session()->get('cart', []);

        if(isset($cart[$id])) {
            $cart[$id]['quantity']++;
        } else {
            $cart[$id] = [
                "name" => $product->name,
                "author_name" => $product->author_name,
                "slug" => $product->slug,
                "quantity" => 1,
                "price" => $product->price > 0 ? $product->price : 0,
                "discount_price" => $product->discount_price > 0 ? $product->discount_price : 0,
                "image" => $product->image,
            ];
        }

        session()->put('cart', $cart);

        $returnHTML = view('sidebar_cart')->render();
        return response()->json([
            'rendered_cart' => $returnHTML,
            'cartTotalQty' => count(session('cart')),
        ]);
    }

    public function removeCartTtem($id){
        $cart = session()->get('cart');
        if(isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        $returnHTML = view('sidebar_cart')->render();
        $viewCartItems = view('cart_items')->render();
        // $viewCartCalculation = view('cart.cart_calculation')->render();
        // $checkoutCartItems = view('checkout.cart_items')->render();
        // $checkoutTotalAmount = view('checkout.order_total')->render();
        return response()->json([
            'rendered_cart' => $returnHTML,
            'viewCartItems' => $viewCartItems,
            // 'viewCartCalculation' => $viewCartCalculation,
            // 'checkoutCartItems' => $checkoutCartItems,
            // 'checkoutTotalAmount' => $checkoutTotalAmount,
            'cartTotalQty' => count(session('cart'))
        ]);
    }

    public function updateCartQty(Request $request){
        $cart = session()->get('cart');
        if(isset($cart[$request->cart_id])) {
            $cart[$request->cart_id]['quantity'] = $request->cart_qty;
            session()->put('cart', $cart);
        }

        $returnHTML = view('sidebar_cart')->render();
        // $viewCartItems = view('cart.cart_items')->render();
        // $viewCartCalculation = view('cart.cart_calculation')->render();
        // $checkoutCartItems = view('checkout.cart_items')->render();
        // $checkoutTotalAmount = view('checkout.order_total')->render();
        return response()->json([
            'rendered_cart' => $returnHTML,
            // 'viewCartItems' => $viewCartItems,
            // 'viewCartCalculation' => $viewCartCalculation,
            // 'checkoutCartItems' => $checkoutCartItems,
            // 'checkoutTotalAmount' => $checkoutTotalAmount,
            'success' => 'Cart Qty Updated'
        ]);
    }


    public function addToCartWithQty(Request $request){

        $product = DB::table('products')
                    // ->leftJoin('categories', 'products.category_id', 'categories.id') // joining for data layer info
                    // ->leftJoin('brands', 'products.brand_id', 'brands.id') // joining for data layer info
                    ->select('products.*') //'categories.name as category_name', 'brands.name as brand_name'
                    ->where('products.id', $request->product_id)
                    ->first();

        $cart = session()->get('cart', []);

        if(isset($cart[$request->product_id])) {

            $cart[$request->product_id]['quantity'] = (int) $request->qty;
            $cart[$request->product_id]['price'] = $request->price;
            $cart[$request->product_id]['color_id'] = $request->color_id != 'null' ? $request->color_id : null;
            $cart[$request->product_id]['size_id'] = $request->size_id != 'null' ? $request->size_id : null;

        } else {
            $cart[$request->product_id] = [
                "name" => $product->name,
                "slug" => $product->slug,
                "quantity" => (int) $request->qty,
                "price" => $request->price,
                "discount_price" => $request->discount_price,
                "image" => $product->image,
                // variant
                "color_id" => $request->color_id != 'null' ? $request->color_id : null,
                "size_id" => $request->size_id != 'null' ? $request->size_id : null,
                // for data layer
                // "brand_name" => $product->brand_name,
                // "category_name" => $product->category_name,
            ];
        }

        session()->put('cart', $cart);

        $returnHTML = view('sidebar_cart')->render();
        return response()->json([
            'rendered_cart' => $returnHTML,
            'cartTotalQty' => count(session('cart')),

            // for data layer
            // 'p_name_data_layer' => $product->name,
            // 'p_price_data_layer' => $request->discount_price > 0 ? $request->discount_price : $request->price,
            // 'p_brand_name' => $product->brand_name,
            // 'p_category_name' => $product->category_name,
            // 'p_qauntity' => (int) $request->qty,
        ]);
    }

    public function viewCart(){
        if(!session('cart') || (session('cart') && count(session('cart')) <= 0)){
            Toastr::error('No Books Found in Cart', 'Failed to Checkout');
            return redirect('/');
        }

        return view('view_cart');
    }

    public function clearCart(){
        session()->put('cart', []);
        Toastr::success('All items removed from Cart', 'Success');
        return back();
    }
}
