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
    // private function calculateDistance($products, $authorization) {
    //     $user = null;

    //     $authorizationHeader = $authorization ? $authorization : false;

    //     if ($authorizationHeader) {
    //       try {
    //         // Extract token from the header (assuming 'Bearer' prefix)
    //         $hashedToken = str_replace('Bearer ', '', $authorizationHeader);
    //         $token = PersonalAccessToken::findToken($hashedToken);
    //         $user = $token ? $token->tokenable : null; // Set user to null if token not found

    //       } catch (Exception $e) {
    //         // Handle potential exceptions during token validation
    //         // Log the error or return an appropriate response
    //       }
    //     }

    //     // Check if user is retrieved successfully before using it
    //     if ($user) {
    //       // Assuming you have the Haversine formula implementation or can use a library

    //       $products->each(function ($product) use ($user) {
    //             $earthRadius = 6371; // Earth radius in kilometers
    //             $deltaLat = deg2rad($product->postedBy->lat - $user->lat);
    //             $deltaLng = deg2rad($product->postedBy->lng - $user->lng);
    //             $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
    //             cos( $user->lat) * cos($product->postedBy->lat) *
    //             sin($deltaLng / 2) * sin($deltaLng / 2);

    //             $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    //             $distance = $earthRadius * $c;
    //             $product->distance = $distance;
    //         });
    //     }

    //     return $products;
    // }
    private const EARTH_RADIUS_KM = 6371;

    public function calculateDistance($products, $authorization)
    {
        $user = $this->authenticateUser($authorization);

        if ($user) {
            $products->each(function ($product) use ($user) {
                $postedBy = User::find($product->user_id);

                $distance = $this->haversineDistance(
                    $user->lat,
                    $user->lng,
                $postedBy->lat,
                $postedBy->lng,
                6378.137
                );
            $newDistance = ($distance * 0.88) * 1.5;
            $product->distance = $newDistance;
            });
        }
        return $products;
    }


    private function authenticateUser($authorization)
    {
        if (!$authorization) {
            return null;
        }

        try {
            $hashedToken = str_replace('Bearer ', '', $authorization);
            $token = PersonalAccessToken::findToken($hashedToken);
            return $token ? $token->tokenable : null;
        } catch (\Exception $e) {
            \Log::error('Token authentication failed: ' . $e->getMessage());
            return null;
        }
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2, $earthRadius = 6371)
    {
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);
    
        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;
    
        $a = sin($dlat/2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon/2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
        return $earthRadius * $c;
    }

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

        $products = $this->addIsFavKey($products, $token);
        $products = $this->calculateDistance($products, $token);
        return $products;
    }

    public function getNeartstPharmacies(Request $request) {
        $user = $request->user();

        // Call the method directly
        $nearst = $user->nearstPharmacies();
        return $this->handleResponse(
            true,
            "Success",
            [],
            $nearst,
            []
        );
    }
    public function getMostSelled($token) {

        $completedOrders = Order::with("products")->get();
        $products = Product::
        with("gallery")->
        withCount('orders')
        ->orderBy('orders_count', 'desc')
        ->limit(10)
        ->get();

        $products = $this->addIsFavKey($products, $token);
        $products = $this->calculateDistance($products, $token);
        return $products;
    }
    public function getDiscountedProducts($token) {

        $products = Product::
        with("gallery")->
        orderBy("discount", "desc")
        ->limit(10)
        ->get();


        $products = $this->addIsFavKey($products, $token);
        $products = $this->calculateDistance($products, $token);
        return $products;
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
