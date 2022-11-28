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
        Schema::create('repos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('github_user_id');
            $table->biginteger('github_id');
            $table->string('name')->nullable();
            $table->string('full_name')->nullable();
            $table->boolean('private')->nullable();
            $table->string('url')->nullable();
            $table->string('html_url')->nullable();
            $table->string('description')->nullable();
            $table->boolean('fork')->nullable();
            $table->string('homepage')->nullable();
            $table->integer('size')->nullable();
            $table->integer('stargazers_count')->nullable();
            $table->integer('watchers_count')->nullable();
            $table->integer('forks')->nullable();
            $table->integer('forks_count')->nullable();
            $table->integer('open_issues')->nullable();
            $table->integer('open_issues_count')->nullable();
            $table->integer('watchers')->nullable();
            $table->string('language')->nullable();
            $table->boolean('has_issues')->nullable();
            $table->boolean('has_projects')->nullable();
            $table->boolean('has_downloads')->nullable();
            $table->boolean('has_wiki')->nullable();
            $table->boolean('has_pages')->nullable();
            $table->boolean('has_discussions')->nullable();
            $table->boolean('is_template')->nullable();
            $table->string('mirror_url')->nullable();
            $table->boolean('archived')->nullable();
            $table->boolean('disabled')->nullable();
            $table->boolean('allow_forking')->nullable();
            $table->string('visibility')->nullable();
            $table->string('default_branch')->nullable();
            $table->timestamp('github_created_at');
            $table->timestamp('github_updated_at');
            $table->timestamp('github_pushed_at');
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
        Schema::dropIfExists('repos');
    }
};
