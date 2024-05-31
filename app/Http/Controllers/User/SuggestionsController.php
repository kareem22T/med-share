<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HandleResponseTrait;
use Illuminate\Support\Facades\Validator;
use App\SendEmailTrait;
use App\Models\Suggestion;

class SuggestionsController extends Controller
{
    use HandleResponseTrait, SendEmailTrait;

    public function placeSuggestion(Request $request) {
        $validator = Validator::make($request->all(), [
            "name" => ["required"],
            "email" => ["required", "email"],
            "msg" => ["required"],
        ], [
            "name.required" => "الاسم مطلوب",
            "email.required" => "البريد الالكتروني مطلوب",
            "email.email" => "ادخل بريد الكتروني صالح",
            "msg.required" => "من فضلك ادخل محتوي الرسالة",
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

        $suggestion = Suggestion::create($request->toArray());

        return $this->handleResponse(
            true,
            "تم ارسال رسالتك بنجاح شكرا لك",
            [],
            [],
            []
        );
    }
}
