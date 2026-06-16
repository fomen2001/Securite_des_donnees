<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_pieces_jointes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_client_id')->constrained('messages_clients')->cascadeOnDelete();
            $table->string('nom_original');
            $table->string('chemin');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('taille')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_pieces_jointes');
    }
};
