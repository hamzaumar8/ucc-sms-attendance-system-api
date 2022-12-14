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
        Schema::create('lecturers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('title', ['Prof.', 'Dr.', 'Rev.', 'Mr.', 'Mrs.', 'Miss', 'Ms.']);
            $table->string('first_name', 20);
            $table->string('other_name')->nullable();
            $table->string('last_name', 20);
            $table->enum('gender', ['male', 'female']);
            $table->string('phone1', 15)->nullable();
            $table->text('picture')->nullable();
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
        Schema::dropIfExists('lecturers');
    }
};