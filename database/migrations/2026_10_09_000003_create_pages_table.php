<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 220);
            $table->string('slug', 220)->unique();
            $table->string('meta_description', 320)->nullable();
            $table->longText('body');
            $table->string('status', 20)->default('draft'); // draft | published
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
