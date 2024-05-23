<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HandleResponseTrait;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    use HandleResponseTrait;

    public function addOrDeleteProductWishlist(Request $request) {
        $validator = Validator::make($request->all(), [
            "product_id" => ["required"],
        ]);

        if ($validator->fails()) {
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                []
            );
        }

        $product = Product::find($request->product_id);

        if ($product) {
            $user = $request->user();
            $product_if_in_user_wishlist = $user->wishlist()->where("product_id", $request->product_id)->first();
            if ($product_if_in_user_wishlist) {
                $product_if_in_user_wishlist->delete();
                return $this->handleResponse(
                    true,
                    "تم خذف المنتج من المفضلة بنجاح",
                    [],
                    [],
                    []
                );
            } else {
                $wislisht_item = Wishlist::create([
                    "user_id" => $user->id,
                    "product_id" => $product->id,
                ]);
                if ($wislisht_item)
                    return $this->handleResponse(
                        true,
                        "تم اضافة المنتج للمفضلة بنجاح",
                        [],
                        [],
                        []
                    );
            }
        } else {
            return $this->handleResponse(
                false,
                "",
                ["هذا المنتج غير متاح"],
                [],
                []
            );
        }

    }

    public function getWishlist(Request $request) {
        $user = $request->user();
        $wishlist = $user->wishlist()->with(["product" => function($q) {
            $q->with(['gallery' => function ($q) {
                $q->take(1);
            }]);
        }])->get();

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [$wishlist],
            []
        );
    }
}
