<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_details', function (Blueprint $table) {
            $table->id();
            $table->string('project_part_number');
            $table->unsignedSmallInteger('document_type_id');
            $table->date('assigned_date');
            $table->date('actual_date');
            $table->boolean('checked')->default(false);
            $table->boolean('approved')->default(false);
            $table->timestamps();

            $table->foreign('project_part_number')->references('part_number')->on('projects')->onDelete('cascade');
            $table->foreign('document_type_id')->references('id')->on('document_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_details');
    }
};
