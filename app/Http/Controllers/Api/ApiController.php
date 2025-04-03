<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderProgressResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\GeneralInfoResource;
use App\Http\Resources\BrandResource;
use App\Http\Resources\FlagResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\ProductVariantResource;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\BillingAddress;
use App\Models\Brand;
use App\Models\ContactRequest;
use App\Models\Flag;
use App\Models\AboutUs;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\OrderPayment;
use App\Models\Adsmob;
use App\Models\OrderProgress;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\PromoCode;
use App\Models\SubscribedUsers;
use App\Models\ShippingInfo;
use App\Models\Subcategory;
use App\Models\WishList;
use App\Models\PaymentGateway;
use App\Models\ProductQuestionAnswer;
use App\Models\SubscriptionPurchase;
use App\Models\ProductVariant;
use Illuminate\Support\Str;
use Image;

class ApiController extends BaseController
{
    const AUTHORIZATION_TOKEN = 'GolpoBazar-SBW7583837NUDD82';

    public function userProfileInfo(){

        $userInfo = User::where('id', auth()->user()->id)->first();

        $name = $userInfo->name;
        $email = $userInfo->email;
        $phone = $userInfo->phone;
        $image = $userInfo->image;
        $balance = $userInfo->balance;
        $address = $userInfo->address;
        $dob = $userInfo->dob;
        $gender = $userInfo->gender;
        $totalProductInWishList = WishList::where('user_id', $userInfo->id)->count();
        $totalOrders = Order::where('user_id', $userInfo->id)->count();

        $data = array(
            'id' => $userInfo->id,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'balance' => $balance,
            'image' => $image,
            'address' => $address,
            'dob' => $dob,
            'gender' => $gender,
            'totalProductInWishList' => $totalProductInWishList,
            'totalOrders' => $totalOrders,
        );

        return $this->sendResponse($data, 'User Profile Retrieved Successfully.');

    }

    public function userProfileUpdate(Request $request){

        $userInfo = User::where('id', auth()->user()->id)->first();
        $userImage = $userInfo->image;
        if ($request->hasFile('image')){

            if($userImage && file_exists(public_path($userImage))){
                unlink(public_path($userImage));
            }

            $get_image = $request->file('image');
            $image_name = str::random(5) . time() . '.' . $get_image->getClientOriginalExtension();
            $location = public_path('userProfileImages/');
            Image::make($get_image)->save($location . $image_name, 50);
            $userImage = "userProfileImages/" . $image_name;
        }

        $user_id = auth()->user()->id;
        $name = $request->name;
        $phone = $request->phone;
        $email = $request->email;
        $address = $request->address;
        $dob = $request->dob;
        $gender = $request->gender;
        $current_password = $request->current_password;
        $new_password = $request->new_password;

        if($email != '' && $userInfo->email != $email){
            $email_check = User::where('email', $email)->first();
            if($email_check){
                return response()->json([
                    'success'=> false,
                    'message'=> 'Email already used ! Please use another Email'
                ]);
            }
        }
        if($phone != '' && $userInfo->phone != $phone){
            $phone_check = User::where('phone', $phone)->first();
            if($phone_check){
                return response()->json([
                    'success'=> false,
                    'message'=> 'Mobile No already used ! Please use another Mobile No'
                ]);
            }
        }

        if($current_password != '' && $new_password != ''){
            if(Hash::check($current_password, $userInfo->password)){
                User::where('id', $user_id)->update([
                    'password' => Hash::make($new_password),
                    'updated_at' => Carbon::now()
                ]);
            } else {
                return response()->json([
                    'success'=> false,
                    'message'=> 'Your Current Password is Incorrect'
                ]);
            }
        }

        if(($email == '' || $email == NULL) && ($phone == '' || $phone == NULL)){
            return response()->json([
                'success'=> false,
                'message'=> 'Both Email & Phone Cannot be Null'
            ]);
        } else {
            $userInfo->name = $name;
            $userInfo->phone = $phone;
            $userInfo->email = $email;
            $userInfo->image = $userImage;
            $userInfo->address = $address;
            $userInfo->dob = $dob;
            $userInfo->gender = $gender;
            $userInfo->updated_at = Carbon::now();
            $userInfo->save();

            return response()->json([
                'success'=> true,
                'message'=> 'Profile Updated Successfully',
                'data' => $userInfo
            ]);
        }

    }

    public function getCategoryTree(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $categories = Category::orderBy('serial', 'asc')->where('status', 1)->get();
            return response()->json([
                'success' => true,
                'data' => CategoryResource::collection($categories)
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getCategoryList(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $book_type = $request->book_type;

            // $categories = Category::orderBy('serial', 'asc')->where('status', 1)->get();
            $categories = DB::table('categories')
                            ->where('status', 1)
                            ->when($book_type, function($query) use ($book_type){
                                return $query->where('is_audio', $book_type);
                            })
                            ->orderBy('serial', 'asc')
                            ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getFeaturedSubcategory(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $subcategories = DB::table('subcategories')
                                ->join('categories', 'subcategories.category_id', '=', 'categories.id')
                                ->select('subcategories.*', 'categories.name as category_name')
                                ->where('subcategories.status', 1)
                                ->where('subcategories.featured', 1)
                                ->orderBy('subcategories.name', 'desc')
                                ->get();

            return response()->json([
                'success' => true,
                'data' => $subcategories
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getSubcategoryOfCategory(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $subcategories = DB::table('subcategories')
                                ->join('categories', 'subcategories.category_id', '=', 'categories.id')
                                ->select('subcategories.*', 'categories.name as category_name')
                                ->where('category_id', $request->category_id)
                                ->where('subcategories.status', 1)
                                ->orderBy('subcategories.name', 'desc')
                                ->get();

            return response()->json([
                'success' => true,
                'data' => $subcategories
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getChildcategoryOfSubcategory(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = DB::table('child_categories')
                        ->join('categories', 'child_categories.category_id', '=' , 'categories.id')
                        ->join('subcategories', 'child_categories.subcategory_id', '=' , 'subcategories.id')
                        ->select('child_categories.*', 'categories.name as category_name', 'subcategories.name as subcategory_name')
                        ->where('child_categories.category_id', $request->category_id)
                        ->where('child_categories.subcategory_id', $request->subcategory_id)
                        ->where('child_categories.status', 1)
                        ->orderBy('child_categories.name', 'asc')
                        ->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getAllProducts(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $book_type = $request->book_type;

            $data = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                ->where('products.status', 1)
                ->when($book_type, function($query) use ($book_type){
                    return $query->where('products.is_audio', $book_type);
                })
                ->orderBy('products.id', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($data)->resource
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }
    public function getAllProductsOfAuthor(Request $request, $id){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $book_type = $request->book_type;

            $data = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                ->where('products.status', 1)
                ->where('products.author_id', $id)
                ->when($book_type, function($query) use ($book_type){
                    return $query->where('products.is_audio', $book_type);
                })
                ->orderBy('products.id', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($data)->resource
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }
    public function getAllProductsOfPublisher(Request $request, $id){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $book_type = $request->book_type;

            $data = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                ->where('products.status', 1)
                ->where('products.brand_id', $id)
                ->when($book_type, function($query) use ($book_type){
                    return $query->where('products.is_audio', $book_type);
                })
                ->orderBy('products.id', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($data)->resource
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }
    public function getAllPremiumProducts(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $book_type = $request->book_type;

            $data = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                ->where('products.status', 1)
                ->where('products.is_premium', 1)
                ->when($book_type, function($query) use ($book_type){
                    return $query->where('products.is_audio', $book_type);
                })
                ->orderBy('products.id', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($data)->resource
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }
    public function getAllFreeProducts(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $book_type = $request->book_type;

            $data = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                ->where('products.status', 1)
                ->where('products.discount_price', '<=', 0)
                ->when($book_type, function($query) use ($book_type){
                    return $query->where('products.is_audio', $book_type);
                })
                ->orderBy('products.id', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($data)->resource
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }
    public function getAllRandomProducts(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $book_type = $request->book_type;

            $data = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                ->where('products.status', 1)
                ->when($book_type, function($query) use ($book_type){
                    return $query->where('products.is_audio', $book_type);
                })
                ->inRandomOrder()
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($data)->resource
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getRelatedProducts(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $prodInfo = Product::where('id', $request->product_id)->first();
            $brand_id = $prodInfo->brand_id;
            $categoryId = $prodInfo->category_id;
            $book_type = $request->book_type;

            $data = DB::table('products')
                        ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                        ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                        ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                        ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                        ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                        ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                        ->where('products.status', 1)
                        ->when($brand_id, function($query) use ($brand_id, $categoryId){
                            if($brand_id > 0)
                                return $query->where('products.brand_id', $brand_id);
                            else
                            return $query->where('products.category_id', $categoryId);
                        })
                        ->when($book_type, function($query) use ($book_type){
                            return $query->where('products.is_audio', $book_type);
                        })
                        ->where('products.id', '!=', $request->product_id)
                        ->inRandomOrder()
                        ->skip(0)
                        ->limit(5)
                        ->get();

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($data)
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getYouMayLikeProducts(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $prodInfo = Product::where('id', $request->product_id)->first();
            $categoryId = $prodInfo->category_id;
            $book_type = $request->book_type;

            $data = DB::table('products')
                        ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                        ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                        ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                        ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                        ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                        ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                        ->where('products.status', 1)
                        ->where('products.id', '!=', $request->product_id)
                        ->where('products.category_id', $categoryId)
                        ->when($book_type, function($query) use ($book_type){
                            return $query->where('products.is_audio', $book_type);
                        })
                        ->skip(0)
                        ->limit(6)
                        ->inRandomOrder()
                        ->get();

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($data)
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function categoryWiseProducts(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $categoryInfo = Category::where('slug', $request->category_slug)->first();
            $book_type = $request->book_type;

            $data = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                ->where('categories.id', $categoryInfo ? $categoryInfo->id : 0)
                ->when($book_type, function($query) use ($book_type){
                    return $query->where('products.is_audio', $book_type);
                })
                ->where('products.status', 1)
                ->orderBy('products.id', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($data)->resource
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function subcategoryWiseProducts(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $subCategoryInfo = Subcategory::where('slug', $request->subcategory_slug)->first();

            $data = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                ->where('subcategories.id', $subCategoryInfo ? $subCategoryInfo->id : 0)
                ->where('products.status', 1)
                ->orderBy('products.id', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($data)->resource
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function productDetails(Request $request, $id){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                ->where('products.id', $id)
                ->orWhere('products.slug', $id)
                ->first();

            return response()->json([
                'success' => true,
                'data' => new ProductResource($data)
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function flagWiseProducts(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $book_type = $request->book_type;

            $data = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                // ->where('products.flag_id', $request->flag)
                ->where('products.flag_id', 'LIKE', '%'.$request->flag.'%')
                ->where('products.status', 1)
                ->when($book_type, function($query) use ($book_type){
                    return $query->where('products.is_audio', $book_type);
                })
                ->orderBy('products.id', 'desc')
                ->skip(0)
                ->limit(6)
                ->get();

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($data)
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function featuredFlagWiseProducts(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = Flag::where('featured', 1)->where('status', 1)->get();

            return response()->json([
                'success' => true,
                'data' => FlagResource::collection($data)
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function flagWiseAllProducts(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $book_type = $request->book_type;

            $data = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                ->where('products.flag_id', 'LIKE', '%'.$request->flag.'%')
                ->when($book_type, function($query) use ($book_type){
                    return $query->where('products.is_audio', $book_type);
                })
                ->where('products.status', 1)
                ->orderBy('products.id', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($data)->resource
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getAllFlags(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $flags = Flag::orderBy('serial', 'asc')->where('status', 1)->get();
            return response()->json([
                'success' => true,
                'data' => $flags
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getAllBrands(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $brands = Brand::orderBy('serial', 'asc')->where('status', 1)->paginate(20);

            return response()->json([
                'success' => true,
                'data' => BrandResource::collection($brands)->resource
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function searchPublisher(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $brands = Brand::where('name', 'LIKE', '%'.$request->search_keyword.'%')->orderBy('serial', 'asc')->where('status', 1)->paginate(20);

            return response()->json([
                'success' => true,
                'data' => BrandResource::collection($brands)->resource
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function searchProducts(Request $request){ //post method
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $brandInfo = Brand::where('slug', $request->brand_slug)->first();
            $brand_id = $brandInfo ? $brandInfo->id : 0;
            $book_type = $request->book_type;

            $data = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                ->where('products.status', 1)
                ->where('products.name', 'LIKE', '%'.$request->search_keyword.'%')
                ->orwhere('categories.name', 'LIKE', '%'.$request->search_keyword.'%')
                ->orwhere('subcategories.name', 'LIKE', '%'.$request->search_keyword.'%')
                ->orwhere('products.tags', 'LIKE', '%'.$request->search_keyword.'%')
                ->orwhere('brands.name', 'LIKE', '%'.$request->search_keyword.'%')
                ->when($brand_id, function($query) use ($brand_id){
                    if($brand_id > 0)
                        return $query->where('products.brand_id', $brand_id);
                })
                ->when($book_type, function($query) use ($book_type){
                    return $query->where('products.is_audio', $book_type);
                })
                ->orderBy('products.id', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($data)->resource
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function searchLiveProducts(Request $request){ //post method
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $brandInfo = Brand::where('slug', $request->brand_slug)->first();
            $brand_id = $brandInfo ? $brandInfo->id : 0;
            $category_id = $request->category_id;
            $keyword = $request->search_keyword;
            $book_type = $request->book_type;

            if($brand_id != '' || $keyword != '' || $category_id){

                $data = DB::table('products')
                    ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                    ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                    ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                    ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                    ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                    ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                    ->where('products.status', 1)

                    // ->when($keyword, function($query) use ($keyword){
                    //     return $query->where('products.name', 'LIKE', '%'.$keyword.'%')
                    //     ->orwhere('categories.name', 'LIKE', '%'.$keyword.'%')
                    //     ->orwhere('subcategories.name', 'LIKE', '%'.$keyword.'%')
                    //     ->orwhere('products.tags', 'LIKE', '%'.$keyword.'%')
                    //     ->orwhere('brands.name', 'LIKE', '%'.$keyword.'%');
                    // })

                    ->where('products.name', 'LIKE', '%'.$keyword.'%')
                    ->when($category_id, function($query) use ($category_id){
                        if($category_id > 0)
                            return $query->where('products.category_id', $category_id);
                    })
                    ->when($brand_id, function($query) use ($brand_id){
                        if($brand_id > 0)
                            return $query->where('products.brand_id', $brand_id);
                    })
                    ->when($book_type, function($query) use ($book_type){
                        return $query->where('products.is_audio', $book_type);
                    })

                    ->orderBy('products.id', 'desc')
                    ->skip(0)
                    ->limit(5)
                    ->get();

                return response()->json([
                    'success' => true,
                    'data' => ProductResource::collection($data)
                ], 200);

            } else {
                return response()->json([
                    'success' => true,
                    'data' => array()
                ], 200);
            }



        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function searchProductsGet(Request $request){  //get method
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $brandInfo = Brand::where('slug', $request->brand_slug)->first();
            $brand_id = $brandInfo ? $brandInfo->id : 0;
            $book_type = $request->book_type;

            $data = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                ->where('products.status', 1)
                ->where('products.name', 'LIKE', '%'.$request->search_keyword.'%')
                ->orwhere('categories.name', 'LIKE', '%'.$request->search_keyword.'%')
                ->orwhere('subcategories.name', 'LIKE', '%'.$request->search_keyword.'%')
                ->orwhere('products.tags', 'LIKE', '%'.$request->search_keyword.'%')
                ->orwhere('brands.name', 'LIKE', '%'.$request->search_keyword.'%')
                ->when($brand_id, function($query) use ($brand_id){
                    if($brand_id > 0)
                        return $query->where('products.brand_id', $brand_id);
                })
                ->when($book_type, function($query) use ($book_type){
                    return $query->where('products.is_audio', $book_type);
                })
                ->orderBy('products.id', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($data)->resource
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function termsAndCondition(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = DB::table('terms_and_policies')->where('id', 1)->select('terms')->first();

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function privacyPolicy(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = DB::table('terms_and_policies')->where('id', 1)->select('privacy_policy')->first();

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function shippingPolicy(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = DB::table('terms_and_policies')->where('id', 1)->select('shipping_policy')->first();

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function returnPolicy(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = DB::table('terms_and_policies')->where('id', 1)->select('return_policy')->first();

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function aboutUs(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = AboutUs::where('id', 1)->first();

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getAllFaq(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = DB::table('faqs')->orderBy('id', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function generalInfo(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = DB::table('general_infos')->where('id', 1)->first();

            return response()->json([
                'success' => true,
                'data' => new GeneralInfoResource($data)
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getAllSliders(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $book_type = $request->book_type;

            $data = DB::table('banners')
                        ->where('type', 1)
                        ->where('status', 1)
                        ->when($book_type, function($query) use ($book_type){
                            return $query->where('is_audio', $book_type);
                        })
                        ->orderBy('serial', 'asc')
                        ->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getAllBanners(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $book_type = $request->book_type;

            $data = DB::table('banners')
                        ->where('type', 2)
                        ->where('status', 1)
                        ->when($book_type, function($query) use ($book_type){
                            return $query->where('is_audio', $book_type);
                        })
                        ->orderBy('serial', 'asc')
                        ->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getPromotionalBanner(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = DB::table('promotional_banners')->where('id', 1)->first();

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function submitContactRequest(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            ContactRequest::insert([
                'name' => $request->name,
                'email' => $request->email,
                'message' => $request->message,
                'phone' => isset($request->phone) ? $request->phone : NULL,
                'company_name' => isset($request->company_name) ? $request->company_name : NULL,
                'status' => 0,
                'created_at' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Request is Submitted"
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function orderCheckout(Request $request){

        date_default_timezone_set("Asia/Dhaka");

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
            'discount' => 0,
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

        $index = 0;
        $totalOrderAmount = 0;

        // for a reason=== dont change the code
        $productIdArray = explode(",", $request->product_id[0]);
        $qtyArray = explode(",", $request->qty[0]);
        $unitPriceArray = explode(",", $request->unit_price[0]);
        $unitIdArray = explode(",", $request->unit_id[0]);

        // variants added later
        $colorIdArray = explode(",", $request->color_id[0]);
        $regionIdArray = explode(",", $request->region_id[0]);
        $simIdArray = explode(",", $request->sim_id[0]);
        $sizeIdArray = explode(",", $request->size_id[0]);
        $storageIdArray = explode(",", $request->storage_id[0]);
        $warrentyIdArray = explode(",", $request->warrenty_id[0]);
        $deviceConditionIdArray = explode(",", $request->device_condition_id[0]);

        foreach($productIdArray as $productId){

            $quantity = (double) trim($qtyArray[$index]);
            $unitPrice = (double) trim($unitPriceArray[$index]);
            $pId = (int) trim($productId);
            $unitId = (double) trim($unitIdArray[$index]);

            // variants added later | chosing default variant while no variant is selected although product has variant
            $prodInfo = Product::where('id', $pId)->first();
            $colorId = (double) trim($colorIdArray[$index]);
            $regionId = (double) trim($regionIdArray[$index]);
            $simId = (double) trim($simIdArray[$index]);
            $storageId = (double) trim($storageIdArray[$index]);
            $warrentyId = (double) trim($warrentyIdArray[$index]);
            $deviceConditionId = (double) trim($deviceConditionIdArray[$index]);
            $sizeId = (double) trim($sizeIdArray[$index]);

            if($prodInfo->has_variant == 1){
                $variants = ProductVariant::where('product_id', $prodInfo->id)->where('stock', '>', 0)->count();
                if($variants > 0){
                    if(!$colorId && !$regionId && !$simId && !$storageId && !$warrentyId && !$deviceConditionId && !$sizeId){
                        $defaultVariant = ProductVariant::where('product_id', $prodInfo->id)->where('stock', '>', 0)->orderBy('price', 'desc')->first();
                        $colorId = $defaultVariant->color_id;
                        $regionId = $defaultVariant->region_id;
                        $simId = $defaultVariant->sim_id;
                        $sizeId = $defaultVariant->size_id;
                        $storageId = $defaultVariant->storage_type_id;
                        $warrentyId = $defaultVariant->warrenty_id;
                        $deviceConditionId = $defaultVariant->device_condition_id;
                        $unitPrice = $defaultVariant->discounted_price > 0 ? $defaultVariant->discounted_price : $defaultVariant->price;
                    }
                }
            }

            // Product::where('id', $pId)->decrement("stock", $quantity);

            $productInfo = Product::where('id', $productId)->first();
            $productPrice = $productInfo->discount_price > 0 ? $productInfo->discount_price : $productInfo->price;

            if($productInfo && $productInfo->author_id){
                $userInfo = User::where('id', $productInfo->author_id)->first();
                if($userInfo){
                    $userInfo->balance = $userInfo->balance + (($productPrice * $productInfo->comission) / 100);
                    $userInfo->save();
                }
            }

            OrderDetails::insert([
                'order_id' => $orderId,
                'product_id' => $pId,

                // VARIANT
                'color_id' => $colorId,
                'region_id' => $regionId,
                'sim_id' => $simId,
                'size_id' => $sizeId,
                'storage_id' => $storageId,
                'warrenty_id' => $warrentyId,
                'device_condition_id' => $deviceConditionId,

                'qty' => $quantity,
                'unit_id' => $unitId,
                'unit_price' => $unitPrice,
                'total_price' => $quantity * $unitPrice,
                'author_comission' => ($productPrice * $productInfo->comission) / 100,
                'created_at' => Carbon::now()
            ]);

            $totalOrderAmount = $totalOrderAmount + ($quantity * $unitPrice);
            $index++;
        }


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

    public function orderCheckoutAppOnly(Request $request){

        date_default_timezone_set("Asia/Dhaka");

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
            'discount' => 0,
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

        $index = 0;
        $totalOrderAmount = 0;

        foreach($request->product_id as $productId){

            $productInfo = Product::where('id', $productId)->first();
            $productPrice = $productInfo->discount_price > 0 ? $productInfo->discount_price : $productInfo->price;

            if($productInfo && $productInfo->author_id){
                $userInfo = User::where('id', $productInfo->author_id)->first();
                if($userInfo){
                    $userInfo->balance = $userInfo->balance + (($productPrice * $productInfo->comission) / 100);
                    $userInfo->save();
                }
            }

            OrderDetails::insert([
                'order_id' => $orderId,
                'product_id' => $productId,

                // VARIANT
                'color_id' => isset($request->color_id[$index]) ? $request->color_id[$index] : null,
                'region_id' => isset($request->region_id[$index]) ? $request->region_id[$index] : null,
                'sim_id' => isset($request->sim_id[$index]) ? $request->sim_id[$index] : null,
                'size_id' => isset($request->size_id[$index]) ? $request->size_id[$index] : null,
                'storage_id' => isset($request->storage_id[$index]) ? $request->storage_id[$index] : null,
                'warrenty_id' => isset($request->warrenty_id[$index]) ? $request->warrenty_id[$index] : null,
                'device_condition_id' => isset($request->device_condition_id[$index]) ? $request->device_condition_id[$index] : null,

                'qty' => $request->qty[$index],
                'unit_id' => $request->unit_id[$index],
                'unit_price' => $request->unit_price[$index],
                'total_price' => $request->qty[$index] * $request->unit_price[$index],
                'author_comission' => ($productPrice * $productInfo->comission) / 100,
                'created_at' => Carbon::now()
            ]);

            $totalOrderAmount = $totalOrderAmount + ($request->qty[$index] * $request->unit_price[$index]);
            $index++;
        }


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


    public function guestOrderCheckout(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            date_default_timezone_set("Asia/Dhaka");

            $orderId = Order::insertGetId([
                'order_no' => time().rand(100,999),
                // 'user_id' => auth()->user()->id,
                'order_date' => date("Y-m-d H:i:s"),
                'estimated_dd' => date('Y-m-d', strtotime("+7 day", strtotime(date("Y-m-d")))),
                'payment_method' => NULL,
                'trx_id' => time().str::random(5),
                'order_status' => 0,
                'sub_total' => 0,
                'coupon_code' => NULL,
                'discount' => 0,
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

            $index = 0;
            $totalOrderAmount = 0;

            // for a reason=== dont change the code
            $productIdArray = explode(",", $request->product_id[0]);
            $qtyArray = explode(",", $request->qty[0]);
            $unitPriceArray = explode(",", $request->unit_price[0]);
            $unitIdArray = explode(",", $request->unit_id[0]);

            // variants added later
            $colorIdArray = explode(",", $request->color_id[0]);
            $regionIdArray = explode(",", $request->region_id[0]);
            $simIdArray = explode(",", $request->sim_id[0]);
            $storageIdArray = explode(",", $request->storage_id[0]);
            $warrentyIdArray = explode(",", $request->warrenty_id[0]);
            $deviceConditionIdArray = explode(",", $request->device_condition_id[0]);

            foreach($productIdArray as $productId){

                $quantity = (double) trim($qtyArray[$index]);
                $unitPrice = (double) trim($unitPriceArray[$index]);
                $pId = (int) trim($productId);
                $unitId = (double) trim($unitIdArray[$index]);

                // variants added later | chosing default variant while no variant is selected although product has variant
                $prodInfo = Product::where('id', $pId)->first();
                $colorId = (double) trim($colorIdArray[$index]);
                $regionId = (double) trim($regionIdArray[$index]);
                $simId = (double) trim($simIdArray[$index]);
                $storageId = (double) trim($storageIdArray[$index]);
                $warrentyId = (double) trim($warrentyIdArray[$index]);
                $deviceConditionId = (double) trim($deviceConditionIdArray[$index]);
                if($prodInfo->has_variant == 1){
                    $variants = ProductVariant::where('product_id', $prodInfo->id)->where('stock', '>', 0)->count();
                    if($variants > 0){
                        if(!$colorId && !$regionId && !$simId && !$storageId && !$warrentyId && !$deviceConditionId){
                            $defaultVariant = ProductVariant::where('product_id', $prodInfo->id)->where('stock', '>', 0)->orderBy('price', 'desc')->first();
                            $colorId = $defaultVariant->color_id;
                            $regionId = $defaultVariant->region_id;
                            $simId = $defaultVariant->sim_id;
                            $storageId = $defaultVariant->storage_type_id;
                            $warrentyId = $defaultVariant->warrenty_id;
                            $deviceConditionId = $defaultVariant->device_condition_id;
                            $unitPrice = $defaultVariant->discounted_price > 0 ? $defaultVariant->discounted_price : $defaultVariant->price;
                        }
                    }
                }

                // Product::where('id', $pId)->decrement("stock", $quantity);
                $productInfo = Product::where('id', $productId)->first();
                $productPrice = $productInfo->discount_price > 0 ? $productInfo->discount_price : $productInfo->price;

                if($productInfo && $productInfo->author_id){
                    $userInfo = User::where('id', $productInfo->author_id)->first();
                    if($userInfo){
                        $userInfo->balance = $userInfo->balance + (($productPrice * $productInfo->comission) / 100);
                        $userInfo->save();
                    }
                }

                OrderDetails::insert([
                    'order_id' => $orderId,
                    'product_id' => $pId,

                    // VARIANT
                    'color_id' => $colorId,
                    'region_id' => $regionId,
                    'sim_id' => $simId,
                    'storage_id' => $storageId,
                    'warrenty_id' => $warrentyId,
                    'device_condition_id' => $deviceConditionId,

                    'qty' => $quantity,
                    'unit_id' => $unitId,
                    'unit_price' => $unitPrice,
                    'total_price' => $quantity * $unitPrice,
                    'author_comission' => ($productPrice * $productInfo->comission) / 100,
                    'created_at' => Carbon::now()
                ]);

                $totalOrderAmount = $totalOrderAmount + ($quantity * $unitPrice);
                $index++;
            }


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


    public function shippingBillingInfo(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            ShippingInfo::where('order_id', $request->order_id)->delete();
            ShippingInfo::insert([
                'order_id' => $request->order_id,
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'gender' => null,
                'address' => null,
                'thana' => null,
                'post_code' => null,
                'city' => null,
                'country' => 'Bangladesh',
                'created_at' => Carbon::now()
            ]);

            BillingAddress::where('order_id', $request->order_id)->delete();
            BillingAddress::insert([
                'order_id' => $request->order_id,
                'address' => null,
                'post_code' => null,
                'thana' => null,
                'city' => null,
                'country' => 'Bangladesh',
                'created_at' => Carbon::now()
            ]);

            if(strtolower(trim($request->city)) == 'dhaka'){
                $orderInfo = Order::where('id', $request->order_id)->first();
                $orderInfo->delivery_fee = 60;
                $orderInfo->total = $orderInfo->total + 60;
                $orderInfo->complete_order = 1;
                $orderInfo->save();
            } else {
                $orderInfo = Order::where('id', $request->order_id)->first();
                $orderInfo->delivery_fee = 100;
                $orderInfo->total = $orderInfo->total + 100;
                $orderInfo->complete_order = 1;
                $orderInfo->save();
            }

            return response()->json([
                'success' => true,
                'message' => "Order Info Updated",
                'data' => new OrderResource(Order::where('id', $request->order_id)->first())
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function orderPreview(Request $request){
        return response()->json([
            'success' => true,
            'message' => "Order Preview",
            'data' => new OrderResource(Order::where('id', $request->order_id)->first())
        ], 200);
    }

    public function getMyOrders(Request $request){
        $data = DB::table('orders')->where('user_id', auth()->user()->id)->where('payment_status', 1)->orderBy('id', 'desc')->paginate(100);
        return response()->json([
            'success' => true,
            'date' => OrderResource::collection($data)->resource
        ], 200);
    }

    public function orderDetails($slug){
        $data = DB::table('orders')->where('user_id', auth()->user()->id)->where('slug', $slug)->first();
        return response()->json([
            'success' => true,
            'date' => new OrderResource($data)
        ], 200);
    }

    public function orderProgress(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $order = DB::table('orders')->where('order_no', $request->order_no)->first();
            $data = DB::table('order_progress')->where('order_id', $order->id)->orderBy('id', 'asc')->get();

            return response()->json([
                'success' => true,
                'date' => OrderProgressResource::collection($data)
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function orderCashOnDelivery(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $orderId = $request->order_id;
            $orderInfo = Order::where('id', $orderId)->first();
            $orderInfo->trx_id = $request->tran_id;
            $orderInfo->payment_method = 1;
            $orderInfo->payment_status = 1; //success
            $orderInfo->save();

            OrderPayment::insert([
                'order_id' => $orderInfo->id,
                'payment_through' => "COD",
                'tran_id' => $orderInfo->trx_id,
                'val_id' => NULL,
                'amount' => $orderInfo->total,
                'card_type' => NULL,
                'store_amount' => $orderInfo->total,
                'card_no' => NULL,
                'status' => "VALID",
                'tran_date' => date("Y-m-d H:i:s"),
                'currency' => "BDT",
                'card_issuer' => NULL,
                'card_brand' => NULL,
                'card_sub_brand' => NULL,
                'card_issuer_country' => NULL,
                'created_at' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Order Payment is Successfull",
                'data' => new OrderResource(Order::where('id', $orderInfo->id)->first())
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }



    public function orderPaymentBkash(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $orderId = $request->order_id;
            $orderInfo = Order::where('id', $orderId)->first();
            $orderInfo->bank_tran_id = "Not Available (COD)";
            $orderInfo->trx_id = $request->tran_id;
            $orderInfo->payment_method = $request->payment_method;
            $orderInfo->payment_status = 1; //success
            $orderInfo->complete_order = 1; //complete
            $orderInfo->save();

            OrderPayment::insert([
                'order_id' => $orderInfo->id,
                'payment_through' => $request->payment_through,
                'tran_id' => $orderInfo->trx_id,
                'val_id' => NULL,
                'amount' => $orderInfo->total,
                'card_type' => NULL,
                'store_amount' => $orderInfo->total,
                'card_no' => NULL,
                'status' => "VALID",
                'tran_date' => date("Y-m-d H:i:s"),
                'currency' => "BDT",
                'card_issuer' => NULL,
                'card_brand' => NULL,
                'card_sub_brand' => NULL,
                'card_issuer_country' => NULL,
                'created_at' => Carbon::now()
            ]);

            // author comission calculation start
            $orderedItems = DB::table('order_details')
                                ->join('orders', 'order_details.order_id', 'orders.id')
                                ->join('products', 'order_details.product_id', 'products.id')
                                ->select('products.*')
                                ->where('order_details.order_id', $orderInfo->id)
                                ->get();

            foreach($orderedItems as $orderedItem){

                $prodInfo = Product::where('id', $orderedItem->id)->first();
                $price = $prodInfo->discount_price > 0 ? $prodInfo->discount_price : $prodInfo->price;

                if($orderedItem->author_id){
                    $userInfo = User::where('id', $orderedItem->author_id)->first();
                    if($userInfo){
                        $userInfo->balance = $userInfo->balance + (($price * $prodInfo->comission) / 100);
                        $userInfo->save();
                    }
                }
            }
            // author comission calculation end

            return response()->json([
                'success' => true,
                'message' => "Order Payment is Successfull",
                'data' => new OrderResource(Order::where('id', $orderInfo->id)->first())
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function submitProductReview(Request $request){

        $product_id = $request->product_id;
        $userId = auth()->user()->id;
        $rating = $request->rating;
        $review = $request->review;

        $productInfo = DB::table('products')->where('id', $product_id)->first();
        $reviewValidity = DB::table('orders')
                            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                            ->where('orders.user_id', $userId)
                            ->where('order_details.product_id', $product_id)
                            ->first();

        if($productInfo->price <= 0 && $productInfo->discount_price <= 0){
            $id = ProductReview::insertGetId([
                'product_id' => $product_id,
                'user_id' => $userId,
                'rating' => $rating,
                'review' => $review,
                'slug' => time().str::random(5),
                'status' => 0,
                'created_at' => Carbon::now()
            ]);
            $data = ProductReview::where('id', $id)->first();

            return response()->json([
                'success' => true,
                'message' => "Prouduct Review Submitted",
                'data' => $data
            ], 200);
        }

        if($productInfo->is_premium == 1 && $reviewValidity){
            $id = ProductReview::insertGetId([
                'product_id' => $product_id,
                'user_id' => $userId,
                'rating' => $rating,
                'review' => $review,
                'slug' => time().str::random(5),
                'status' => 0,
                'created_at' => Carbon::now()
            ]);
            $data = ProductReview::where('id', $id)->first();

            return response()->json([
                'success' => true,
                'message' => "Prouduct Review Submitted",
                'data' => $data
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => "You are not eligible to submit Review for this Product",
                'data' => NULL
            ], 200);
        }
    }

    public function submitProductQuestion(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            ProductQuestionAnswer::insert([
                'product_id' => $request->product_id,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'question' => $request->question,
                'slug' => time().str::random(5),
                'created_at' => Carbon::now()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Question Submitted Successfully"
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getAllTestimonials(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = DB::table('testimonials')->orderBy('id', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }


    public function subscriptionForUpdates(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = SubscribedUsers::where('email', $request->email)->first();

            if($data){
                return response()->json([
                    'success' => true,
                    'message' => "Already Subscribed"
                ], 200);
            } else {
                SubscribedUsers::insert([
                    'email' => $request->email,
                    'created_at' => Carbon::now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Successfully Subscribed"
                ], 200);
            }

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function featuredBrandWiseProducts(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $featuredBrands = Brand::where('featured', 1)->orderBy('serial', 'asc')->paginate(20);

            return response()->json([
                'success' => true,
                'data' => BrandResource::collection($featuredBrands)->resource
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getPaymentGateways(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $gateways = PaymentGateway::where('status', 1)->get();
            return response()->json([
                'success' => true,
                'data' => $gateways
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function bestSellingProduct(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            // SELECT products.id, count(order_details.product_id) as prod_count FROM products
            // LEFT JOIN order_details ON products.id = order_details.product_id
            // GROUP BY products.id ORDER BY prod_count DESC;

            $book_type = $request->book_type;

            $data = DB::table('products')
                        ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
                        ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                        ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                        ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                        ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                        ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                        ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty', DB::raw("count(order_details.product_id) as order_count"))
                        ->where('products.status', 1)
                        ->when($book_type, function($query) use ($book_type){
                            return $query->where('products.is_audio', $book_type);
                        })
                        ->orderBy('order_count', 'desc')
                        ->groupBy('products.id') //group is must needed, otherwise only one product will show
                        ->paginate(20);

            return response()->json([
                'success' => true,
                // 'data' => ProductResource::collection($data)
                'data' => ProductResource::collection($data)->resource
            ]);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getAllAuthors(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = User::where('user_type', 2)->where('status', 1)->paginate(20);

            return response()->json([
                'success' => true,
                'data' => UserResource::collection($data)->resource
            ]);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getAuthorProfile(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = DB::table('users')->where('id', $request->author_id)->first();

            return response()->json([
                'success' => true,
                'data' => new UserResource($data)
            ]);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function searchAuthors(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = User::where('name', 'LIKE', '%'.$request->search_keyword.'%')->where('user_type',
            2)->where('status', 1)->paginate(20);

            return response()->json([
                'success' => true,
                'data' => UserResource::collection($data)->resource
            ]);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }


    // New Search API
    public function searchByBookName(Request $request){ //post method
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                ->where('products.status', 1)
                ->where('products.name', 'LIKE', '%'.$request->book_name.'%')
                ->orderBy('products.id', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($data)->resource
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function searchByPublisherName(Request $request){ //post method
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                ->where('products.status', 1)
                ->where('brands.name', 'LIKE', '%'.$request->publisher_name.'%')
                ->orderBy('products.id', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($data)->resource
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function searchByAuthorName(Request $request){ //post method
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = DB::table('products')
                    ->leftJoin('users', 'products.author_id', '=', 'users.id')
                    ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                    ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                    ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                    ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                    ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                    ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                    ->where('products.status', 1)
                    ->where('users.name', 'LIKE', '%'.$request->author_name.'%')
                    ->orderBy('products.id', 'desc')
                    ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($data)->resource
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }


    public function globalSearch(Request $request){ //post method
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $ebooks = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                ->where('products.status', 1)
                ->where('products.is_audio', 2)
                ->where('products.name', 'LIKE', '%'.$request->keyword.'%')
                ->orderBy('products.id', 'desc')
                ->skip(0)
                ->limit(20)
                ->get();

            $audio_books = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                ->where('products.status', 1)
                ->where('products.is_audio', 1)
                ->where('products.name', 'LIKE', '%'.$request->keyword.'%')
                ->orderBy('products.id', 'desc')
                ->skip(0)
                ->limit(20)
                ->get();

            $authors = User::where('name', 'LIKE', '%'.$request->keyword.'%')->where('user_type', 2)->where('status', 1)->skip(0)->limit(20)->get();

            return response()->json([
                'success' => true,
                'ebooks' => ProductResource::collection($ebooks),
                'audio_books' => ProductResource::collection($audio_books),
                'authors' => UserResource::collection($authors)
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getSubscriptionPackages(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = DB::table('subscription_packages')
                        ->where('status', 1)
                        ->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function getAllChapters(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $variants = DB::table('product_variants')
                    ->select('product_variants.*')
                    ->where('product_id', $request->product_id)
                    // ->groupBy('product_variants.title')
                    ->orderBy('id', 'asc')
                    ->get();

            $is_package_subscribed = 0;
            $is_premium = 0;
            $is_purchased = 0;

            $bookInfo = Product::where('id', $request->product_id)->first();
            if($bookInfo->is_premium){
                $is_premium = 1;
            }

            if($request->user_id > 0){

                $purchasInfo = DB::table('order_details')
                        ->join('orders', 'order_details.order_id', 'orders.id')
                        ->where('order_details.product_id', $request->product_id)
                        ->where('orders.user_id', $request->user_id)
                        ->where('orders.payment_status', 1)
                        ->select('order_details.id')
                        ->first();

                if($purchasInfo){
                    $is_purchased = 1;
                }

                if($bookInfo->is_premium){
                    // $subscribedStatus = DB::table('subscription_purchases')->where('user_id', $request->user_id)->where('status', 1)->where('expire_date', '>=', date("Y-m-d"))->first();
                    // if($subscribedStatus){
                    //     $is_package_subscribed = 1;
                    // }
                    
                    $subscribedStatus = DB::table('subscription_purchases')
                                            ->leftJoin('subscription_packages', 'subscription_purchases.subscription_id', 'subscription_packages.id')
                                            ->where('subscription_purchases.user_id', $request->user_id)
                                            ->where('subscription_purchases.status', 1)
                                            ->where('subscription_purchases.expire_date', '>=', date("Y-m-d"))
                                            ->select('subscription_purchases.*', 'subscription_packages.audio_ebook')
                                            ->first();

                    if($subscribedStatus && $subscribedStatus->audio_ebook == 3){
                        $is_package_subscribed = 1;
                    } elseif($subscribedStatus && $subscribedStatus->audio_ebook == 2 && $bookInfo->is_audio == 2){
                        $is_package_subscribed = 1;
                    } elseif($subscribedStatus && $subscribedStatus->audio_ebook == 1 && $bookInfo->is_audio == 1){
                        $is_package_subscribed = 1;
                    } else {
                        $is_package_subscribed = 0;
                    }
                    
                }
            }


            $data = array();
            foreach($variants as $index => $variant){
                $data[$index]['id'] = $variant->id;
                $data[$index]['product_id'] = $variant->product_id;
                $data[$index]['audio'] = 'productAudio/' . $variant->image;
                $data[$index]['chapter'] = $variant->title;
                $data[$index]['is_free'] = $variant->is_free;
                $data[$index]['description'] = $variant->description;
                $data[$index]['is_package_subscribed'] = $is_package_subscribed;
                $data[$index]['is_premium'] = $is_premium;
                $data[$index]['is_purchased'] = $is_purchased;
            }

            return response()->json([
                'success' => true,
                // 'data' => ProductVariantResource::collection($variants)
                'data' => $data
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }
    public function getAllOffer(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = DB::table('promo_codes')
                    ->select('promo_codes.*')
                    ->where('status', 1)
                    ->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function purchaseSubscriptionPackage(Request $request){

        $subscriptionPackageInfo = DB::table('subscription_packages')->where('id', $request->subscription_id)->first();
        $expireDate = date("Y-m-d", strtotime(date("Y-m-d"). ' + '.$subscriptionPackageInfo->day.' days'));

        SubscriptionPurchase::insert([
            'subscription_id' => $request->subscription_id,
            'user_id' => auth()->user()->id,
            'payment_through' => $request->payment_through,
            'tran_id' => $request->tran_id,
            'amount' => $request->amount,
            'status' => $request->status,
            'tran_date' => date("Y-m-d"),
            'currency' => $request->currency,
            'expire_date' => $expireDate,
            'created_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Purchased Successfully'
        ], 200);

    }

    public function subscriptionPurchaseHistory(){

        $data = SubscriptionPurchase::where('user_id', auth()->user()->id)->get();

        $data = DB::table('subscription_purchases')
                    ->join('subscription_packages', 'subscription_purchases.subscription_id', 'subscription_packages.id')
                    ->select('subscription_purchases.*', 'subscription_packages.title', 'subscription_packages.audio_ebook', 'subscription_packages.description')
                    ->where('subscription_purchases.user_id', auth()->user()->id)
                    ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }


    public function getPurchasedItems(Request $request){

        $userId = auth()->user()->id;
        $book_type = $request->book_type;

        $data = DB::table('order_details')
                ->leftJoin('orders', 'order_details.order_id', '=', 'orders.id')
                ->leftJoin('products', 'order_details.product_id', '=', 'products.id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('product_models', 'products.model_id', '=', 'product_models.id')
                ->leftJoin('product_warrenties', 'products.warrenty_id', '=', 'product_warrenties.id')
                ->select('products.*', 'categories.name as category_name', 'units.name as unit_name', 'brands.name as brand_name', 'product_models.name as model_name', 'product_warrenties.name as product_warrenty')
                ->where('orders.user_id', $userId)
                ->where('orders.payment_status', 1)
                ->when($book_type, function($query) use ($book_type){
                    return $query->where('products.is_audio', $book_type);
                })
                ->orderBy('orders.id', 'desc')
                ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($data)->resource
        ], 200);


    }

    public function isProductWishlisted(Request $request){

        $userId = auth()->user()->id;
        $product_id = $request->product_id;

        $data = DB::table('wish_lists')->where('user_id', $userId)->where('product_id', $product_id)->first();

        if($data){
            return response()->json([
                'success' => true,
                'message' => 'Already in Wishlist',
                'data' => $data->id
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Not in Wishlist'
            ], 200);
        }

    }

    public function getProductReviews(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = DB::table('product_reviews')
                        ->join('users', 'product_reviews.user_id', 'users.id')
                        ->select('product_reviews.*', 'users.name as username', 'users.image as user_image')
                        ->where('product_id', $request->product_id)
                        ->where('product_reviews.status', 1)
                        ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

    public function chapterViewCount(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            DB::table('product_variants')->where('id', $request->chapter_id)->increment('view');

            return response()->json([
                'success' => true,
                'message' => 'View Count has Incresed'
            ], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }
    
    public function adMobConfig(Request $request){
        if ($request->header('Authorization') == ApiController::AUTHORIZATION_TOKEN) {

            $data = Adsmob::where('id', 1)->first();
            $data = array(
                'adsmob_app_id' => $data->fb_ad_id ? $data->fb_ad_id : null,
                'adsmob_banner_ad_id' => $data->banner_ad_id ? $data->banner_ad_id : null,
                'adsmob_native_ad_id' => $data->native_ad_id ? $data->native_ad_id : null,
                'adsmob_interstitial_ad_id' => $data->interstitial_ad_id ? $data->interstitial_ad_id : null,

                'fb_banner_ad_id' => $data->fb_banner_ad_id ? $data->fb_banner_ad_id : null,
                'fb_native_ad_id' => $data->fb_native_ad_id ? $data->fb_native_ad_id : null,
                'fb_interstitial_ad_id' => $data->fb_interstitial_ad_id ? $data->fb_interstitial_ad_id : null,

                'show_status' => strval($data->show_status),
                'banner_show_status' => strval($data->banner_show_status),
                'native_show_status' => strval($data->native_show_status),
                'interstitial_show_status' => strval($data->interstitial_show_status),
            );
            
            return response()->json([
                'success' =>true,
                'data' => $data
            ]);

        } else {
            return response()->json([
                'success' => false,
                'message' => "Authorization Token is Invalid"
            ], 422);
        }
    }

}
