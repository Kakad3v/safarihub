<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 12)->default('trip');
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 30)->index();
            $table->string('place', 80)->index();
            $table->string('county', 60)->nullable();
            $table->text('summary')->nullable();
            $table->text('description');
            $table->unsignedTinyInteger('featured_position')->nullable();
            $table->unsignedTinyInteger('duration_days')->default(1);
            $table->unsignedTinyInteger('nights')->default(0);
            $table->unsignedTinyInteger('max_group')->default(6);
            $table->unsignedInteger('price');
            $table->unsignedTinyInteger('deposit_percent')->default(30);
            $table->string('pickup_location')->nullable();
            $table->string('pickup_time', 20)->nullable();
            $table->text('meeting_details')->nullable();
            $table->json('languages')->nullable();
            $table->json('includes')->nullable();
            $table->json('excludes')->nullable();
            $table->json('itinerary')->nullable();
            $table->boolean('featured')->default(false)->index();
            $table->string('status', 12)->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('package_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt')->nullable();
            $table->string('focus', 20)->default('50% 50%');
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('departures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->date('starts_on')->index();
            $table->date('ends_on');
            $table->unsignedSmallInteger('seats');
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 12)->unique();
            $table->foreignId('package_id')->constrained();
            $table->foreignId('departure_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->unsignedTinyInteger('travelers');
            $table->unsignedInteger('total');
            $table->unsignedInteger('deposit');
            $table->unsignedInteger('balance');
            $table->date('balance_due_on');
            $table->string('status', 20)->default('pending')->index();
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('body');
            $table->date('travelled_on')->nullable();
            $table->timestamps();
            $table->unique(['package_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('departures');
        Schema::dropIfExists('package_photos');
        Schema::dropIfExists('packages');
    }
};
