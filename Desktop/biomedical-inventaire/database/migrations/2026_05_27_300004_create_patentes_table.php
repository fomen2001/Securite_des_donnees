<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patentes', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('annee');
            $table->date('date_echeance');               // 31 mars de l'année

            // Base = CA de l'année précédente
            $table->decimal('chiffre_affaires_reference', 15, 2)->default(0);

            // Calcul patente (CGI Cameroun)
            $table->decimal('droit_fixe', 15, 2)->default(0);
            $table->decimal('droit_variable', 15, 2)->default(0);        // 0.159% du CA
            $table->decimal('centimes_additionnels', 15, 2)->default(0); // 10% de (fixe + variable)
            $table->decimal('montant_total', 15, 2)->default(0);

            $table->enum('statut', ['brouillon', 'soumise', 'payee', 'en_retard'])->default('brouillon');
            $table->date('date_paiement')->nullable();
            $table->string('reference_paiement', 100)->nullable();
            $table->string('numero_quittance', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patentes');
    }
};
