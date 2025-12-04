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
        Schema::create('category_product', function (Blueprint $table) {
            // foreignId for category, constrained to the 'categories' table
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            // foreignId for product, constrained to the 'products' table
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            // Set a primary key to prevent duplicate entries
            $table->primary(['category_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_product');
    }
};
