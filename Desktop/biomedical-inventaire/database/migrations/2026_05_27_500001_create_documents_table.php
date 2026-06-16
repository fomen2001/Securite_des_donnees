<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('couleur')->default('primary');   // couleur Bootstrap
            $table->string('icone')->default('bi-folder');   // Bootstrap Icon
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();           // DOC-2025-0001
            $table->string('titre');
            $table->foreignId('document_categorie_id')->constrained('document_categories')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('type', [
                'contrat', 'attestation', 'facture', 'licence',
                'rapport', 'proces_verbal', 'convention', 'autre',
            ])->default('autre');
            $table->enum('confidentialite', ['public', 'interne', 'confidentiel'])->default('interne');
            $table->enum('statut', ['actif', 'archive', 'expire'])->default('actif');
            $table->text('description')->nullable();
            $table->string('tags')->nullable();              // CSV de mots-clés
            $table->date('date_document');
            $table->date('date_expiration')->nullable();
            $table->string('fichier_chemin');               // chemin dans storage/app/documents/
            $table->string('fichier_nom_original');
            $table->string('fichier_mime');
            $table->unsignedBigInteger('fichier_taille');   // octets
            $table->unsignedInteger('telechargements')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_categories');
    }
};
