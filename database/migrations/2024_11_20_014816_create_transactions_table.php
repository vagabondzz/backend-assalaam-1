<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('trans_no', 100)->unique();
            $table->string('member_card_no', 100);
            $table->dateTime('trans_date');
            $table->decimal('trans_total_transaction', 10, 2);
            $table->string('trans_poin_pas', 100);
            $table->foreignId('card_member_id')->constrained('card_members')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
