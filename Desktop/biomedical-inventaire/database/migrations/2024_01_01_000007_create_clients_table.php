<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('code_client')->unique();          // ex: CLI-0001
            $table->string('nom');
            $table->enum('type', ['hopital', 'clinique', 'cabinet', 'laboratoire', 'particulier', 'autre'])->default('hopital');
            $table->string('contact_nom')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->text('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('pays')->default('Cameroun');
            $table->string('numero_contribuable')->nullable();  // N° fiscal
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
