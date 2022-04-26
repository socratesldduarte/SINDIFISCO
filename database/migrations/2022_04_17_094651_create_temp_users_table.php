<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->boolean('is_processed')->default(false);
            $table->dateTime('processed_at')->nullable();

            $table->string('document', 11)->unique();
            $table->string('name', 100);
            $table->date('birthday')->nullable();
            $table->string('code_area', 10)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('phone2', 40)->nullable();
            $table->string('phone2_desc', 40)->nullable();
            $table->string('phone3', 40)->nullable();
            $table->string('address_type', 20)->nullable();
            $table->string('address', 100)->nullable();
            $table->string('address_number', 20)->nullable();
            $table->string('address_line2', 100)->nullable();
            $table->string('pobox', 50)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('zipcode', 20)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('email2', 100)->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('situation', 100)->nullable();
            $table->string('password_plain', 100)->nullable();
            $table->string('password_bcrypt', 100)->nullable();

            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('temp_users');
    }
}
