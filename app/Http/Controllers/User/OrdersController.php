<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\PushNotificationTrait;
use App\HandleResponseTrait;
use App\SendEmailTrait;
use App\Models\Order;
use App\Models\Money_request;
use App\Models\Product;
use App\Models\User;
use App\Models\Request as Order_request;
use App\Models\Ordered_Product;
use App\Models\Request_product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    use HandleResponseTrait, SendEmailTrait, PushNotificationTrait;

    public function placeOrder(Request $request)
    {
        DB::beginTransaction();

        try {

            $user = $request->user();
            $cart = $user->cart()->whereHas('product')->get();

            // check if cart empty
            if (!$cart || $cart->count() === 0)
                return $this->handleResponse(
                    false,
                    "",
                    ["العربة فارغة قم بتعبئتها اولا"],
                    [],
                    []
                );

            $sub_total = 0;
            // get cart sub total
            if ($cart->count() > 0)
                foreach ($cart as $item) {
                    $item_product = $item->product()->with(["gallery" => function ($q) {
                        $q->take(1);
                    }])->first();
                    if ($item_product) :
                        $item->total = ((int) $item_product->price * (int) $item->quantity);
                        $sub_total += $item->total;
                    endif;
                    $item->dose_product_missing = $item_product ? false : true;
                    $item->product = $item_product ?? "This product is missing may deleted!";
                }

            $order = Order::create([
                "buyer_id" => $user->id,
                "payment_methode" => 1,
                "status" => 1,
                "is_paid" => 1,
                "shipping_fees" => 0,
                "payment_fees" => 0,
                "sub_total" => $sub_total,
                "total" => $sub_total,
            ]);

            $groupedProducts = [];
            // get each pharmacy products
            foreach ($cart as $cart_item) {
                $userId = $cart_item['product']['user_id'];
                $product = $cart_item;

                if (!isset($groupedProducts[$userId])) {
                    $groupedProducts[$userId] = [];
                }

                $groupedProducts[$userId][] = $product;
            }

            // get each make request for each pharmacy
            foreach ($groupedProducts as $userId => $products) {
                $seller = User::find($userId);

                $request_order = Order_request::create([
                    "user_id" => $userId,
                    "byuer_id" => $user->id
                ]);

                // email user with request
                $msg_content = "<h1>";
                $msg_content = " طلب جديد بواسطة" . $user->name;
                $msg_content .= "</h1>";
                $msg_content .= "<br>";
                $msg_content .= "<h3>";
                $msg_content .= "تفاصيل الطلب: ";
                $msg_content .= "</h3>";

                $msg_content .= "<h4>";
                $msg_content .= "رقم هاتف الطالب: ";
                $msg_content .= $user->phone;
                $msg_content .= "</h4>";

                $msg_content .= "<h4>";
                $msg_content .= "المنتجات المطلوبة : ";
                $msg_content .= "</h4>";

                foreach ($products as $item) {
                    if (!$item->dose_product_missing) {
                        $record_req_product = Request_product::create([
                            "request_id" => $request_order->id,
                            "product_id" => $item["product_id"],
                            "price_in_order" => $item["product"]["price"],
                            "ordered_quantity" => $item["quantity"],
                        ]);
                        $msg_content .= "<h4>";
                        $msg_content .= "اسم المنتج : ";
                        $msg_content .= $item['product']["name"];
                        $msg_content .= "<br>";
                        $msg_content .= "الكمية المطلوبة من المنتج : ";
                        $msg_content .= $item["quantity"];
                        $msg_content .= "</h4>";
                    }
                }

                // if ($user->fcm_token()->first())
                //     $this->pushNotification("لديك طلب جديد", "لديك طلب جديد بواسطة " . $user->pharmacy_name, $user->fcm_token()->first()->token);

                if ($seller)
                    $send_email = $this->sendEmail($seller->email , "طلب جديد", $msg_content);

            }

            // link products to order and remove cart
            foreach ($cart as $item) {
                if (!$item->dose_product_missing) {
                    $record_product = Ordered_Product::create([
                        "order_id" => $order->id,
                        "product_id" => $item["product_id"],
                        "price_in_order" => $item["product"]["price"],
                        "ordered_quantity" => $item["quantity"],
                    ]);
                }
                $item->delete();
            }

            DB::commit();

            return $this->handleResponse(
                true,
                "تم ارسال طلبك بنجاح",
                [],
                [],
                []
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->handleResponse(
                false,
                "فشل اكمال الطلب",
                [$e->getMessage()],
                [],
                []
            );
        }
    }

    public function ordersAll(Request $request) {
        $user = $request->user();
        $status = $request->status;
        $order = $user->orders()->latest()->with(["products" => function ($q) {
            $q->whereHas('product')->with(["product" => function ($q) {
                $q->with(["postedBy" =>  function ($q) {
                    $q->select("id", "name", "email", "phone", "signature", "picture");
                }]);
            }]);
        }])->get();

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [$order],
            [
            ]
        );
    }

    public function ordersPagination(Request $request) {
        $per_page = $request->per_page ? $request->per_page : 10;

        $user = $request->user();
        $status = $request->status;
        $order = $user->orders()->latest()->with(["products" => function ($q) {
            $q->whereHas('product')->with(["product" => function ($q) {
                $q->with(["postedBy" =>  function ($q) {
                    $q->select("id", "name", "email", "phone", "signature", "picture");
                }]);
            }]);
        }])->paginate($per_page);

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [$order],
            [
            ]
        );
    }

    public function order($id) {
        $order = Order::with(["products" => function ($q) {
            $q->whereHas('product')->with(["product" => function ($q) {
                $q->with(["postedBy" =>  function ($q) {
                    $q->select("id", "name", "email", "phone", "signature", "picture");
                }]);
            }]);
        }])->find($id);
        if ($order)
            return $this->handleResponse(
                true,
                "عملية ناجحة",
                [],
                [$order],
                []
            );

        return $this->handleResponse(
            false,
            "",
            ["Invalid Order id"],
            [],
            []
        );
    }

    public function requestsAll(Request $request) {
        $user = $request->user();
        $requests = $user->requests()->latest()->with(["products" => function ($q) {
            $q->whereHas('product')->with(["product"]);
        }, "requested_by" => function ($q) {
            $q->select("id", "name", "email", "phone", "signature", "picture");
        }])->get();

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [$requests],
            [
            ]
        );
    }

    public function requestsPagination(Request $request) {
        $per_page = $request->per_page ? $request->per_page : 10;

        $user = $request->user();
        $status = $request->status;
        $order = $user->orders()->latest()->with(["products" => function ($q) {
            $q->whereHas('product')->with(["product"]);
        }, "requested_by" => function ($q) {
            $q->select("id", "name", "email", "phone", "signature", "picture");
        }])->paginate($per_page);

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [$order],
            [
            ]
        );
    }

}
