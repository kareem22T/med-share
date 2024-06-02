<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Order;
use App\HandleResponseTrait;
use App\SendEmailTrait;

class OrdersController extends Controller
{
    use HandleResponseTrait, SendEmailTrait;

    public function index() {
        return view("Admin.orders.all");
    }

    public function indexReview() {
        return view("Admin.orders.reviews");
    }

    public function indexConfirmed() {
        return view("Admin.orders.confirmed");
    }

    public function indexDelivary() {
        return view("Admin.orders.delivary");
    }

    public function indexCompleted() {
        return view("Admin.orders.completed");
    }

    public function indexCanceled() {
        return view("Admin.orders.canceled");
    }

    public function order($id) {
        $order = Order::with(["products" => function ($q) {
            $q->with(["product" => function ($q) {
                $q->with("category");
            }]);
        }, "user"])->find($id);
        if ($order)
            return view("Admin.orders.order")->with(compact("order"));

        return $this->handleResponse(
            false,
            "",
            ["Invalid Order id"],
            [],
            []
        );
    }

    public function approveIndex($id) {
        $order = Order::with(["products" => function ($q) {
            $q->with(["product" => function ($q) {
                $q->with("category");
            }]);
        }, "user"])->find($id);
        if ($order && $order->status !== 4 && $order->status !== 0)
            return view("Admin.orders.approve")->with(compact("order"));

        return $this->handleResponse(
            false,
            "",
            ["Invalid Order status"],
            [],
            []
        );
    }

    public function cancelIndex($id) {
        $order = Order::with(["products" => function ($q) {
            $q->with(["product" => function ($q) {
                $q->with("category");
            }]);
        }, "user"])->find($id);
        if ($order && $order->status !== 4 && $order->status !== 0)
            return view("Admin.orders.cancel")->with(compact("order"));

        return $this->handleResponse(
            false,
            "",
            ["Invalid Order status"],
            [],
            []
        );
    }

    public function approve($id) {
        $order = Order::with(["products" => function ($q) {
            $q->with(["product" => function ($q) {
                $q->with("category");
            }]);
        }, "user"])->find($id);

        if ($order->status === 1) {
            $order->status = 2;
            $order->save();
        }

        else if ($order->status === 2) {
            $order->status = 3;
            $order->save();
        }

        else if ($order->status === 3) {
            $order->status = 4;
            if ($order->user->user_type == 1) {
                $order->user->expected_profit = $order->user->expected_profit - ((float) $order->total_sell_price - (float) $order->sub_total);
                $order->user->balance = $order->user->balance + ((float) $order->total_sell_price - (float) $order->sub_total);
                $order->user->save();
                $transaction = Transaction::create([
                    "user_id" => $order->user->id,
                    "order_id" => $order->id,
                    "type" => 1,
                    "amount" => ((float) $order->total_sell_price - (float) $order->sub_total),
                ]);
                if ($order->user) {
                    $msg_title = "أضافة ربح " . ((float) $order->total_sell_price - (float) $order->sub_total) . " جنيها";
                    $msg_content = "<h1>";
                    $msg_content = " تم اطافة مبلغ" . ((float) $order->total_sell_price - (float) $order->sub_total) . " جنيها الي حسابك كعمولة من الطلب هذا <br> يمكنك طلب المبلغ الان من التطبيق";
                    $msg_content .= "</h1>";
                    $msg_content .= "<br>";
                    $msg_content .= "<h3>";
                    $msg_content .= "تفاصيل الطلب: ";
                    $msg_content .= "</h3>";

                    $msg_content .= "<h4>";
                    $msg_content .= "اسم المستلم: ";
                    $msg_content .= $order->recipient_name;
                    $msg_content .= "</h4>";


                    $msg_content .= "<h4>";
                    $msg_content .= "رقم هاتف المستلم: ";
                    $msg_content .= $order->recipient_phone;
                    $msg_content .= "</h4>";


                    $msg_content .= "<h4>";
                    $msg_content .= "عنوان المستلم: ";
                    $msg_content .= $order->recipient_address;
                    $msg_content .= "</h4>";


                    $msg_content .= "<h4>";
                    $msg_content .= "الاجمالي : ";
                    $msg_content .= $order->sub_total;
                    $msg_content .= "</h4>";


                    $msg_content .= "<h4>";
                    $msg_content .= "سعر البيع : ";
                    $msg_content .= $order->total_sell_price;
                    $msg_content .= "</h4>";

                    $this->sendEmail($order->user->email, $msg_title, $msg_content);
                }
            }
            $order->save();
        }

        if ($order) {
            return redirect('/admin/orders/order/success/' . $order->id);
        }

        return $this->handleResponse(
            false,
            "",
            ["Fail Proccess"],
            [],
            []
        );
    }

    public function cancel($id) {
        $order = Order::with(["products" => function ($q) {
            $q->with(["product" => function ($q) {
                $q->with("category");
            }]);
        }, "user"])->find($id);

       if ($order->status != 4 && $order->status != 0) {
            $order->status = 0;
            if ($order->user->user_type == 1) {
                $order->user->expected_profit = $order->user->expected_profit - ((float) $order->total_sell_price - (float) $order->sub_total);
                $order->user->save();
                if ($order->products()->count() > 0)
                    foreach ($order->products as $item) {
                        $item->product->quantity = (int) $item->product->quantity + (int) $item->ordered_quantity ;
                        $item->product->save();
                    }
                if ($order->user) {
                    $msg_title = "تم الغاء الطلب";
                    $msg_content = "<h1>";
                    $msg_content .= "تم الغاء هذا الطلب ولم يكتمل";
                    $msg_content .= "</h1>";
                    $msg_content .= "<br>";
                    $msg_content .= "<h3>";
                    $msg_content .= "تفاصيل الطلب: ";
                    $msg_content .= "</h3>";

                    $msg_content .= "<h4>";
                    $msg_content .= "اسم المستلم: ";
                    $msg_content .= $order->recipient_name;
                    $msg_content .= "</h4>";


                    $msg_content .= "<h4>";
                    $msg_content .= "رقم هاتف المستلم: ";
                    $msg_content .= $order->recipient_phone;
                    $msg_content .= "</h4>";


                    $msg_content .= "<h4>";
                    $msg_content .= "عنوان المستلم: ";
                    $msg_content .= $order->recipient_address;
                    $msg_content .= "</h4>";


                    $msg_content .= "<h4>";
                    $msg_content .= "الاجمالي : ";
                    $msg_content .= $order->sub_total;
                    $msg_content .= "</h4>";


                    $msg_content .= "<h4>";
                    $msg_content .= "سعر البيع : ";
                    $msg_content .= $order->total_sell_price;
                    $msg_content .= "</h4>";

                    $this->sendEmail($order->user->email, $msg_title, $msg_content);
                }
            }
            $order->save();
        }

        if ($order) {
            return redirect('/admin/orders/order/success/' . $order->id);
        }

        return $this->handleResponse(
            false,
            "",
            ["Fail Proccess"],
            [],
            []
        );
    }

    public function successIndex($id) {
        $order = Order::with(["products" => function ($q) {
            $q->with(["product" => function ($q) {
                $q->with("category");
            }]);
        }, "user"])->find($id);
        if ($order)
            return view("Admin.orders.success")->with(compact("order"));

        return $this->handleResponse(
            false,
            "",
            ["Invalid Order id"],
            [],
            []
        );
    }

}
