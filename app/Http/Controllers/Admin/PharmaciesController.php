<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HandleResponseTrait;
use App\SaveImageTrait;
use App\DeleteImageTrait;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class PharmaciesController extends Controller
{
    use HandleResponseTrait, SaveImageTrait, DeleteImageTrait;

    public function index() {
        return view('Admin.pharmacies.index');
    }

    public function toggleApprove($id) {
        $prod = User::find($id);

        if ($prod) {
            $prod->isApproved = !$prod->isApproved;
            $prod->save();
        }

        return redirect()->back();
    }

}
