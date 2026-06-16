<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Registre des visiteurs ──────────────────────────────
        Schema::create('visiteurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->string('entreprise')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('objet_visite');
            $table->string('personne_visitee');
            $table->foreignId('employe_id')->nullable()->constrained('employes')->nullOnDelete();
            $table->dateTime('date_entree');
            $table->dateTime('date_sortie')->nullable();
            $table->string('badge_numero')->nullable();
            $table->enum('statut', ['en_attente', 'recu', 'sorti', 'annule'])->default('en_attente');
            $table->text('observations')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });

        // ── Messages / Correspondance clients ───────────────────
        Schema::create('messages_clients', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('objet');
            $table->text('corps');
            $table->enum('canal', ['email', 'sms', 'email_sms'])->default('email');
            $table->enum('statut', ['brouillon', 'envoye', 'partiellement_envoye', 'echoue'])->default('brouillon');
            $table->dateTime('envoye_le')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('message_client_destinataires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_client_id')->constrained('messages_clients')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('email_copie')->nullable();
            $table->string('telephone_copie')->nullable();
            $table->enum('statut_email', ['en_attente', 'envoye', 'echoue', 'non_concerne'])->default('en_attente');
            $table->enum('statut_sms',   ['en_attente', 'envoye', 'echoue', 'non_concerne'])->default('en_attente');
            $table->timestamps();
        });

        // ── Rapports de réunion ──────────────────────────────────
        Schema::create('rapports_reunion', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('titre');
            $table->dateTime('date_reunion');
            $table->string('lieu')->nullable();
            $table->enum('type', ['interne', 'client', 'fournisseur', 'partenaire', 'autre'])->default('interne');
            $table->text('ordre_du_jour')->nullable();
            $table->text('compte_rendu');
            $table->text('decisions')->nullable();
            $table->text('actions_a_suivre')->nullable();
            $table->enum('statut', ['brouillon', 'finalise'])->default('brouillon');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('rapport_reunion_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rapport_reunion_id')->constrained('rapports_reunion')->cascadeOnDelete();
            $table->string('nom');
            $table->string('fonction')->nullable();
            $table->string('entreprise')->nullable();
            $table->boolean('present')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapport_reunion_participants');
        Schema::dropIfExists('rapports_reunion');
        Schema::dropIfExists('message_client_destinataires');
        Schema::dropIfExists('messages_clients');
        Schema::dropIfExists('visiteurs');
    }
};
