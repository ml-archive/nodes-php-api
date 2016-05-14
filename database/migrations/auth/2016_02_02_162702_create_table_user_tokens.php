<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateTableAccessTokens
 */
class CreateTableUserTokens extends Migration
{
    /**
     * Run the migrations
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_tokens', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('token', 60);
            $table->dateTime('expire')->nullable()->default(null);
            $table->timestamps();

            // Update "token" to be binary, so it's case sensitive
            DB::statement('ALTER TABLE `user_tokens` CHANGE `token` `token` VARCHAR(60) BINARY NOT NULL');
        });
    }

    /**
     * Reverse the migrations
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_tokens');
    }
}