<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('comment_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            // Prevent duplicate likes for posts
            $table->unique(['user_id', 'post_id']);

            // Prevent duplicate likes for comments
            $table->unique(['user_id', 'comment_id']);

            $table->index(['post_id', 'created_at']);
            $table->index(['comment_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('likes');
    }
};
