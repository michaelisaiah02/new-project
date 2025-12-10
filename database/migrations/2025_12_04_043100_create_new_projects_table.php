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
        Schema::create('projects', function (Blueprint $table) {
            $table->string('part_number')->primary();
            $table->string('part_name');
            $table->string('part_type');
            $table->string('customer_code');
            $table->string('model');
            $table->string('drawing_2d')->comment('2D Drawing File Name');
            $table->string('drawing_3d')->nullable()->comment('3D Drawing File Name');
            $table->integer('qty')->comment('Qty per Year (pcs)');
            $table->string('eee_number')->comment('ECI/EO/ECN Number');
            $table->string('drawing_number');
            $table->date('drawing_revision_date');
            $table->string('material_on_drawing');
            $table->date('receive_date_sldg')->comment('Receive Date SPK/LOI/DIE GO');
            $table->string('sldg_number')->comment('SPK/LOI/DIE GO Number');
            $table->string('masspro_target');
            $table->string('minor_change');
            $table->enum('remark', ['new', 'not checked', 'not approved', 'on going', 'completed'])->default('new');
            $table->boolean('checked')->default(false);
            $table->boolean('approved')->default(false);
            $table->timestamps();

            $table->foreign('customer_code')->references('code')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_projects');
    }
};
