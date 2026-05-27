<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->foreignId('categorie_depense_id')->constrained('categories_depenses');
            $table->string('libelle', 255);
            $table->decimal('montant_ht', 14, 2);
            $table->decimal('tva', 5, 2)->default(0);
            $table->decimal('montant_ttc', 14, 2);
            $table->date('date_depense');
            $table->enum('mode_paiement', ['especes', 'virement', 'cheque', 'mobile_money', 'carte', 'autre'])->default('especes');
            $table->string('beneficiaire', 200)->nullable();
            $table->foreignId('fournisseur_id')->nullable()->constrained('fournisseurs')->nullOnDelete();
            $table->string('numero_piece', 100)->nullable();
            $table->enum('statut', ['en_attente', 'approuvee', 'rejetee', 'payee'])->default('en_attente');
            $table->foreignId('approuve_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('date_approbation')->nullable();
            $table->text('notes')->nullable();
            $table->string('document_path', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};
