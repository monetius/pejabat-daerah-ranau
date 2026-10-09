<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('path', 255)->nullable()->after('slug');
            $table->string('section', 80)->nullable()->after('path');
            $table->string('body_class', 120)->nullable()->after('meta_description');
            $table->text('styles')->nullable()->after('body_class');
            $table->longText('scripts')->nullable()->after('body');
            $table->boolean('is_system')->default(false)->after('status');
        });

        // Pages made with the first basic CMS get a path based on their slug.
        foreach (DB::table('pages')->whereNull('path')->get() as $row) {
            DB::table('pages')->where('id', $row->id)->update(['path' => 'halaman/' . $row->slug]);
        }

        Schema::table('pages', function (Blueprint $table) {
            $table->unique('path');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropUnique(['path']);
            $table->dropColumn(['path', 'section', 'body_class', 'styles', 'scripts', 'is_system']);
        });
    }
};
