<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('semester_id')->nullable();
            $table->unsignedBigInteger('module_bank_id')->nullable();
            $table->unsignedBigInteger('coordinator_id')->nullable();
            $table->unsignedBigInteger('course_rep_id')->nullable();
            $table->unsignedBigInteger('level_id')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'upcoming', 'inactive'])->default('upcoming');
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('set null');
            $table->foreign('module_bank_id')->references('id')->on('module_banks')->onDelete('set null');
            $table->foreign('coordinator_id')->references('id')->on('lecturers')->onDelete('set null');
            $table->foreign('course_rep_id')->references('id')->on('students')->onDelete('set null');
            $table->foreign('level_id')->references('id')->on('levels')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modules');
    }
};
