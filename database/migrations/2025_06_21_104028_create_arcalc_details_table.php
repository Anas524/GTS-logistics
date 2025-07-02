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
        Schema::create('arcalc_details', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('asin')->nullable();
            $table->text('brief_description')->nullable();
            $table->decimal('shipping_cost', 10, 2)->nullable();
            $table->decimal('labeling_charges', 10, 2)->nullable();
            $table->decimal('goods_cost', 10, 2)->nullable();
            $table->string('fulfillment')->nullable();
            $table->decimal('sell_price', 10, 2)->nullable();
            $table->decimal('amazon_storage_charges', 10, 2)->nullable();
            $table->decimal('tariff_percentage', 10, 2)->nullable();
            $table->string('origin_purchase')->nullable();
            $table->integer('units_sold')->nullable();
            $table->decimal('low_inventory_fee', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->text('product_link')->nullable();
            $table->longText('image_url')->nullable();

            // Calculated data
            $table->decimal('min_sell_price', 10, 2)->nullable();
            $table->decimal('profit', 10, 2)->nullable();
            $table->decimal('target_buy_price', 10, 2)->nullable();
            $table->decimal('net_proceeds', 10, 2)->nullable();
            $table->decimal('amazon_fees', 10, 2)->nullable();
            $table->decimal('tariff', 10, 2)->nullable();
            $table->string('return_value')->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arcalc_details');
    }
};
