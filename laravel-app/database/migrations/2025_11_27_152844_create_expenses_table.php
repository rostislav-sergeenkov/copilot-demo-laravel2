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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('description', 255);
            $table->decimal('amount', 10, 2);
            $table->string('category', 50);
            $table->date('date');
            $table->timestamps();
            $table->softDeletes();

            // Add indexes for better query performance
            $table->index('date');
            $table->index('category');
            $table->index(['date', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
