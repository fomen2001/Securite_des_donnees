<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipements', function (Blueprint $table) {
            $table->id();
            $table->string('code_inventaire')->unique();   // ex: BIO-2024-001
            $table->string('designation');                  // nom de l'équipement
            $table->string('marque')->nullable();
            $table->string('modele')->nullable();
            $table->string('numero_serie')->nullable()->unique();
            $table->foreignId('categorie_id')->constrained('categories');
            $table->foreignId('fournisseur_id')->nullable()->constrained('fournisseurs');
            $table->foreignId('service_id')->nullable()->constrained('services');
            $table->date('date_acquisition')->nullable();
            $table->date('date_mise_en_service')->nullable();
            $table->date('date_fin_garantie')->nullable();
            $table->decimal('prix_achat', 15, 2)->nullable();
            $table->integer('quantite')->default(1);
            $table->integer('quantite_min')->default(1);    // seuil d'alerte
            $table->enum('etat', [
                'operationnel',
                'en_maintenance',
                'hors_service',
                'en_attente',
                'reformé',
            ])->default('operationnel');
            $table->enum('classe_risque', ['I', 'IIa', 'IIb', 'III'])->nullable(); // classification DM
            $table->string('numero_lot')->nullable();
            $table->date('date_expiration')->nullable();    // pour consommables
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('periodicite_maintenance')->nullable(); // en jours
            $table->date('prochaine_maintenance')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipements');
    }
};
