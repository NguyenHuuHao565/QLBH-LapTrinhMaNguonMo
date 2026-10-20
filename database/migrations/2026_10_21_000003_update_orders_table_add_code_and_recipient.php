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
            $table->renameColumn('customer_name', 'recipient_name');
            $table->renameColumn('phone', 'recipient_phone');
            $table->renameColumn('address', 'shipping_address');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('code', 20)->unique()->after('id');
            $table->timestamp('placed_at')->useCurrent()->after('status');

            $table->index('status');
            $table->index('placed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['placed_at']);
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'placed_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('recipient_name', 'customer_name');
            $table->renameColumn('recipient_phone', 'phone');
            $table->renameColumn('shipping_address', 'address');
        });
    }
};
