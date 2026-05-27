<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lignes_vente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vente_id')->constrained('ventes')->cascadeOnDelete();
            $table->foreignId('equipement_id')->constrained('equipements');
            $table->string('designation_snapshot');    // copie du nom au moment de la vente
            $table->string('reference_snapshot')->nullable(); // copie du code inventaire
            $table->integer('quantite');
            $table->decimal('prix_unitaire_ht', 15, 2);
            $table->decimal('remise', 5, 2)->default(0); // % remise sur la ligne
            $table->decimal('total_ht', 15, 2);           // calculé : qte * pu * (1 - remise/100)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_vente');
    }
};
