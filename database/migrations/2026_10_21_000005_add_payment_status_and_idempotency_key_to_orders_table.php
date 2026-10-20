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
        Schema::table('orders', function (Blueprint $table) {
            // Trạng thái thanh toán tách riêng trạng thái đơn: COD luôn bắt đầu là 'unpaid'
            $table->string('payment_status', 20)->default('unpaid')->after('payment_method');
            // Khóa chống gửi trùng đơn (double-click / gửi lại form)
            $table->string('idempotency_key', 64)->nullable()->unique()->after('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn(['payment_status', 'idempotency_key']);
        });
    }
};
