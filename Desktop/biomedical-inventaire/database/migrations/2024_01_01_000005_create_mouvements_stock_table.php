<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mouvements_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipement_id')->constrained('equipements');
            $table->enum('type', [
                'entree',       // réception fournisseur
                'sortie',       // mise en service / transfert
                'transfert',    // changement de service
                'retour',       // retour en stock
                'ajustement',   // correction inventaire
                'reforme',      // mise au rebut
            ]);
            $table->integer('quantite');
            $table->integer('quantite_avant');
            $table->integer('quantite_apres');
            $table->foreignId('service_source_id')->nullable()->constrained('services');
            $table->foreignId('service_destination_id')->nullable()->constrained('services');
            $table->string('reference_document')->nullable(); // N° bon de livraison, etc.
            $table->text('motif')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('date_mouvement')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvements_stock');
    }
};
