<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_facture')->unique();         // ex: FAC-2024-0001
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('user_id')->constrained('users'); // vendeur
            $table->enum('statut', [
                'brouillon',    // en cours de saisie
                'confirmee',    // stock débité
                'livree',       // matériel remis au client
                'facturee',     // facture émise
                'payee',        // paiement reçu
                'annulee',      // annulée (stock restitué)
            ])->default('brouillon');
            $table->enum('mode_paiement', [
                'especes', 'virement', 'cheque', 'mobile_money', 'credit', 'autre',
            ])->nullable();
            $table->date('date_vente');
            $table->date('date_livraison_prevue')->nullable();
            $table->date('date_livraison_reelle')->nullable();
            $table->date('date_echeance')->nullable();          // pour ventes à crédit
            $table->decimal('remise_globale', 5, 2)->default(0); // % remise
            $table->decimal('tva', 5, 2)->default(19.25);       // % TVA Cameroun
            $table->decimal('sous_total_ht', 15, 2)->default(0);
            $table->decimal('montant_remise', 15, 2)->default(0);
            $table->decimal('montant_tva', 15, 2)->default(0);
            $table->decimal('total_ttc', 15, 2)->default(0);
            $table->decimal('montant_paye', 15, 2)->default(0);
            $table->text('conditions')->nullable();              // conditions de vente
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventes');
    }
};
