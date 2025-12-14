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
        Schema::create('approval_statuses', function (Blueprint $table) {
            $table->id();

            $table->string('part_number');

            $table->string('created_by_id')->nullable();
            $table->string('created_by_name')->nullable();
            $table->date('created_date')->nullable();

            $table->string('checked_by_id')->nullable();
            $table->string('checked_by_name')->nullable();
            $table->date('checked_date')->nullable();

            $table->string('approved_by_id')->nullable();
            $table->string('approved_by_name')->nullable();
            $table->date('approved_date')->nullable();

            $table->string('management_approved_by_id')->nullable();
            $table->string('management_approved_by_name')->nullable();
            $table->date('management_approved_date')->nullable();

            $table->timestamps();

            $table->foreign('part_number')
                ->references('part_number')
                ->on('projects')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_histories');
    }
};
