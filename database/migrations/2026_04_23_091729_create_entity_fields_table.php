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
        Schema::create('entity_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('column_name');
            $table->string('type');
            $table->text('validation_rules')->nullable();
            $table->boolean('is_nullable')->default(false);
            $table->string('default_value')->nullable();
            $table->integer('order')->default(0);
            
            // Relation specific fields
            $table->unsignedBigInteger('related_entity_id')->nullable();
            $table->string('relationship_type')->nullable(); // BelongsTo, HasMany
            $table->string('foreign_key')->nullable();
            $table->json('options')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_fields');
    }
};
