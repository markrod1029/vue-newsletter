<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->string('approvable_type');
            $table->unsignedBigInteger('approvable_id');
            $table->foreignId('decided_by')->constrained('users')->onDelete('cascade');
            $table->enum('decision', ['approved', 'rejected']);
            $table->text('reason')->nullable();
            $table->timestamp('decided_at');
            $table->timestamps();

            $table->index(['approvable_type', 'approvable_id']);
            $table->index('decided_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('approvals');
    }
};
