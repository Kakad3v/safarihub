<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_departures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->date('starts_on');
            $table->unsignedSmallInteger('seats_total')->nullable();
            $table->unsignedSmallInteger('seats_booked')->default(0);
            $table->unsignedInteger('price_override')->nullable();
            $table->timestamps();
            $table->index(['package_id', 'starts_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_departures');
    }
};
