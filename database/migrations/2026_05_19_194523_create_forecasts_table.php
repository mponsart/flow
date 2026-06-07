<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->date('month'); // Premier jour du mois
            $table->decimal('projected_revenue', 10, 2)->default(0);
            $table->decimal('projected_expenses', 10, 2)->default(0);
            $table->decimal('projected_profit', 10, 2)->default(0);
            $table->decimal('actual_revenue', 10, 2)->nullable();
            $table->decimal('actual_expenses', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecasts');
    }
};
