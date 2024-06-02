<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HandleResponseTrait;
use App\SaveImageTrait;
use App\DeleteImageTrait;
use App\Models\Category;
use App\Models\Product;
use App\Models\Gallery;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    use HandleResponseTrait, SaveImageTrait, DeleteImageTrait;

    public function index() {
        return view('Admin.categories.index');
    }

    public function get() {
        $categories = Category::all();

        return $this->handleResponse(
            true,
            "",
            [],
            [
                $categories
            ],
            []
        );
    }

    public function add() {
        return view("Admin.categories.create");
    }

    public function edit($id) {
        $category = Category::latest()->find($id);

        if ($category)
            return view("Admin.categories.edit")->with(compact("category"));

        return $this->handleResponse(
            false,
            "Category not exits",
            ["Categry id not valid"],
            [],
            []
        );
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            "name" => ["required", "max:100"],
            "description" => ["required"],
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            "name.required" => "ادخل اسم القسم",
            "name.max" => "يجب الا يتعدى اسم القسم 100 حرف",
            "description.required" => "ادخل وصف القسم",
            "thumbnail.required" => "الصورة المصغرة للقسم مطلوبة",
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
            $image = $this->saveImg($request->thumbnail, 'images/uploads/Categories', $request->name);

        $category = Category::create([
            "name" => $request->name,
            "description" => $request->description,
            "thumbnail_path" => '/images/uploads/Categories/' . $image,
        ]);

        if ($category)
            return $this->handleResponse(
                true,
                "تم اضافة القسم بنجاح",
                [],
                [],
                []
            );

    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            "id" => ["required"],
            "name" => ["required", "max:100"],
            "description" => ["required"],
            'thumbnail' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            "name.required" => "ادخل اسم القسم",
            "name.max" => "يجب الا يتعدى اسم القسم 100 حرف",
            "description.required" => "ادخل وصف القسم",
            "thumbnail.required" => "الصورة المصغرة للقسم مطلوبة",
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

        $category = Category::find($request->id);

        if ($request->thumbnail) {
            $this->deleteFile(base_path($category->thumbnail_path));
            $image = $this->saveImg($request->thumbnail, 'images/uploads/Categories', $request->name);
            $category->thumbnail_path= '/images/uploads/Categories/' . $image;
        }

        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();

        if ($category)
            return $this->handleResponse(
                true,
                "تم تحديث القسم بنجاح",
                [],
                [],
                []
            );

    }

    public function deleteIndex($id) {
        $category = Category::find($id);

        if ($category)
            return view("Admin.categories.delete")->with(compact("category"));

        return $this->handleResponse(
            false,
            "Category not exits",
            ["Categry id not valid"],
            [],
            []
        );
    }

    public function deleteProduct($id) {
        $product = Product::with("gallery")->find($id);

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

        $category = Category::find($request->id);

        $this->deleteFile(base_path($category->thumbnail_path));

        foreach ($category->products()->get() as $prod) {
            $this->deleteProduct($prod->id);
        }

        $category->delete();

        if ($category)
            return $this->handleResponse(
                true,
                "تم حذف القسم بنجاح",
                [],
                [],
                []
            );

    }

}
