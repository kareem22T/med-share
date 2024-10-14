<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HandleResponseTrait;
use App\SaveImageTrait;
use App\DeleteImageTrait;
use App\Models\Product;
use App\Models\Gallery;
use App\Models\Wishlist;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

class ProductsController extends Controller
{
    use HandleResponseTrait, SaveImageTrait, DeleteImageTrait;
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

    //     //   $products->each(function ($product) use ($user) {
    //     foreach($products as $product){
    //             $earthRadius = 6371; // Earth radius in kilometers
    //             $deltaLat = deg2rad($product->postedBy->lat - $user->lat);
    //             $deltaLng = deg2rad($product->postedBy->lng - $user->lng);
    //             $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
    //             cos( $user->lat) * cos($product->postedBy->lat) *
    //             sin($deltaLng / 2) * sin($deltaLng / 2);

    //             $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    //             $distance = $earthRadius * $c;
    //             $product->distance = $distance;
    //     }
    //         // });
    //     }

    //     return $products;
    // }


    // private function calculateDistanceSingle($product, $authorization) {
    //     $user = null;

    //     $authorizationHeader = $authorization ? $authorization : false;
    
    //     if ($authorizationHeader) {
    //         try {
    //             // Extract token from the header (assuming 'Bearer' prefix)
    //             $hashedToken = str_replace('Bearer ', '', $authorizationHeader);
    //             $token = PersonalAccessToken::findToken($hashedToken);
    //             $user = $token ? $token->tokenable : null; // Set user to null if token not found
    
    //         } catch (\Exception $e) {
    //             // Handle potential exceptions during token validation
    //             // Log the error or return an appropriate response
    //         }
    //     }
    
    //     // Check if user is retrieved successfully before using it
    //     if ($user) {
    //         // Assuming you have the Haversine formula implementation or can use a library
    
    //         $earthRadius = 6371; // Earth radius in kilometers
    //         $deltaLat = deg2rad($product->postedBy->lat - $user->lat);
    //         $deltaLng = deg2rad($product->postedBy->lng - $user->lng);
    //         $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
    //             cos($user->lat) * cos($product->postedBy->lat) *
    //             sin($deltaLng / 2) * sin($deltaLng / 2);
    
    //         $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    //         $distance = $earthRadius * $c;
    //         $product->distance = $distance;
    //     }
    
    //     return $product;
    // }
    
    ///////////////////////////

     private const EARTH_RADIUS_KM = 6371;

    public function calculateDistance($products, $authorization)
    {
        $user = $this->authenticateUser($authorization);

        if ($user) {
            $products->each(function ($product) use ($user) {
                $distance = $this->haversineDistance(
                    $user->lat,
                    $user->lng,
                    $product->postedBy->lat,
                    $product->postedBy->lng,
                    6378.137
                );
                $newDistance = ($distance * 0.88) * 1.85;
                $product->distance = $newDistance;
            });
        }
        return $products;
    }

    public function calculateDistanceSingle($product, $authorization)
    {
        $user = $this->authenticateUser($authorization);
        $postedBy = User::find($product->user_id);
        if ($user) {
            $distance = $this->haversineDistance(
                $user->lat,
                $user->lng,
                $postedBy->lat,
                $postedBy->lng,
                6378.137
            );
            $newDistance = ($distance * 0.88) * 1.85;
            $product->distance = $newDistance;
        }

        return $product;
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

        $authorizationHeader = $authorization ? $authorization : false;

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

    public function create(Request $request) {
        $user = $request->user();
        if (!$user)
            return $this->handleResponse(
                false,
                "",
                ["هذا المستخدم غير موجود"],
                [],
                []
            );

        $validator = Validator::make($request->all(), [
            "name" => ["required"],
            "quantity" => ["required", "numeric"],
            "price" => ["required", "numeric"],
            "discount" => ["numeric"],
            'expired_at' => ['required'],
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            "name.required" => "ادخل اسم المنتج",
            "expired_at.required" => "ادخل تاريخ الانتهاء",
            "quantity.required" => "ادخل الكمية المتاحة من المنتج",
            "price.required" => "ادخل سعر المنتج المنتج",
            "images.required" => "يجب ان ترفع ما لايقل عن 4 صور لكل منتج ",
            "images.min_images" => "يجب ان ترفع ما لايقل عن 4 صور لكل منتج ",
            "images.mimes" => "يجب ان تكون الصورة بين هذه الصيغ (jpeg, png, jpg, gif)",
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

        if (collect($request->file('images'))->count() < 4) {
            return $this->handleResponse(
                false,
                "",
                ["يجب ان ترفع ما لايقل عن 4 صور لكل منتج "],
                [],
                []
            );
        }

        $product = Product::create([
            "name" => $request->name,
            "quantity" => $request->quantity,
            "price" => $request->price,
            "user_id" => $user->id,
            'discount' => $request->discount ?? 0,
            'expired_at' => Carbon::parse($request->expired_at),
        ]);

        foreach ($request->file('images') as $img) {
            $image = $this->saveImg($img, 'images/uploads/Products');
            $gallery = Gallery::create([
                "path" => '/images/uploads/Products/' . $image,
                "product_id" => $product->id
            ]);
        }


        if ($product)
            return $this->handleResponse(
                true,
                "تم اضافة المنتج بنجاح",
                [],
                [],
                []
            );

    }

    public function update(Request $request) {
        $user = $request->user();
        if (!$user)
            return $this->handleResponse(
                false,
                "",
                ["هذا المستخدم غير موجود"],
                [],
                []
            );

        $validator = Validator::make($request->all(), [
            "product_id" => ["required"],
            "name" => ["required"],
            "quantity" => ["required", "numeric"],
            "price" => ["required", "numeric"],
            "discount" => ["numeric"],
            "expired_at" => ["required", "date"],
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            "name.required" => "ادخل اسم المنتج",
            "expired_at.required" => "ادخل تاريخ الانتهاء",
            "expired_at.date" => "يجب ان يكون تاريخ الانتهاء بصيغى تاريخ صحيحة",
            "quantity.required" => "ادخل الكمية المتاحة من المنتج",
            "price.required" => "ادخل سعر المنتج المنتج",
            "images.required" => "يجب ان ترفع ما لايقل عن 4 صور لكل منتج ",
            "images.min_images" => "يجب ان ترفع ما لايقل عن 4 صور لكل منتج ",
            "images.mimes" => "يجب ان تكون الصورة بين هذه الصيغ (jpeg, png, jpg, gif)",
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

        $product = Product::with("gallery")->find($request->product_id);
        if (!$product)
            return $this->handleResponse(
                false,
                "",
                ["هذا المنتج غير متاح"],
                [],
                []
            );

        if ($product->user_id !== $user->id)
            return $this->handleResponse(
                false,
                "",
                ["ليس لديك حقوق التعديل على هذا المنتج"],
                [],
                []
            );


        if ((collect($request->file('images'))->count() + ($product->gallery->count() - collect($request->deleted_gallery ? $request->deleted_gallery : [])->count()) < 4)) {
            return $this->handleResponse(
                false,
                "",
                ["يجب ان ترفع ما لايقل عن 4 صور لكل منتج "],
                [],
                []
            );
        }

        $product->name = $request->name;
        $product->quantity = $request->quantity;
        $product->price = $request->price;
        $product->discount = $request->discount ?? 0;
        $product->expired_at = Carbon::parse($request->expired_at);

        if ($request->deleted_gallery) {
            foreach ($request->deleted_gallery as $img) {
                $this->deleteFile(base_path($img['path']));
                $imageD = Gallery::find($img['id']);
                $imageD->delete();
            }
        }

        if ($request->images && $product) {
            foreach ($request->images as $img) {
                $image = $this->saveImg($img, 'images/uploads/Products');
                $gallery = Gallery::create([
                    "path" => '/images/uploads/Products/' . $image,
                    "product_id" => $product->id
                ]);
            }
        }

        $product->save();

        if ($product)
            return $this->handleResponse(
                true,
                "تم تحديث المنتج بنجاح",
                [],
                [],
                []
            );

    }


    public function delete(Request $request) {
        $user = $request->user();
        if (!$user)
            return $this->handleResponse(
                false,
                "",
                ["هذا المستخدم غير موجود"],
                [],
                []
            );

        $validator = Validator::make($request->all(), [
            "product_id" => ["required"],
        ], [
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

        $product = Product::with("gallery")->find($request->product_id);
        if (!$product)
            return $this->handleResponse(
                false,
                "",
                ["هذا المنتج غير متاح"],
                [],
                []
            );


        if ($product->user_id !== $user->id)
            return $this->handleResponse(
                false,
                "",
                ["ليس لديك حقوق حذف هذا المنتج"],
                [],
                []
            );

        if ($product->gallery) {
            foreach ($product->gallery as $img) {
                $this->deleteFile(base_path($img['path']));
                $imageD = Gallery::find($img['id']);
                $imageD->delete();
            }
        }


        $product->delete();

        if ($product)
            return $this->handleResponse(
                true,
                "تم حذف المنتج بنجاح",
                [],
                [],
                []
            );

    }

    public function get(Request $request) {
        $per_page = $request->per_page ? $request->per_page : 10;

        $sortKey = ($request->sort && $request->sort == "HP") || ($request->sort && $request->sort == "LP") ? "price" : "created_at";
        $sortWay = $request->sort && $request->sort == "HP" ? "desc" : ($request->sort && $request->sort == "LP" ? "asc" : "desc");

        $products = Product::with("gallery")
            ->where("isApproved", true)
            ->orderBy($sortKey, $sortWay)
            ->paginate($per_page);

        $products = $this->addIsFavKey($products, $request->header('Authorization'));
        $products = $this->calculateDistance($products, $request->header('Authorization'));

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [
                $products
            ],
            [
                "parameters" => [
                    "per_page" => "لتحديد عدد العناصر لكل صفحة",
                    "page" => "لتحديد صفحة",
                    "sort" => [
                        "HP" => "height price",
                        "LP" => "lowest price",
                    ]
                ],
                "sort" => [
                    "default" => "لو مبعتش حاجة هيفلتر ع اساس الاحدث",
                    "sort = HP" => "لو بعت ال 'sort' ب 'HP' هيفلتر من الاغلى للارخص",
                    "sort = LP" => "لو بعت ال 'sort' ب 'LP' هيفلتر من الارخص للاغلى",
                ]
            ]
        );
    }

    public function getAll(Request $request) {
        $sortKey =($request->sort && $request->sort == "HP") || ( $request->sort && $request->sort == "LP") ? "price" :"created_at";
        $sortWay = $request->sort && $request->sort == "HP" ? "desc" : ( $request->sort && $request->sort  == "LP" ? "asc" : "desc");

        $products = Product::with("gallery")->where("isApproved", true)->orderBy($sortKey, $sortWay)->get();
        $products = $this->addIsFavKey($products, $request->header('Authorization'));
        $products = $this->calculateDistance($products, $request->header('Authorization'));

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [
                $products
            ],
            [
                "parameters" => [
                    "sort" => [
                        "HP" => "height price",
                        "LP" => "lowest price",
                    ]
                ],
                "sort" => [
                    "default" => "لو مبعتش حاجة هيفلتر ع اساس الاحدث",
                    "sort = HP" => "لو بعت ال 'sort' ب 'HP' هيفلتر من الاغلى للارخص",
                    "sort = LP" => "لو بعت ال 'sort' ب 'LP' هيفلتر من الارخص للاغلى",
                ]
            ]
        );
    }

    public function search(Request $request) {
        $per_page = $request->per_page ? $request->per_page : 10;

        $sortKey =($request->sort && $request->sort == "HP") || ( $request->sort && $request->sort == "LP") ? "price" :"created_at";
        $sortWay = $request->sort && $request->sort == "HP" ? "desc" : ( $request->sort && $request->sort  == "LP" ? "asc" : "desc");
        $search = $request->search ? $request->search : '';

        $products = Product::with("gallery")->where('name', 'like', '%' . $search . '%')->where("isApproved", true)->orderBy($sortKey, $sortWay)->paginate($per_page);
        $products = $this->addIsFavKey($products, $request->header('Authorization'));
        $products = $this->calculateDistance($products, $request->header('Authorization'));

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [
                $products
            ],
            [
                "search" => "البحث بالاسم او اي كلمة في المحتوى",
                "parameters" => [
                    "per_page" => "لتحديد عدد العناصر لكل صفحة",
                    "page" => "لتحديد صفحة",
                    "sort" => [
                        "HP" => "height price",
                        "LP" => "lowest price",
                    ]
                ],
                "sort" => [
                    "default" => "لو مبعتش حاجة هيفلتر ع اساس الاحدث",
                    "sort = HP" => "لو بعت ال 'sort' ب 'HP' هيفلتر من الاغلى للارخص",
                    "sort = LP" => "لو بعت ال 'sort' ب 'LP' هيفلتر من الارخص للاغلى",
                ]
            ]
        );
    }

    public function getProductsPerUserAll(Request $request) {
        $validator = Validator::make($request->all(), [
            "user_id" => ["required"],
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

        $user = User::with("products")->find($request->user_id);

        if ($user) {
            $sortKey = ($request->sort && $request->sort == "HP") || ( $request->sort && $request->sort == "LP") ? "price" :"created_at";
            $sortWay = $request->sort && $request->sort == "HP" ? "desc" : ( $request->sort && $request->sort  == "LP" ? "asc" : "desc");

            $products = $user->products()->orderBy($sortKey, $sortWay)->get();

            return $this->handleResponse(
                true,
                "عملية ناجحة",
                [],
                [
                    $products
                ],
                [
                    "parameters" => [
                        "sort" => [
                            "HP" => "height price",
                            "LP" => "lowest price",
                        ]
                    ],
                    "sort" => [
                        "default" => "لو مبعتش حاجة هيفلتر ع اساس الاحدث",
                        "sort = HP" => "لو بعت ال 'sort' ب 'HP' هيفلتر من الاغلى للارخص",
                        "sort = LP" => "لو بعت ال 'sort' ب 'LP' هيفلتر من الارخص للاغلى",
                    ]
                ]
            );
        }

        return $this->handleResponse(
            false,
            "",
            ["المستخدم غير موجود"],
            [],
            []
        );
    }

    public function getProductsPerUserPagination(Request $request) {
        $validator = Validator::make($request->all(), [
            "user_id" => ["required"],
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

        $user = User::with("products")->find($request->user_id);

        if ($user) {
            $per_page = $request->per_page ? $request->per_page : 10;

            $sortKey =($request->sort && $request->sort == "HP") || ( $request->sort && $request->sort == "LP") ? "price" :"created_at";
            $sortWay = $request->sort && $request->sort == "HP" ? "desc" : ( $request->sort && $request->sort  == "LP" ? "asc" : "desc");

            $products = $user->products()->orderBy($sortKey, $sortWay)->paginate($per_page);

            return $this->handleResponse(
                true,
                "عملية ناجحة",
                [],
                [
                    $products
                ],
                [
                    "parameters" => [
                        "sort" => [
                            "HP" => "height price",
                            "LP" => "lowest price",
                        ]
                    ],
                    "sort" => [
                        "default" => "لو مبعتش حاجة هيفلتر ع اساس الاحدث",
                        "sort = HP" => "لو بعت ال 'sort' ب 'HP' هيفلتر من الاغلى للارخص",
                        "sort = LP" => "لو بعت ال 'sort' ب 'LP' هيفلتر من الارخص للاغلى",
                    ]
                ]
            );
        }

        return $this->handleResponse(
            false,
            "",
            ["المستخدم غير موجود"],
            [],
            []
        );
    }

    public function getProduct(Request $request) {
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

        $product = Product::with(["gallery", "postedBy" => function ($q) {
            $q->select("id", "name", "pharmacy_name", "email", "phone", "signature");
        }])->find($request->product_id);


        if ($product) {
            $product = $this->calculateDistanceSingle($product, $request->header('Authorization'));

            return $this->handleResponse(
                true,
                "عملية ناجحة",
                [],
                [
                    $product
                ],
                []
            );
        } else {
            return $this->handleResponse(
                false,
                "",
                ["المنتج غير موجود"],
                [],
                []
            );
        }
    }

}
