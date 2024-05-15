<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HandleResponseTrait;
use App\SaveImageTrait;
use App\DeleteImageTrait;
use App\SendEmailTrait;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AuthController extends Controller
{
    use HandleResponseTrait, SaveImageTrait, DeleteImageTrait, SendEmailTrait;

    public function register(Request $request) {
        if (!$request->accepts('application/json')) {
            return response()->json(['error' => 'Invalid content type'], 400);
        }

        $validator = Validator::make($request->json()->all(), [
            "name" => ["required"],
            "email" => ["required", "email", "unique:users,email"],
            "phone" => ["required", "unique:users,phone"],
            "pharmacy_name" => ["required"],
            'signature' => "required|max:2048|image|mimes:jpeg,png,jpg,gif|max:2048",
            'password' => [
                'required', // Required only if joined_with is 1
                'min:8',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/u',
            ],
        ], [
            "name.required" => "ادخل اسمك الثلاثي",
            "pharmacy_name.required" => "ادخل اسم الصيدلية",
            "signature.required" => "ارفع صورة الختم ع ورقة بيضة",
            "signature.image" => "من فضلك ارفع صورة صالحة بين jpeg,png,jpg,gif",
            "signature.mimes" => "يجب ان تكون الصورة بين هذه الصيغ (jpeg, png, jpg, gif)",
            "signature.max" => "يجب الا يتعدى حجم الصورة 2 ميجا",
            "name.regex" => "ادخل الاسم ثلاثي",
            "email.required" => "من فضلك ادخل بريد الكتروني صالح",
            "email.email" => "من فضلك ادخل بريد الكتروني صالح",
            "email.unique" => "هذا الحساب موجود بالفعل",
            "phone.required" => "ادخل رقم الهاتف",
            "phone.unique" => "هذا الرقم موجود بالفعل",
            "password.required" => "ادخل كلمة المرور",
            "password.min" => "يجب ان تكون كلمة المرور من 8 احرف على الاقل",
            "password.regex" => "يجب ان تحتوي كلمة المرور علي حروف وارقام ورموز",
            "password.confirmed" => "كلمة المرور والتاكيد غير متطابقان",
        ]);

        if ($validator->fails()) {
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                [
                    "request" => $request
                ],
            );
        }

        $image = $this->saveImg($request->signature, 'images/uploads/Users/Signature', $request->email);

        $user = User::create([
            "name" => $request->name,
            "pharmacy_name" => $request->pharmacy_name,
            "email" => $request->email,
            "phone" => $request->phone,
            "signature" => '/images/uploads/Users/Signature' . $image,
            "is_phone_verified" => false,
            "is_email_verified" => false,
            "password" => Hash::make($request->password),
        ]);


        if ($user) :
            $token = $user->createToken('token')->plainTextToken;

            return $this->handleResponse(
                true,
                "تم انشاء حسابك بنجاح",
                [],
                [
                    "user" => $user->only("name", "email", "phone", "is_email_verified", "is_phone_verified"),
                    "token" => $token
                ],
                []
            );
        endif;
    }

    public function askEmailCode(Request $request) {
        $user = $request->user();

        if ($user) {
            $code = rand(100000, 999999);

            $user->email_last_verfication_code = Hash::make($code);
            $user->email_last_verfication_code_expird_at = Carbon::now()->addMinutes(10)->timezone('Europe/Istanbul');
            $user->save();

            $msg_title = "تفضل رمز تفعيل بريدك الالكتروني";
            $msg_content = "<h1>";
            $msg_content .= "رمز التاكيد هو <span style='color: blue'>" . $code . "</span>";
            $msg_content .= "</h1>";

            $this->sendEmail($user->email, $msg_title, $msg_content);

            return $this->handleResponse(
                true,
                "تم ارسال رمز التحقق بنجاح عبر الايميل",
                [],
                [],
                [
                    "code get expired after 10 minuts",
                    "the same endpoint you can use for ask resend email"
                ]
            );
        }

        return $this->handleResponse(
            false,
            "",
            ["invalid process"],
            [],
            [
                "code get expired after 10 minuts",
                "the same endpoint you can use for ask resend email"
            ]
        );
    }

    public function verifyEmail(Request $request) {
        $validator = Validator::make($request->all(), [
            "code" => ["required"],
        ], [
            "code.required" => "ادخل رمز التاكيد ",
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


        $user = $request->user();
        $code = $request->code;

        if ($user) {
            if (!Hash::check($code, $user->email_last_verfication_code)) {
                return $this->handleResponse(
                    false,
                    "",
                    ["الرمز غير صحيح"],
                    [],
                    []
                );
            } else {
                $timezone = 'Europe/Istanbul'; // Replace with your specific timezone if different
                $verificationTime = new Carbon($user->email_last_verfication_code_expird_at, $timezone);
                if ($verificationTime->isPast()) {
                    return $this->handleResponse(
                        false,
                        "",
                        ["الرمز غير ساري"],
                        [],
                        []
                    );
                } else {
                    $user->is_email_verified = true;
                    $user->save();

                    if ($user) {
                        return $this->handleResponse(
                            true,
                            "تم تاكيد بريدك الالكتروني بنجاح",
                            [],
                            [],
                            []
                        );
                    }
                }
            }
        }

    }

    public function changePassword(Request $request) {
        $validator = Validator::make($request->all(), [
            "old_password" => ["required"],
            'password' => [
                'required', // Required only if joined_with is 1
                'min:8',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/u',
                'confirmed'
            ],
        ], [
            "old_password.required" => "ادخل كلمة المرور الحالية",
            "password.required" => "ادخل كلمة المرور",
            "password.min" => "يجب ان تكون كلمة المرور من 8 احرف على الاقل",
            "password.regex" => "يجب ان تحتوي كلمة المرور علي حروف وارقام ورموز",
            "password.confirmed" => "كلمة المرور والتاكيد غير متطابقان",
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


        $user = $request->user();

        if ($user) {
            if (!Hash::check($request->old_password, $user->password)) {
                return $this->handleResponse(
                    true,
                    "",
                    ["كلمة المرور الحالية غير صحيحة"],
                    [],
                    []
                );
            }

            $user->password = Hash::make($user->password);
            $user->save();

            return $this->handleResponse(
                true,
                "تم تغير كلمة المرور بنجاح",
                [],
                [],
                []
            );
        }

    }

    public function askEmailCodeForgot(Request $request) {
        $validator = Validator::make($request->all(), [
            "email" => ["required", "email"],
        ], [
            "email.required" => "من فضلك ادخل بريدك الاكتروني ",
            "email.email" => "من فضلك ادخل بريد الكتروني صالح",
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

        $user = User::where("email", $request->email)->first();

        if ($user) {

            if ((int) $user->joined_with === 2)
                return $this->handleResponse(
                    true,
                    "",
                    ["هذا الحساب مسجل بواسطة جوجل يمكنك الدخول باستخدام التسجيل بجوجل"],
                    [],
                    [
                        "code get expired after 10 minuts",
                        "the same endpoint you can use for ask resend email"
                    ]
                );

            if ((int) $user->joined_with === 3)
                return $this->handleResponse(
                    true,
                    "",
                    ["هذا الحساب مسجل بواسطة فيسبوك يمكنك الدخول باستخدام التسجيل بفيسبوك"],
                    [],
                    [
                        "code get expired after 10 minuts",
                        "the same endpoint you can use for ask resend email"
                    ]
                );

            $code = rand(100000, 999999);

            $user->email_last_verfication_code = Hash::make($code);
            $user->email_last_verfication_code_expird_at = Carbon::now()->addMinutes(10)->timezone('Europe/Istanbul');
            $user->save();

            $msg_title = "تفضل رمز تفعيل بريدك الالكتروني";
            $msg_content = "<h1>";
            $msg_content .= "رمز التاكيد هو <span style='color: blue'>" . $code . "</span>";
            $msg_content .= "</h1>";

            $this->sendEmail($user->email, $msg_title, $msg_content);

            return $this->handleResponse(
                true,
                "تم ارسال رمز التحقق بنجاح عبر الايميل",
                [],
                [],
                [
                    "code get expired after 10 minuts",
                    "the same endpoint you can use for ask resend email"
                ]
            );
        } else {
            return $this->handleResponse(
                false,
                "",
                ["هذا الحساب غير مسجل"],
                [],
                []
            );
        }

        return $this->handleResponse(
            false,
            "",
            ["invalid process"],
            [],
            [
                "code get expired after 10 minuts",
                "the same endpoint you can use for ask resend email"
            ]
        );
    }

    public function forgetPassword(Request $request) {
        $validator = Validator::make($request->all(), [
            "email" => ["required", "email"],
            "code" => ["required"],
            'password' => [
                'required', // Required only if joined_with is 1
                'min:8',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/u',
                'confirmed'
            ],
        ], [
            "code.required" => "ادخل رمز التاكيد ",
            "email.required" => "من فضلك ادخل بريدك الاكتروني ",
            "email.email" => "من فضلك ادخل بريد الكتروني صالح",
            "password.required" => "ادخل كلمة المرور",
            "password.min" => "يجب ان تكون كلمة المرور من 8 احرف على الاقل",
            "password.regex" => "يجب ان تحتوي كلمة المرور علي حروف وارقام ورموز",
            "password.confirmed" => "كلمة المرور والتاكيد غير متطابقان",
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


        $user = User::where("email", $request->email)->first();
        $code = $request->code;

        if ($user) {
            if ((int) $user->joined_with === 2)
            return $this->handleResponse(
                true,
                "",
                ["هذا الحساب مسجل بواسطة جوجل يمكنك الدخول باستخدام التسجيل بجوجل"],
                [],
                [
                    "code get expired after 10 minuts",
                    "the same endpoint you can use for ask resend email"
                ]
            );

            if ((int) $user->joined_with === 3)
                return $this->handleResponse(
                    true,
                    "",
                    ["هذا الحساب مسجل بواسطة فيسبوك يمكنك الدخول باستخدام التسجيل بفيسبوك"],
                    [],
                    [
                        "code get expired after 10 minuts",
                        "the same endpoint you can use for ask resend email"
                    ]
                );

            if (!Hash::check($code, $user->email_last_verfication_code)) {
                return $this->handleResponse(
                    false,
                    "",
                    ["الرمز غير صحيح"],
                    [],
                    []
                );
            } else {
                $timezone = 'Europe/Istanbul'; // Replace with your specific timezone if different
                $verificationTime = new Carbon($user->email_last_verfication_code_expird_at, $timezone);
                if ($verificationTime->isPast()) {
                    return $this->handleResponse(
                        false,
                        "",
                        ["الرمز غير ساري"],
                        [],
                        []
                    );
                } else {
                    $user->password = Hash::make($request->password);
                    $user->save();

                    if ($user) {
                        return $this->handleResponse(
                            true,
                            "تم تعين كلمة المرور بنجاح ",
                            [],
                            [],
                            []
                        );
                    }
                }
            }
        } else {
            return $this->handleResponse(
                false,
                "",
                ["هذا الحساب غير مسجل"],
                [],
                []
            );
        }

    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            "email" => ["required", "email"],
            'password' => [
                'required', // Required only if joined_with is 1
                'min:8',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]+$/u',
            ],
        ], [
            "email.required" => "من فضلك ادخل بريد الكتروني صالح",
            "email.email" => "من فضلك ادخل بريد الكتروني صالح",
            "password.required_if" => "ادخل كلمة المرور",
            "password.min" => "يجب ان تكون كلمة المرور من 8 احرف على الاقل",
            "password.regex" => "يجب ان تحتوي كلمة المرور علي حروف وارقام ورموز",
        ]);

        if ($validator->fails()) {
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                [
                ]
            );
        }

        $user = User::where("email", $request->email)->first();

        if ($user) {
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $userManual = Auth::user();
                $token = $userManual->createToken('token')->plainTextToken;
                return $this->handleResponse(
                    true,
                    "تم التسجيل بنجاح",
                    [],
                    [
                        "user" => $userManual->only("name", "email", "phone", "user_type", "picture", "is_email_verified", "is_phone_verified"),
                        "token" => $token
                    ],
                    [
                    ]
                );
            } else {
                return $this->handleResponse(
                    false,
                    "كلمة مرور خاطئة",
                    [],
                    [],
                    []
                );
            }
}
        return $this->handleResponse(
            false,
            "",
            ["هذا الحساب غير مسجل"],
            [],
            []
        );
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'picture' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            "name" => ['regex:/^[\p{L} ]{3,}\s[\p{L} ]{3,}\s[\p{L} ]{3,}$/u'],
            "phone" => ["unique:users,phone"],
        ], [
            "name.required" => "ادخل اسمك الثلاثي",
            "name.regex" => "ادخل الاسم ثلاثي",
            "phone.unique" => "هذا الرقم موجود بالفعل",
            "picture.image" => "من فضلك ارفع صورة صالحة",
            "picture.mimes" => "يجب ان تكون الصورة بين هذه الصيغ (jpeg, png, jpg, gif)",
            "picture.max" => "يجب الا يتعدى حجم الصورة 2 ميجا",
        ]);

        if ($validator->fails()) {
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                [
                    "validation" => [
                        "phone" => "متبعتش الرقم في التعديل غير لو اتغير لاني مش هيعدي لو نفس القديم",
                        "picture" => "لازم تكون صورة وتكون اقل من 2 ميجا",
                        "name" => "الاسم لازم ثلاثي",
                        "general" => "مفيش حاجة مطلوبة انت اللي يتعدل ابعته لو معدلش متبعتش ال key عادي"
                    ],
                    "user_type" => [
                        "1" => "تعني مسوق",
                        "2" => "تعني تاجر",
                    ],
                    "joind_with" => [
                        "1" => "تعني تسجيل يدوي",
                        "2" => "تعني تسجيل عن طريق جوجل ولا يشترط ارسال كلمة مرور",
                        "3" => "تعني تسجيل عن طريق فيس بوك ولا يشترط ارسال كلمة مرور",
                    ]
                ]
            );
        }


        $user = $request->user();

        if ($user) {
            if ($request->name)
                $user->name = $request->name;

            if ($request->phone) {
                $user->phone = $request->phone;
                if ($user->phone != $request->phone )
                    $user->is_phone_verified = false;
            }

            if ($request->picture) {
                $image = $this->saveImg($request->picture, 'images/uploads/Users', "user_" . $user->id);
                $user->picture = '/images/uploads/Users/' . $image;
            }

            $user->save();

            if ($user) {
                return $this->handleResponse(
                    true,
                    "تم تحديث البيانات بنجاح",
                    [],
                    [
                        "user" => $user->only("name", "email", "phone", "user_type", "picture", "is_email_verified", "is_phone_verified")
                    ],
                    [
                        "validation" => [
                            "phone" => "متبعتش الرقم في التعديل غير لو اتغير لاني مش هيعدي لو نفس القديم",
                            "picture" => "لازم تكون صورة وتكون اقل من 2 ميجا",
                            "name" => "الاسم لازم ثلاثي",
                            "general" => "مفيش حاجة مطلوبة انت اللي يتعدل ابعته لو معدلش متبعتش ال key عادي"
                        ],
                        "user_type" => [
                            "1" => "تعني مسوق",
                            "2" => "تعني تاجر",
                        ],
                        "joind_with" => [
                            "1" => "تعني تسجيل يدوي",
                            "2" => "تعني تسجيل عن طريق جوجل ولا يشترط ارسال كلمة مرور",
                            "3" => "تعني تسجيل عن طريق فيس بوك ولا يشترط ارسال كلمة مرور",
                        ]
                    ]
                );
            }
        }
    }

    public function getUser(Request $request) {
        $user = $request->user();

        if ($user) {
            return $this->handleResponse(
                true,
                "عملية ناجحة",
                [],
                [
                    "user" => $user->only("name", "email", "phone", "user_type", "picture", "is_email_verified", "is_phone_verified")
                ],
                [
                    "user_type" => [
                        "1" => "تعني مسوق",
                        "2" => "تعني تاجر",
                    ],
                    "joind_with" => [
                        "1" => "تعني تسجيل يدوي",
                        "2" => "تعني تسجيل عن طريق جوجل ولا يشترط ارسال كلمة مرور",
                        "3" => "تعني تسجيل عن طريق فيس بوك ولا يشترط ارسال كلمة مرور",
                    ]
                ]
            );
        }
    }

    public function logout(Request $request) {
        $user = $request->user();

        if ($user) {
            if ($user->tokens())
                $user->tokens()->delete();
        }

        return $this->handleResponse(
            true,
            "تم تسجيل الخروج بنجاح",
            [],
            [
            ],
            [
                "On logout" => "كل التوكينز بتتمسح انت كمان امسحها من الكاش عندك"
            ]
        );
    }
}
