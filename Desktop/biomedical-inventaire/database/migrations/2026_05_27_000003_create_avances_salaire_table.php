<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avances_salaire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes');
            $table->decimal('montant', 12, 2);
            $table->date('date_avance');
            $table->unsignedTinyInteger('mois_deduction');   // 1–12
            $table->unsignedSmallInteger('annee_deduction');
            $table->text('motif')->nullable();
            $table->enum('statut', ['en_attente', 'approuvee', 'remboursee', 'annulee'])->default('en_attente');
            $table->foreignId('approuve_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('date_approbation')->nullable();
            $table->foreignId('bulletin_paie_id')->nullable()->constrained('bulletins_paie')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avances_salaire');
    }
};
