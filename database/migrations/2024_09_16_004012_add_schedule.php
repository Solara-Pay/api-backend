<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_group_id'); 
            $table->foreign('payroll_group_id')
                ->references('id')
            ->on('payroll_groups')
            ->onDelete('cascade');
                        $table->unsignedBigInteger('user_id'); 
            $table->foreign('user_id')
                ->references('id')
            ->on('users')
            ->onDelete('cascade');
            $table->string('name');
            $table->string('wallet_address');
            $table->string('amount');
            $table->string('sol');
            $table->string('schedule');
            $table->boolean('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_recipients');
    }
};
