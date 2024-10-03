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
        Schema::create('user_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');  // User relation
            $table->string('card_id');         // Tokenized card ID
            $table->string('number');            // Last 4 digits of card number
            $table->string('exp_month');
            $table->string('exp_year');
            $table->string('cvc');
            $table->string('card_brand');           // Card brand (e.g., Visa, Mastercard)
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cards');
    }
};
