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
        Schema::create('contributors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('repo_id');
            $table->biginteger('github_id');
            $table->string('login')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('url')->nullable();
            $table->string('html_url')->nullable();
            $table->string('repos_url')->nullable();
            $table->string('type')->nullable();
            $table->boolean('site_admin')->nullable();
            $table->integer('contributions')->nullable();
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
        Schema::dropIfExists('contributors');
    }
};
