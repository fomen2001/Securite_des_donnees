<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bons_livraison', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();              // BL-2025-0001
            $table->foreignId('vente_id')->nullable()->constrained('ventes')->nullOnDelete();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('date_livraison');
            $table->enum('statut', ['prepare', 'expedie', 'livre', 'retourne', 'annule'])->default('prepare');
            $table->string('adresse_livraison')->nullable();
            $table->string('transporteur')->nullable();
            $table->string('contact_reception')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bon_livraison_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bon_livraison_id')->constrained('bons_livraison')->cascadeOnDelete();
            $table->foreignId('equipement_id')->nullable()->constrained('equipements')->nullOnDelete();
            $table->string('designation');
            $table->string('reference')->nullable();
            $table->decimal('quantite', 10, 2);
            $table->string('unite')->default('unité');
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bon_livraison_lignes');
        Schema::dropIfExists('bons_livraison');
    }
};
