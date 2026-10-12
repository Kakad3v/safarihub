<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('status');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 20)->unique();
            $table->string('gateway', 20)->default('paystack');
            $table->string('purpose', 12);
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('KES');
            $table->string('channel', 30)->nullable();
            $table->string('status', 12)->default('pending')->index();
            $table->string('gateway_id', 40)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }
};
