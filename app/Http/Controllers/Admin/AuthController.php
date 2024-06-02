<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HandleResponseTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use HandleResponseTrait;

    public function index () {
        return view("Admin.auth.login");
    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
        ], [
            "email.required" => "ادخل البريد الالكتروني",
            "email.email" => "ادخل بريد الكتروني صحيح",
            "password.required" => "ادخل كلمة مرور لا تقل عن 8 احرف",
            "password.min" => "ادخل كلمة مرور لا تقل عن 8 احرف",
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

        $createAdmin = Admin::all()->count() > 0 ? '' : Admin::create(['username' => 'Admin', 'email' => 'admin@gmail.com', 'password' => Hash::make('admin123'), "role" => "Master"]);


        $credentials = ['email' => $request->input('email'), 'password' => $request->input('password')];

        if (Auth::guard('admin')->attempt($credentials)) {

            return $this->handleResponse(
                true,
                "تم التسجيل بنجاح",
                [],
                [],
                []
            );
        }

        return $this->handleResponse(
            false,
            "",
            ["بيانات غير صحيحة"],
            [],
            []
        );
    }

}
