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
        Schema::create('customer_stage_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_stage_id')->constrained('customer_stages')->onDelete('cascade');
            $table->unsignedSmallInteger('document_type_id');
            $table->enum('qr_position', ['above_left', 'above_right', 'below_left', 'below_right']);
            $table->timestamps();

            $table->foreign('document_type_id')->references('id')->on('document_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_stage_documents');
    }
};
