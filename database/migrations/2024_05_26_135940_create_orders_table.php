<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('buyer_id');
            $table->boolean('payment_methode'); // 1 => cash on delivary
            $table->boolean('status')->default(1);
            $table->boolean('is_paid')->default(0);
            $table->float('shipping_fees')->default(0);
            $table->float('payment_fees')->default(0);
            $table->float('sub_total');
            $table->float('total')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
