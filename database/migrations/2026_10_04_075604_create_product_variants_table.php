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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->string('sku', 100)->unique();
            $table->string('variant_name', 100);
            $table->string('color', 50)->nullable();
            $table->string('material', 100)->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);

            $table->timestamps();

            $table->index('product_id');
            $table->index('stock');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
