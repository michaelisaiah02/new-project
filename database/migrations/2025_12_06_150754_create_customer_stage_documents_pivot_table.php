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
            $table->string('document_type_code');
            $table->enum('qr_position', ['top_left', 'top_right', 'bottom_left', 'bottom_right']);
            $table->timestamps();

            $table->foreign('document_type_code')->references('code')->on('document_types')->onDelete('cascade');
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
