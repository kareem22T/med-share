<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HandleResponseTrait;
use App\SaveImageTrait;
use App\DeleteImageTrait;
use App\Models\Banner;
use App\Models\Product;
use App\Models\Gallery;
use Illuminate\Support\Facades\Validator;

class BacnnerController extends Controller
{
    use HandleResponseTrait, SaveImageTrait, DeleteImageTrait;

    public function index() {
        return view('Admin.banners.index');
    }

    public function get() {
        $banners = Banner::all();

        return $this->handleResponse(
            true,
            "",
            [],
            [
                $banners
            ],
            []
        );
    }

    public function add() {
        return view("Admin.banners.create");
    }

    public function edit($id) {
        $Banner = Banner::latest()->find($id);

        if ($Banner)
            return view("Admin.banners.edit")->with(compact("Banner"));

        return $this->handleResponse(
            false,
            "Banner not exits",
            ["Categry id not valid"],
            [],
            []
        );
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            "description" => ["required"],
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            "name.required" => "ادخل اسم الاعلان",
            "name.max" => "يجب الا يتعدى اسم الاعلان 100 حرف",
            "description.required" => "ادخل وصف الاعلان",
            "thumbnail.required" => "الصورة المصغرة للاعلان مطلوبة",
            "thumbnail.image" => "من فضلك ارفع صورة صالحة",
            "thumbnail.mimes" => "يجب ان تكون الصورة بين هذه الصيغ (jpeg, png, jpg, gif)",
            "thumbnail.max" => "يجب الا يتعدى حجم الصورة 2 ميجا",
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

        if ($request->thumbnail)
            $image = $this->saveImg($request->thumbnail, 'images/uploads/banners', $request->name);

        $Banner = Banner::create([
            "description" => $request->description,
            "thumbnail_path" => '/images/uploads/banners/' . $image,
        ]);

        if ($Banner)
            return $this->handleResponse(
                true,
                "تم اضافة الاعلان بنجاح",
                [],
                [],
                []
            );

    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            "id" => ["required"],
            "description" => ["required"],
            'thumbnail' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            "description.required" => "ادخل وصف الاعلان",
            "thumbnail.required" => "الصورة المصغرة للاعلان مطلوبة",
            "thumbnail.image" => "من فضلك ارفع صورة صالحة",
            "thumbnail.mimes" => "يجب ان تكون الصورة بين هذه الصيغ (jpeg, png, jpg, gif)",
            "thumbnail.max" => "يجب الا يتعدى حجم الصورة 2 ميجا",
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

        $Banner = Banner::find($request->id);

        if ($request->thumbnail) {
            $this->deleteFile(base_path($Banner->thumbnail_path));
            $image = $this->saveImg($request->thumbnail, 'images/uploads/banners', $request->name);
            $Banner->thumbnail_path= '/images/uploads/banners/' . $image;
        }

        $Banner->description = $request->description;
        $Banner->save();

        if ($Banner)
            return $this->handleResponse(
                true,
                "تم تحديث الاعلان بنجاح",
                [],
                [],
                []
            );

    }

    public function deleteIndex($id) {
        $Banner = Banner::find($id);

        if ($Banner)
            return view("Admin.banners.delete")->with(compact("Banner"));

        return $this->handleResponse(
            false,
            "Banner not exits",
            ["Categry id not valid"],
            [],
            []
        );
    }


    public function delete(Request $request) {
        $validator = Validator::make($request->all(), [
            "id" => ["required"],
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

        $Banner = Banner::find($request->id);

        $this->deleteFile(base_path($Banner->thumbnail_path));

        $Banner->delete();

        if ($Banner)
            return $this->handleResponse(
                true,
                "تم حذف الاعلان بنجاح",
                [],
                [],
                []
            );

    }

}
