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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->foreignUuid('brand_id')
                ->nullable()
                ->constrained('brands')
                ->nullOnDelete();

            $table->string('sku', 50)->unique();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            // $table->decimal('price', 12, 2);
            // $table->decimal('cost_price', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            $table->timestamps();

            $table->index('category_id');
            $table->index('brand_id');
            $table->index('is_active');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
