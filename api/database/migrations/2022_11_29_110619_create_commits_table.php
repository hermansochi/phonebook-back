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
        Schema::create('commits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('repo_id');
            $table->string('sha');
            $table->string('author_id');
            $table->string('author_login');
            $table->string('author_name');
            $table->timestamp('author_date');
            $table->string('committer_id');
            $table->string('committer_login');
            $table->string('committer_name');
            $table->timestamp('committer_date');
            $table->string('message')->nullable();
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
        Schema::dropIfExists('commits');
    }
};
