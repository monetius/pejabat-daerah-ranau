<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_parts', function (Blueprint $table) {
            $table->string('key', 50)->primary();
            $table->string('label', 100);
            $table->longText('html');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_parts');
    }
};
