<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bilans_comptables', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('exercice');   // année fiscale (ex: 2025)

            // ── ACTIF (SYSCOHADA) ────────────────────────────────
            // Actif immobilisé
            $table->decimal('immob_incorporelles', 15, 2)->default(0);   // brevets, logiciels, fonds commercial
            $table->decimal('immob_corporelles', 15, 2)->default(0);     // terrains, bâtiments, matériel
            $table->decimal('immob_financieres', 15, 2)->default(0);     // titres de participation, prêts LT
            $table->decimal('total_actif_immobilise', 15, 2)->default(0);

            // Actif circulant
            $table->decimal('stocks', 15, 2)->default(0);
            $table->decimal('creances_clients', 15, 2)->default(0);
            $table->decimal('tva_recuperable', 15, 2)->default(0);       // crédit TVA
            $table->decimal('autres_creances', 15, 2)->default(0);
            $table->decimal('total_actif_circulant', 15, 2)->default(0);

            // Trésorerie-Actif
            $table->decimal('banques_caisse', 15, 2)->default(0);
            $table->decimal('total_actif', 15, 2)->default(0);

            // ── PASSIF (SYSCOHADA) ───────────────────────────────
            // Capitaux propres
            $table->decimal('capital_social', 15, 2)->default(0);        // minimum SARL Cameroun = 1 000 000
            $table->decimal('reserves', 15, 2)->default(0);              // réserve légale (5% bénéfice, max 10% capital)
            $table->decimal('report_a_nouveau', 15, 2)->default(0);      // peut être négatif
            $table->decimal('resultat_exercice', 15, 2)->default(0);     // + bénéfice / - perte
            $table->decimal('total_capitaux_propres', 15, 2)->default(0);

            // Dettes financières (long terme)
            $table->decimal('emprunts_long_terme', 15, 2)->default(0);
            $table->decimal('autres_dettes_financieres', 15, 2)->default(0);
            $table->decimal('total_dettes_financieres', 15, 2)->default(0);

            // Passif circulant (court terme)
            $table->decimal('dettes_fournisseurs', 15, 2)->default(0);
            $table->decimal('dettes_fiscales', 15, 2)->default(0);       // TVA à payer, IS à payer
            $table->decimal('dettes_sociales', 15, 2)->default(0);       // CNPS, salaires à payer
            $table->decimal('autres_dettes_court_terme', 15, 2)->default(0);
            $table->decimal('total_passif_circulant', 15, 2)->default(0);
            $table->decimal('total_passif', 15, 2)->default(0);

            // ── COMPTE DE RÉSULTAT ──────────────────────────────
            $table->decimal('chiffre_affaires', 15, 2)->default(0);
            $table->decimal('autres_produits', 15, 2)->default(0);
            $table->decimal('achats_consommes', 15, 2)->default(0);
            $table->decimal('charges_personnel', 15, 2)->default(0);     // masse salariale brute
            $table->decimal('dotations_amortissements', 15, 2)->default(0);
            $table->decimal('autres_charges_exploitation', 15, 2)->default(0);
            $table->decimal('resultat_exploitation', 15, 2)->default(0);
            $table->decimal('produits_financiers', 15, 2)->default(0);
            $table->decimal('charges_financieres', 15, 2)->default(0);
            $table->decimal('resultat_avant_impot', 15, 2)->default(0);
            $table->decimal('is_exerce', 15, 2)->default(0);             // IS de l'exercice
            $table->decimal('resultat_net', 15, 2)->default(0);

            // ── STATUT ──────────────────────────────────────────
            $table->enum('statut', ['brouillon', 'valide', 'depose'])->default('brouillon');
            $table->date('date_depot')->nullable();                       // date dépôt DSF à la DGI
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['exercice']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bilans_comptables');
    }
};
