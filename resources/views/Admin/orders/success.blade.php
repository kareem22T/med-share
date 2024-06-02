@extends('Admin.layouts.main')

@section("title", "Approved successfuly")

@section("content")
    <div class="card p-4">
        <h1 class="text-center m-2" style="font-weight: 600; color: #00b341">Successfuly Operation</h1>
        <svg xmlns="http://www.w3.org/2000/svg" style="  width: 250px;  height: 250px;
        margin: auto;" class="icon icon-tabler icon-tabler-checklist" width="20" height="20" viewBox="0 0 24 24" stroke-width="2.5" stroke="#00b341" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M9.615 20h-2.615a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8" />
            <path d="M14 19l2 2l4 -4" />
            <path d="M9 8h4" />
            <path d="M9 12h2" />
          </svg>
        <div class="btns d-flex justify-content-center align-items-center">
            <a href="{{route('admin.orders.order.details', ['id' => $order->id])}}" class="btn btn-info w-25 m-3">Show Order</a>
            <a href="{{route('admin.orders.show.all')}}" class="btn btn-secondary w-25 m-3">Back To List</a>
        </div>
    </div>
@endSection
