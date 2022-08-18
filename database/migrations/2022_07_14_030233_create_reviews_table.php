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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')
                ->references('id')
                ->on('students')
                ->unique()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->date('date_request')->default(now());
            $table->date('date_review');
            $table->integer('calification')->default(0);
            $table->boolean('status')->default(false);
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
        Schema::dropIfExists('reviews');
    }
};
