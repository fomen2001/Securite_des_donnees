<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulletins_paie', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->foreignId('employe_id')->constrained('employes');
            $table->unsignedTinyInteger('mois');   // 1–12
            $table->unsignedSmallInteger('annee');
            $table->date('periode_debut');
            $table->date('periode_fin');
            $table->unsignedTinyInteger('jours_travailles')->default(26);
            $table->decimal('heures_supplementaires', 8, 2)->default(0);

            // Gains
            $table->decimal('salaire_base', 12, 2);
            $table->decimal('total_primes', 12, 2)->default(0);
            $table->decimal('total_indemnites', 12, 2)->default(0);
            $table->decimal('avantages_nature', 12, 2)->default(0);
            $table->decimal('salaire_brut', 12, 2);

            // Retenues légales (loi camerounaise)
            $table->decimal('cotisation_cnps_salarie', 12, 2)->default(0);   // 4.2%
            $table->decimal('cotisation_cnps_employeur', 12, 2)->default(0); // ~13%
            $table->decimal('irpp', 12, 2)->default(0);                      // IRPP
            $table->decimal('cac', 12, 2)->default(0);                       // 10% IRPP
            $table->decimal('rav', 12, 2)->default(2500);                    // Redevance audiovisuelle

            // Déductions diverses
            $table->decimal('avances_deduites', 12, 2)->default(0);

            $table->decimal('total_retenues', 12, 2);
            $table->decimal('net_a_payer', 12, 2);

            $table->enum('statut', ['brouillon', 'valide', 'paye'])->default('brouillon');
            $table->enum('mode_paiement', ['virement', 'especes', 'cheque', 'mobile_money'])->nullable();
            $table->date('date_paiement')->nullable();

            $table->json('details_primes')->nullable();       // [{type, montant, imposable}]
            $table->json('details_indemnites')->nullable();   // [{type, montant}]

            $table->text('observations')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employe_id', 'mois', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletins_paie');
    }
};
