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
        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();

            // FK ke projects (PK = part_number)
            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete()->cascadeOnUpdate();

            // FK ke document_types (PK = code)
            $table->string('document_type_code');

            // stage dari customer_stages
            $table->foreignId('customer_stage_id')
                ->constrained('customer_stages')
                ->onDelete('cascade');

            $table->date('due_date')->nullable();
            $table->date('actual_date')->nullable();
            $table->string('file_name')->nullable();

            $table->string('created_by_id')->nullable();
            $table->string('created_by_name')->nullable();
            $table->date('created_date')->nullable();

            $table->string('checked_by_id')->nullable();
            $table->string('checked_by_name')->nullable();
            $table->date('checked_date')->nullable();

            $table->string('approved_by_id')->nullable();
            $table->string('approved_by_name')->nullable();
            $table->date('approved_date')->nullable();

            $table->string('remark')->nullable();

            $table->timestamps();

            $table->foreign('document_type_code')
                ->references('code')
                ->on('document_types')
                ->cascadeOnDelete()->cascadeOnUpdate();
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
