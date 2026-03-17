<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cdrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->string('uniqueid', 64)->unique();
            $table->string('src', 20);
            $table->string('dst', 20);
            $table->timestamp('started_at');
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration')->default(0);
            $table->integer('billsec')->default(0);
            $table->decimal('cost', 10, 2)->default(0);
            $table->enum('disposition', ['ANSWERED', 'NO ANSWER', 'BUSY', 'FAILED'])->default('ANSWERED');
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->timestamps();
            $table->index(['account_id', 'started_at']);
            $table->index(['status']);
            $table->index(['started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cdrs');
    }
};
