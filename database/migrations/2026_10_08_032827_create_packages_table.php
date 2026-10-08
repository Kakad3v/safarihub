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
            $table->foreignId('destination_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('type', 20)->default('trip')->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('summary', 160)->nullable();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('duration_days')->nullable();
            $table->decimal('duration_hours', 4, 1)->nullable();
            $table->time('start_time')->nullable();
            $table->string('meeting_point')->nullable();
            $table->string('bring')->nullable();
            $table->json('weekdays')->nullable();
            $table->unsignedInteger('price');
            $table->unsignedTinyInteger('child_discount_percent')->default(0);
            $table->unsignedSmallInteger('max_group_size')->nullable();
            $table->string('cancellation_policy', 20)->default('flexible');
            $table->string('video_url')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->decimal('rating_avg', 2, 1)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'price']);
            $table->index(['destination_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
