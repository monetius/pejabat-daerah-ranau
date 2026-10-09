<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->string('title', 220);
            $table->longText('body');
            $table->longText('scripts')->nullable();
            $table->foreignId('saved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['page_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_revisions');
    }
};
