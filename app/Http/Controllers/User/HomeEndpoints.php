<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HandleResponseTrait;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\Category;
use App\Models\Banner;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
class HomeEndpoints extends Controller
{
    use HandleResponseTrait;

    public function addIsFavKey($products, $authorization) {
        $user = null;

        $authorizationHeader = $authorization;

        if ($authorizationHeader) {
            try {
                // Extract token from the header (assuming 'Bearer' prefix)
                $hashedTooken = str_replace('Bearer ', '', $authorizationHeader);
                $token = PersonalAccessToken::findToken($hashedTooken);
                $user = $token?->tokenable;

            } catch (Exception $e) {
                // Handle potential exceptions during token validation
                // Log the error or return an appropriate response
            }
        }

        if ($user) {
            $user_id = $user->id;

            // Add isFav key to each product
            $products->each(function ($product) use ($user_id) {
                $product->isFav = Wishlist::where('user_id', $user_id)->where('product_id', $product->id)->exists();
            });
        } else {
            // Add isFav key to each product as false if not logged in
            $products->each(function ($product) {
                $product->isFav = false;
            });
        }
        return $products;
    }

    public function getLatestBanners() {
        return $banners = Banner::all();
    }

    public function getLatestProducts($token) {
        $products = Product::latest()->with("gallery")->limit(15)->get();

        return $products = $this->addIsFavKey($products, $token);
    }

    public function getMostSelled($token) {

        $completedOrders = Order::with("products")->get();
        $topProducts = Product::
        withCount('orders')
        ->orderBy('orders_count', 'desc')
        ->limit(10)
        ->get();

        $topProducts = $this->addIsFavKey( $topProducts, $token);

        return $topProducts;
    }
    public function getDiscountedProducts($token) {

        $discountedProducts = Product::
        orderBy("discount", "desc")
        ->limit(10)
        ->get();

        $topProducts = $this->addIsFavKey( $discountedProducts, $token);

        return $discountedProducts;
    }

    public function getHomeApi(Request $request) {
        return $this->handleResponse(
            true,
            "Success",
            [],
            [
                "banners" => $this->getLatestBanners(),
                "most_selled" => $this->getMostSelled($request->header('Authorization')),
                "with_offer" => $this->getDiscountedProducts($request->header('Authorization')),
                "new_arrival" => $this->getLatestProducts($request->header('Authorization')),
            ],
            []
        );
    }
}
