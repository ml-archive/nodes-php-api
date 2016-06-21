<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUserVerification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_verifications', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('email', 255)->index();
            $table->string('token', 64)->index();
            $table->boolean('used')->unsigned()->default(false);
            $table->timestamp('expire_at')->nullable()->default(null);
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
        Schema::drop('user_verifications');
    }
}
