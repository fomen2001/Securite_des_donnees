<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bons_commande', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();              // BC-2025-0001
            $table->foreignId('fournisseur_id')->constrained('fournisseurs')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('date_commande');
            $table->date('date_livraison_souhaitee')->nullable();
            $table->enum('statut', ['brouillon', 'confirmee', 'partiellement_recue', 'recue', 'annulee'])->default('brouillon');
            $table->decimal('montant_ht', 15, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(19.25);
            $table->decimal('montant_tva', 15, 2)->default(0);
            $table->decimal('montant_ttc', 15, 2)->default(0);
            $table->text('conditions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bon_commande_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_commande_id')->constrained('bons_commande')->cascadeOnDelete();
            $table->foreignId('equipement_id')->nullable()->constrained('equipements')->nullOnDelete();
            $table->string('designation');
            $table->string('reference_fournisseur')->nullable();
            $table->decimal('quantite_commandee', 10, 2);
            $table->decimal('quantite_recue', 10, 2)->default(0);
            $table->string('unite')->default('unité');
            $table->decimal('prix_unitaire_ht', 15, 2);
            $table->decimal('taux_tva', 5, 2)->default(19.25);
            $table->decimal('total_ht', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bon_commande_lignes');
        Schema::dropIfExists('bons_commande');
    }
};
