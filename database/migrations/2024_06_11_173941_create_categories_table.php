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
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->nullable()->references('id')->on('categories');

            $table->enum('type', [
                'product', 'service', 'post', 'page', 'other'
            ])->default('product');
            $table->string('type_other')->nullable();

            $table->string('name');
            $table->string('slug')->nullable();

            $table->tinyInteger('order')->unsigned()->default(0);
            // ORDER INDICATE DEPTH
            // ..0          -> parent
            // ..0..1       -> parent|child
            // ..0..1..2    -> child|child

            $table->json('data')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
