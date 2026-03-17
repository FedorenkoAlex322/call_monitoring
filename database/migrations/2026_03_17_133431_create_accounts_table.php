<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tariff_id')->constrained();
            $table->string('number', 20)->unique();
            $table->string('name');
            $table->decimal('balance', 12, 2)->default(0);
            $table->enum('status', ['active', 'blocked', 'suspended'])->default('active');
            $table->timestamps();
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
