<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->string('commentable_type');
            $table->unsignedBigInteger('commentable_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('body');
            $table->enum('status', ['visible', 'hidden', 'flagged'])->default('visible');
            $table->timestamps();

            $table->index(['commentable_type', 'commentable_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('comments');
    }
};
