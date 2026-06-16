<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bons_reception', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();              // BR-2025-0001
            $table->foreignId('bon_commande_id')->constrained('bons_commande')->restrictOnDelete();
            $table->foreignId('fournisseur_id')->constrained('fournisseurs')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('date_reception');
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');
            $table->string('transporteur')->nullable();
            $table->string('numero_bl_fournisseur')->nullable();  // BL reçu du fournisseur
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bon_reception_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_reception_id')->constrained('bons_reception')->cascadeOnDelete();
            $table->foreignId('bon_commande_ligne_id')->constrained('bon_commande_lignes')->restrictOnDelete();
            $table->foreignId('equipement_id')->nullable()->constrained('equipements')->nullOnDelete();
            $table->string('designation');
            $table->decimal('quantite_recue', 10, 2);
            $table->decimal('quantite_conforme', 10, 2)->default(0);
            $table->decimal('quantite_rejetee', 10, 2)->default(0);
            $table->string('motif_rejet')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bon_reception_lignes');
        Schema::dropIfExists('bons_reception');
    }
};
