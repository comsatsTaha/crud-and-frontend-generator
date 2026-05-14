<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('entity_fields', function (Blueprint $table) {
            $table->string('display_field')->nullable()->after('foreign_key');
            $table->string('pivot_table')->nullable()->after('display_field');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entity_fields', function (Blueprint $table) {
            $table->dropColumn(['display_field', 'pivot_table']);
        });
    }
};
