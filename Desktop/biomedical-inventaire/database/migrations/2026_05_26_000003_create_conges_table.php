<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes');
            $table->enum('type_conge', [
                'annuel', 'maladie', 'maternite', 'paternite',
                'sans_solde', 'deuil', 'autre',
            ])->default('annuel');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->unsignedSmallInteger('nombre_jours');
            $table->text('motif')->nullable();
            $table->enum('statut', ['en_attente', 'approuve', 'refuse', 'annule'])->default('en_attente');
            $table->foreignId('approuve_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('date_approbation')->nullable();
            $table->unsignedSmallInteger('solde_avant')->default(0);
            $table->unsignedSmallInteger('solde_apres')->default(0);
            $table->string('document_path', 255)->nullable();
            $table->text('motif_refus')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conges');
    }
};
