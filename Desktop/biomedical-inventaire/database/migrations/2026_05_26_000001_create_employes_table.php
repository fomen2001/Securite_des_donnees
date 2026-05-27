<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employes', function (Blueprint $table) {
            $table->id();
            $table->string('matricule', 20)->unique();
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance', 100)->nullable();
            $table->string('nationalite', 60)->default('Camerounaise');
            $table->enum('sexe', ['M', 'F'])->default('M');
            $table->enum('situation_matrimoniale', ['celibataire', 'marie', 'divorce', 'veuf'])->default('celibataire');
            $table->unsignedTinyInteger('nombre_enfants')->default(0);
            $table->string('telephone', 20)->nullable();
            $table->string('email', 150)->nullable()->unique();
            $table->text('adresse')->nullable();
            $table->string('ville', 100)->nullable()->default('Yaoundé');
            $table->string('photo', 255)->nullable();
            $table->string('numero_cni', 50)->nullable()->unique();
            $table->string('numero_cnps', 50)->nullable();
            $table->string('numero_contribuable', 50)->nullable();
            $table->date('date_embauche');
            $table->enum('type_contrat', ['CDI', 'CDD', 'stage', 'consultant'])->default('CDI');
            $table->date('date_fin_contrat')->nullable();
            $table->string('poste', 150);
            $table->string('departement', 100)->nullable();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('categorie_professionnelle', 20)->nullable();
            $table->decimal('salaire_base', 12, 2);
            $table->unsignedSmallInteger('solde_conge')->default(0);
            $table->enum('statut', ['actif', 'conge', 'suspendu', 'demissionne', 'licencie'])->default('actif');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employes');
    }
};
