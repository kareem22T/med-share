@extends('Admin.layouts.main')

@section("title", "Pharmaciess")

@php
    $pharmacies = App\Models\User::all();
@endphp

@section("content")
<style>
    #dataTable_wrapper {
        min-width: 100%;
    }
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Pharmaciess</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive" style="width: auto">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>User Phone</th>
                        <th>User email</th>
                        <th>Pharmacy Name</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pharmacies as $phar)
                        <tr>
                            <td>{{ $phar->name }}</td>
                            <td>{{ $phar->phone }}</td>
                            <td>{{ $phar->email }}</td>
                            <td>{{ $phar->pharmacy_name }}</td>
                            <td>
                                <div class="d-flex gap-2" style="gap: 16px">

                                    <form action="{{route('admin.pharmacies.pharmacy.toggleApprove', ['id' => $phar->id])}}" method="post">
                                        @csrf
                                        @if($phar->isApproved)
                                        <button type="submit" class="btn btn-danger" style="margin: auto; display: block;white-space: nowrap">Ban</button>
                                    @else
                                        <button type="submit" class="btn btn-success" style="margin: auto; display: block;white-space: nowrap">Approve</button>
                                    @endif
                                </form>
                                <a href="{{ $phar->signature }}" download="download" class="btn btn-primary">Download Signature</a>
                            </div>
                            </td>
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
