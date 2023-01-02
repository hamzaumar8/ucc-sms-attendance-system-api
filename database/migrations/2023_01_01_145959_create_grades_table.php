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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assesment_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();

            $table->decimal('score', 8, 2)->default(0);
            $table->enum('remarks', ['honour', 'pass', 'fail', 'IC'])->default('IC');

            $table->foreign('assesment_id')->references('id')->on('assesments')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('set null');
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
        Schema::dropIfExists('grades');
    }
};