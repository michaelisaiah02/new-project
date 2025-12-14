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
            $table->string('project_part_number');

            // FK ke document_types (PK = code)
            $table->string('document_type_code');

            // stage dari customer_stages
            $table->foreignId('customer_stage_id')
                ->constrained('customer_stages')
                ->onDelete('cascade');

            $table->date('due_date')->nullable();
            $table->date('actual_date')->nullable();
            $table->string('file_name')->nullable();

            $table->boolean('checked')->default(false);
            $table->boolean('approved')->default(false);

            $table->string('remark')->nullable();

            $table->timestamps();

            $table->foreign('project_part_number')
                ->references('part_number')
                ->on('projects')
                ->onDelete('cascade');

            $table->foreign('document_type_code')
                ->references('code')
                ->on('document_types')
                ->onDelete('cascade');
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
