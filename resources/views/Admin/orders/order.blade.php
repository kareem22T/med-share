@extends('Admin.layouts.main')

@section("title", "Order #" . $order->id . " Details")

@section("content")
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Order #{{$order->id}} Details</h1>
    <a href="{{ route("admin.orders.show.all") }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
            class="fas fa-arrow-left fa-sm text-white-50"></i> Back</a>
</div>
<div class="card p-3 mb-3">
    <h2>Orderd by:</h2>
    <div class="user_details" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px">
        <div class="form-group">
            <label>User Name</label>
            <span class="form-control">{{ $order->buyer ? $order->buyer->name : "Missing" }}</span>
        </div>
        <div class="form-group">
            <label>User Email</label>
            <span class="form-control">{{ $order->buyer ? $order->buyer->email : "Missing" }}</span>
        </div>
        <div class="form-group">
            <label>User Phone</label>
            <span class="form-control">{{ $order->buyer ? $order->buyer->phone : "Missing" }}</span>
        </div>
        <div class="form-group">
            <label>User Pharmacy</label>
            <span class="form-control">{{ $order->buyer ? $order->buyer->pharmacy_name : "Missing" }}</span>
        </div>
    </div>
    <hr>
    <h2>Order Information:</h2>
    <div class="user_details" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px">
        <div class="form-group">
            <label>Date</label>
            <span class="form-control">{{ $order->created_at }}</span>
        </div>
        <div class="form-group">
            <label>Sub Total</label>
            <span class="form-control">{{ $order->sub_total }}</span>
        </div>
    </div>
    <hr>
    <h2>Order Products:</h2>
    <div class="table-responsive p-2">
        <table class="table table-bordered" width="100%" cellspacing="0" style="white-space: nowrap;">
            <thead>
                <tr>
                    <th>Product Id</th>
                    <th>Product Name</th>
                    <th>Product Sold Price</th>
                    <th>Product Sold Quantity</th>
                    <th>Product Souce</th>
                </tr>
            </thead>
            <tbody>
                    @foreach ($order->products as $product)
                    @if($product->product)
                    <tr>
                        <td>{{ $product->product->id }}</td>
                        <td>{{ $product->product->name }}</td>
                        <td>{{ $product->price_in_order }}</td>
                        <td>{{ $product->ordered_quantity }}</td>
                        <td>{{ $product->product->postedBy->pharmacy_name }}</td>
                    </tr>
                    @else
                    <tr class="text-center text-danger">
                        <td colspan="5">Missing Product may be deleted</td>
                    </tr>
                    @endif
                    @endforeach
            </tbody>
        </table>
    </div>

</div>

@endSection
