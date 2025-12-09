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
        Schema::create('new_projects', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code');
            $table->string('model');
            $table->string('part_number');
            $table->string('part_name');
            $table->string('part_type');
            $table->string('drawing_2d')->comment('2D Drawing File Name');
            $table->string('drawing_3d')->comment('3D Drawing File Name');
            $table->integer('qty')->comment('Qty per Year (pcs)');
            $table->string('eee_number')->comment('ECI/EO/ECN Number');
            $table->string('drawing_number');
            $table->date('drawing_revision_date');
            $table->string('material_on_drawing');
            $table->date('receive_date_sldg')->comment('Receive Date SPK/LOI/DIE GO');
            $table->string('spk_number')->comment('SPK/LOI/DIE GO Number');
            $table->string('masspro_target');
            $table->string('message')->comment('Message from Management');
            $table->string('minor_change');
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
