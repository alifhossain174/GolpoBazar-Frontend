<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AudioBooksController extends Controller
{
    public function audioBooks(Request $request){

        $categories = DB::table('categories')->where('status', 1)->where('is_audio', 1)->orderBy('serial', 'asc')->get();
        $bookAuthors = DB::table('users')->where('status', 1)->where('user_type', 2)->inRandomOrder()->get();
        $publishers = DB::table('brands')->where('status', 1)->orderBy('serial', 'asc')->get();

        $query = DB::table('products')
                    ->leftJoin('users', 'products.author_id', 'users.id')
                    ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                    ->leftJoin('brands', 'products.brand_id', 'brands.id')
                    ->select('products.*', 'users.name as author_name', 'categories.name as category_name', 'brands.name as publisher')
                    ->where('products.status', 1)
                    ->where('products.is_audio', 1);

        // ============== applying filters from url parameter start ================
        $parameters = '';
        $categorySlug = isset($request->category) ? $request->category : '';
        $authorSlug = isset($request->author) ? $request->author : '';
        $publisherSlug = isset($request->publisher) ? $request->publisher : '';
        $sort_by = isset($request->sort_by) ? $request->sort_by : '';
        $search_keyword = isset($request->search_keyword) ? $request->search_keyword : '';

        if($categorySlug){
            $query->whereIn('categories.slug', explode(",", $categorySlug));
            $parameters == '' ? $parameters .= '?category=' . $categorySlug : $parameters .= '&category=' . $categorySlug;
        }
        if($authorSlug){
            $query->whereIn('users.id', explode(",", $authorSlug));
            $parameters == '' ? $parameters .= '?author=' . $authorSlug : $parameters .= '&author=' . $authorSlug;
        }
        if($publisherSlug){
            $query->whereIn('brands.slug', explode(",",$publisherSlug));
            $parameters == '' ? $parameters .= '?publisher=' . $publisherSlug : $parameters .= '&publisher=' . $publisherSlug;
        }
        // sorting
        if($sort_by && $sort_by > 0){
            if($sort_by == 1){
                $query->orderBy('products.id', 'desc');
            }
            if($sort_by == 2){
                $query->orderBy('products.discount_price', 'asc')->orderBy('products.price', 'asc');
            }
            if($sort_by == 3){
                $query->orderBy('products.discount_price', 'desc')->orderBy('products.price', 'desc');
            }
            $parameters == '' ? $parameters .= '?sort_by=' . $sort_by : $parameters .= '&sort_by=' . $sort_by;
        } else {
            $query->orderBy('products.id', 'desc');
        }
        // search keyword
        if($search_keyword){
            $query->where('products.name', 'LIKE', '%'.$search_keyword.'%');
            $parameters == '' ? $parameters .= '?search_keyword=' . $search_keyword : $parameters .= '&search_keyword=' . $search_keyword;
        }

        $books = $query->paginate(12);
        $books->withPath('/shop'.$parameters);
        return view('audio_books.shop', compact('books', 'bookAuthors', 'categories', 'publishers', 'categorySlug', 'publisherSlug', 'authorSlug', 'sort_by', 'search_keyword'));

    }

    public function filterAudioBooks(Request $request){

        // main query
        $query = DB::table('products')
                ->leftJoin('users', 'products.author_id', 'users.id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('brands', 'products.brand_id', 'brands.id')
                ->select('products.*', 'users.name as author_name', 'categories.name as category_name', 'brands.name as publisher')
                ->where('products.status', 1)
                ->where('products.is_audio', 1);


        // ============== applying filters from url parameter start ================
        $parameters = '';
        $categorySlug = isset($request->category) ? $request->category : '';
        $authorSlug = isset($request->author) ? $request->author : '';
        $publisherSlug = isset($request->publisher) ? $request->publisher : '';
        $sort_by = isset($request->sort_by) ? $request->sort_by : '';
        $search_keyword = isset($request->search_keyword) ? $request->search_keyword : '';

        if($categorySlug){
            $query->whereIn('categories.slug', explode(",", $categorySlug));
            $parameters == '' ? $parameters .= '?category=' . $categorySlug : $parameters .= '&category=' . $categorySlug;
        }
        if($authorSlug){
            $query->whereIn('users.id', explode(",", $authorSlug));
            $parameters == '' ? $parameters .= '?author=' . $authorSlug : $parameters .= '&author=' . $authorSlug;
        }
        if($publisherSlug){
            $query->whereIn('brands.slug', explode(",",$publisherSlug));
            $parameters == '' ? $parameters .= '?publisher=' . $publisherSlug : $parameters .= '&publisher=' . $publisherSlug;
        }
        // sorting
        if($sort_by && $sort_by > 0){
            if($sort_by == 1){
                $query->orderBy('products.id', 'desc');
            }
            if($sort_by == 2){
                $query->orderBy('products.discount_price', 'asc')->orderBy('products.price', 'asc');
            }
            if($sort_by == 3){
                $query->orderBy('products.discount_price', 'desc')->orderBy('products.price', 'desc');
            }
            $parameters == '' ? $parameters .= '?sort_by=' . $sort_by : $parameters .= '&sort_by=' . $sort_by;
        } else {
            $query->orderBy('products.id', 'desc');
        }
        // search keyword
        if($search_keyword){
            $query->where('products.name', 'LIKE', '%'.$search_keyword.'%');
            $parameters == '' ? $parameters .= '?search_keyword=' . $search_keyword : $parameters .= '&search_keyword=' . $search_keyword;
        }

        $books = $query->paginate(12);
        $books->withPath('/shop'.$parameters);

        $returnHTML = view('shop.products', compact('books'))->render();
        return response()->json(['rendered_view' => $returnHTML]);

    }
}
