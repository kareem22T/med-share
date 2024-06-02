@extends('Admin.layouts.main')

@section("title", "Products")

@php
    $products = App\Models\Product::with("postedBy")->get();
@endphp

@section("content")
<style>
    #dataTable_wrapper {
        min-width: 100%;
    }
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Products</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive" style="width: auto">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Posted By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $prod)
                        <tr>
                            <td>{{ $prod->name }}</td>
                            <td>{{ $prod->price }}</td>
                            <td>{{ $prod->quantity }}</td>
                            <td>{{ $prod->postedBy->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endSection


@section("scripts")
<script src="{{ asset('/admin/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('/admin/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

<!-- Page level custom scripts -->
<script src="{{ asset('/admin/js/demo/datatables-demo.js') }}"></script>
@endSection
