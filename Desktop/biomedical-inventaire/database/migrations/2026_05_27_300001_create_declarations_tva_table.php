<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('declarations_tva', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('periode_mois');  // 1-12
            $table->unsignedSmallInteger('periode_annee');
            $table->date('date_echeance');                // 15 du mois suivant

            // TVA collectée (sur ventes facturées/payées)
            $table->decimal('ventes_ht', 15, 2)->default(0);
            $table->decimal('tva_collectee', 15, 2)->default(0);

            // TVA déductible (sur achats/dépenses approuvés)
            $table->decimal('achats_ht', 15, 2)->default(0);
            $table->decimal('tva_deductible', 15, 2)->default(0);

            // Crédit de TVA reporté de la période précédente
            $table->decimal('credit_anterieur', 15, 2)->default(0);

            // Résultat
            $table->decimal('tva_nette', 15, 2)->default(0);      // collectée - déductible - crédit
            $table->decimal('credit_nouveau', 15, 2)->default(0);  // si nette < 0
            $table->decimal('montant_a_payer', 15, 2)->default(0); // si nette > 0

            $table->enum('statut', ['brouillon', 'soumise', 'payee', 'en_retard'])->default('brouillon');
            $table->date('date_paiement')->nullable();
            $table->string('reference_paiement', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('document_path', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['periode_mois', 'periode_annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('declarations_tva');
    }
};
