<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Money_request;
use App\HandleResponseTrait;
use App\SendEmailTrait;

class MoneyRequestsContrller extends Controller
{
    use HandleResponseTrait, SendEmailTrait;

    public function index() {
        return view("Admin.requests.all");
    }

    public function indexReview() {
        return view("Admin.requests.reviews");
    }

    public function indexCompleted() {
        return view("Admin.requests.completed");
    }

    public function indexCanceled() {
        return view("Admin.requests.canceled");
    }


    public function approveIndex($id) {
        $request = Money_request::with(["user"])->find($id);
        if ($request && $request->status !== 2 && $request->status !== 0)
            return view("Admin.requests.approve")->with(compact("request"));

        return $this->handleResponse(
            false,
            "",
            ["Invalid request status"],
            [],
            []
        );
    }

    public function request($id) {
        $request = Money_request::with(["user"])->find($id);
        if ($request)
            return view("Admin.requests.request")->with(compact("request"));

        return $this->handleResponse(
            false,
            "",
            ["Invalid Request id"],
            [],
            []
        );
    }

    public function approve($id) {
        $request = Money_request::with([ "user"])->find($id);

        if ($request->status === 1) {
            $request->status = 2;
            if ($request->user->user_type == 1) {
                $request->user->balance = (float) $request->user->balance - ((float) $request->amount);
                $request->user->save();
                $transaction = Transaction::create([
                    "user_id" => $request->user->id,
                    "request_id" => $request->id,
                    "type" => 2,
                    "amount" => ((float) $request->amount),
                ]);
                if ($request->user) {
                    $msg_title = "سحب" . ((float) $request->amount) . " جنيها";
                    $msg_content = "<h1>";
                    $msg_content = "تم سحب مبلغ " . ((float) $request->amount) . " من حسابك الان في حالة ان المبلغ لم يصل لك يمكنك الاتصال ب 123456 للاستفسار";
                    $msg_content .= "</h1>";

                    $this->sendEmail($request->user->email, $msg_title, $msg_content);
                }
            }
            $request->save();
        }

        if ($request) {
            return redirect('/admin/requests/request/success/' . $request->id);
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
        $request = Money_request::with(["user"])->find($id);
        if ($request)
            return view("Admin.requests.success")->with(compact("request"));

        return $this->handleResponse(
            false,
            "",
            ["Invalid request id"],
            [],
            []
        );
    }

    public function cancelIndex($id) {
        $request = Money_request::with(["user"])->find($id);

        if ($request && $request->status !== 2 && $request->status !== 0)
            return view("Admin.requests.cancel")->with(compact("request"));

        return $this->handleResponse(
            false,
            "",
            ["Invalid request status"],
            [],
            []
        );
    }

    public function cancel($id) {
        if (!request()->reason)
            return redirect()->back()->withErrors(['reason' => 'You have to write the reason of rejection'])->withInput();

        $request = Money_request::with([ "user"])->find($id);

       if ($request->status != 2 && $request->status != 0) {
            $request->status = 0;
            $request->save();
            if ($request->user->user_type == 1) {
                if ($request->user) {
                    $msg_title = "تم رفض طلب السحب";
                    $msg_content = "<h1>";
                    $msg_content .= "تم رفض طلبك لسحب مبلغ ";
                    $msg_content .= $request->amount . " جنيها للاسباب التالية";
                    $msg_content .= "</h1>";
                    $msg_content .= "<br>";
                    $msg_content .= "<h3>";
                    $msg_content .= "تفاصيل الرفض: ";
                    $msg_content .= "</h3>";
                    $msg_content .= "<h3>";
                    $msg_content .= request()->reason;
                    $msg_content .= "</h3>";

                    $this->sendEmail($request->user->email, $msg_title, $msg_content);
                }
            }
        }

        if ($request) {
            return redirect('/admin/requests/request/success/' . $request->id);
        }

        return $this->handleResponse(
            false,
            "",
            ["Fail Proccess"],
            [],
            []
        );
    }
}
