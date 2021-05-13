<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('poll_id')->nullable();

            $table->string('document', 11);
            $table->boolean('able');
            $table->string('name', 100);
            $table->date('birthday');
            $table->string('email', 100);
            $table->string('mobile', 40);
            $table->boolean('administrator')->default(false);
            $table->boolean('committee')->default(false);
            $table->boolean('can_be_voted')->default(true);
            $table->string('password');
            $table->timestamp('enabled_until')->nullable();

            $table->unique(['poll_id', 'document']);
            $table->foreign('poll_id')->references('id')->on('polls');
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
        Schema::dropIfExists('users');
    }
}
