<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operator_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('operator_profiles', 'slug')) {
                $table->string('slug')->nullable()->unique();
            }
            if (! Schema::hasColumn('operator_profiles', 'about')) {
                $table->text('about')->nullable();
            }
            if (! Schema::hasColumn('operator_profiles', 'location')) {
                $table->string('location')->nullable();
            }
            if (! Schema::hasColumn('operator_profiles', 'logo_path')) {
                $table->string('logo_path')->nullable();
            }
            if (! Schema::hasColumn('operator_profiles', 'cover_path')) {
                $table->string('cover_path')->nullable();
            }
            if (! Schema::hasColumn('operator_profiles', 'founded_year')) {
                $table->unsignedSmallInteger('founded_year')->nullable();
            }
            if (! Schema::hasColumn('operator_profiles', 'status')) {
                $table->string('status', 20)->default('pending')->index();
            }
        });

        $nameColumn = collect(['business_name', 'name', 'company_name'])
            ->first(fn($column) => Schema::hasColumn('operator_profiles', $column));

        if (! $nameColumn) {
            return;
        }

        DB::table('operator_profiles')->whereNull('slug')->get()->each(function ($row) use ($nameColumn) {
            $base = Str::slug($row->{$nameColumn}) ?: 'operator';
            $slug = $base;
            $i = 2;

            while (DB::table('operator_profiles')->where('slug', $slug)->exists()) {
                $slug = $base . '-' . $i++;
            }

            DB::table('operator_profiles')->where('id', $row->id)->update(['slug' => $slug]);
        });
    }

    public function down(): void
    {
        Schema::table('operator_profiles', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
