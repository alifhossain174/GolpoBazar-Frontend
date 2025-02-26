<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrontendController extends Controller
{
    public function index()
    {
        $randomBook = DB::table('products')
                        ->leftJoin('users', 'products.author_id', 'users.id')
                        ->leftJoin('categories', 'products.category_id', 'categories.id')
                        ->select('products.*', 'users.name as author_name', 'categories.name as category_name')
                        ->where('products.status', 1)
                        ->inRandomOrder()
                        ->first();

        $categories = DB::table('categories')->where('status', 1)->orderBy('serial', 'asc')->get();
        $bookAuthors = DB::table('users')->where('status', 1)->where('user_type', 2)->inRandomOrder()->limit(8)->get();

        return view('index', compact('randomBook', 'categories', 'bookAuthors'));
    }

    public function bookDetails($slug){

        $book = DB::table('products')
                ->leftJoin('users', 'products.author_id', 'users.id')
                ->leftJoin('categories', 'products.category_id', 'categories.id')
                ->leftJoin('brands', 'products.brand_id', 'brands.id')
                ->select('products.*', 'users.name as author_name', 'categories.name as category_name', 'brands.name as publisher')
                ->where('products.slug', $slug)
                ->first();

        $relatedBooks = DB::table('products')
                        ->leftJoin('users', 'products.author_id', 'users.id')
                        ->leftJoin('categories', 'products.category_id', 'categories.id')
                        ->select('products.*', 'users.name as author_name', 'categories.name as category_name')
                        ->where('products.category_id', $book->category_id)
                        ->where('products.id', '!=', $book->id)
                        ->inRandomOrder()
                        ->limit(10)
                        ->get();

        return view('book_details.details', compact('book', 'relatedBooks'));

    }
}
