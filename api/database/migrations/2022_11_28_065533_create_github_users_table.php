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
        Schema::create('github_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('role');
            $table->biginteger('github_id');
            $table->string('avatar_url')->nullable();
            $table->string('url')->nullable();
            $table->string('html_url')->nullable();
            $table->string('repos_url')->nullable();
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->string('company')->nullable();
            $table->string('blog')->nullable();
            $table->string('location')->nullable();
            $table->string('email')->nullable();
            $table->string('hireable')->nullable();
            $table->integer('public_repos')->nullable();
            $table->integer('public_gists')->nullable();
            $table->integer('followers')->nullable();
            $table->integer('following')->nullable();
            $table->timestamp('github_created_at');
            $table->timestamp('github_updated_at');
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
        Schema::dropIfExists('github_users');
    }
};
