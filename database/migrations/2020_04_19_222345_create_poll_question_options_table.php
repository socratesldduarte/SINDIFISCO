<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePollQuestionOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poll_question_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('poll_question_id');

            $table->smallInteger('order');
            $table->string('option', 100);
            $table->text('description');

            $table->foreign('poll_question_id')->references('id')->on('poll_questions');
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
        Schema::dropIfExists('poll_options');
    }
}
