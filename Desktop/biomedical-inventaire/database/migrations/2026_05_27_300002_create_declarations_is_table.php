<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('declarations_is', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['acompte', 'annuelle']);
            $table->unsignedSmallInteger('annee');
            $table->unsignedTinyInteger('trimestre')->nullable(); // 1-4 pour acomptes

            $table->date('date_echeance');

            // Base de calcul
            $table->decimal('chiffre_affaires', 15, 2)->default(0);
            $table->decimal('benefice_imposable', 15, 2)->default(0);

            // Calcul IS (CGI Cameroun art. 25)
            $table->decimal('is_brut', 15, 2)->default(0);        // 30% du bénéfice
            $table->decimal('minimum_is', 15, 2)->default(0);     // 1% CA, min 500 000 FCFA
            $table->decimal('is_du', 15, 2)->default(0);          // max(is_brut, minimum_is)

            // Pour acompte : fraction de l'IS annuel précédent
            $table->decimal('base_acompte', 15, 2)->default(0);   // IS N-1
            $table->decimal('montant_acompte', 15, 2)->default(0); // IS N-1 / 4

            // Pour déclaration annuelle
            $table->decimal('acomptes_verses', 15, 2)->default(0);
            $table->decimal('montant_a_payer', 15, 2)->default(0); // is_du - acomptes_verses

            $table->enum('statut', ['brouillon', 'soumise', 'payee', 'en_retard'])->default('brouillon');
            $table->date('date_paiement')->nullable();
            $table->string('reference_paiement', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('document_path', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('declarations_is');
    }
};
