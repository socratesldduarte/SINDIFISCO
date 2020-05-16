<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserVoteDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_vote_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('vote');
            $table->unsignedBigInteger('poll_id');
            $table->string('question', 4);
            $table->unsignedBigInteger('poll_question_option_id');

//            $table->foreign('voto')->references('voto')->on('user_votes');
            $table->foreign('poll_id')->references('id')->on('polls');
            $table->foreign('poll_question_option_id')->references('id')->on('poll_question_options');
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
        Schema::dropIfExists('user_vote_details');
    }
}
