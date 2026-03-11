<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency_groups', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_group_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('acronym');
            $table->string('icon');
            $table->string('url')->default('#');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agencies');
        Schema::dropIfExists('agency_groups');
    }
};
