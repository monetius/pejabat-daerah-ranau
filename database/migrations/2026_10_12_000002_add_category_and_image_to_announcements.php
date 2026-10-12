<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('category', 20)->default('hebahan')->after('slug'); // hebahan | berita | notis
            $table->string('image', 500)->nullable()->after('summary');
            $table->index(['status', 'category', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex(['status', 'category', 'published_at']);
            $table->dropColumn(['category', 'image']);
        });
    }
};
