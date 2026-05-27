<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipement_id')->constrained('equipements');
            $table->enum('type', ['preventive', 'corrective', 'calibration', 'verification']);
            $table->enum('statut', ['planifiee', 'en_cours', 'terminee', 'annulee'])->default('planifiee');
            $table->date('date_planifiee');
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('technicien')->nullable();
            $table->foreignId('fournisseur_id')->nullable()->constrained('fournisseurs'); // prestataire externe
            $table->text('description_travaux')->nullable();
            $table->text('observations')->nullable();
            $table->decimal('cout', 15, 2)->nullable();
            $table->string('rapport_path')->nullable(); // chemin vers le rapport PDF
            $table->boolean('equipement_operationnel')->default(true);
            $table->date('prochaine_maintenance')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
