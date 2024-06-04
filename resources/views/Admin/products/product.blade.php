@extends('Admin.layouts.main')

@section("title", "Products")

@section("content")
@if($prod)
<div class="card shadow mb-4">
    <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Product #{{ $prod->id }} Details</h1>
            </div>
            <div class="d-flex justify-content-between" style="gap: 16px">
                <div class="w-50">
                    <div class="form-group w-100">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name"  value="{{$prod->name}}" disabled>
                    </div>
                </div>
                <div class="w-50">
                    <div class="form-group w-100">
                        <label for="name" class="form-label">Posted By</label>
                        <input type="text" class="form-control" id="name"  value="{{$prod->postedBy->name}}" disabled>
                    </div>
                </div>
                <div class="w-50">
                    <div class="form-group w-100">
                        <label for="name" class="form-label">Pharmacy Name</label>
                        <input type="text" class="form-control" id="name"  value="{{$prod->postedBy->name}}" disabled>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between" style="gap: 16px">
                <div class="w-50">
                    <div class="form-group w-100">
                        <label for="name" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="name"  value="{{$prod->postedBy->phone}}" disabled>
                    </div>
                </div>
                <div class="w-50">
                    <div class="form-group w-100">
                        <label for="name" class="form-label">Email</label>
                        <input type="text" class="form-control" id="name"   value="{{$prod->postedBy->email}}" disabled>
                    </div>
                </div>
            </div>
            <div id="preview-gallery" class="mt-3">
                <div class="row">
                    @foreach ($prod->gallery as $item)
                <div
                    class="col-lg-3 col-md-6 mb-4">
                    <img src="{{ $item->path }}"
                        style="width: 100%; height: 250px; object-fit: cover;" alt="gallery">
                </div>
                @endforeach
                </div>
            </div>
            <form action="{{route('admin.products.product.toggleApprove', ['id' => $prod->id])}}" method="post">
                @csrf
                @if($prod->isApproved)
                    <button type="submit" class="btn btn-danger w-25" style="margin: auto; display: block">Ban</button>
                @else
                    <button type="submit" class="btn btn-success w-25" style="margin: auto; display: block">Approve</button>
                @endif
            </form>
        </div>
    </div>
@endif
@endSection


@section("scripts")
    <script src="{{ asset('/admin/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('/admin/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

    <!-- Page level custom scripts -->
    <script src="{{ asset('/admin/js/demo/datatables-demo.js') }}"></script>
@endSection
