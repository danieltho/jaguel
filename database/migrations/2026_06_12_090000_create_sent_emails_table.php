<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sent_emails', function (Blueprint $table) {
            $table->id();
            $table->string('to_address')->index();
            $table->string('subject')->nullable();
            $table->string('mailable')->nullable();
            $table->string('mailer')->default('default');
            $table->string('message_id')->nullable()->index();
            $table->string('status')->default('sending')->index();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sent_emails');
    }
};
