<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revisions_salaire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')->constrained('employes');
            $table->decimal('ancien_salaire', 12, 2);
            $table->decimal('nouveau_salaire', 12, 2);
            $table->date('date_effet');
            $table->enum('motif', ['augmentation_merite', 'promotion', 'reclassement', 'anciennete', 'revision_annuelle', 'autre'])->default('revision_annuelle');
            $table->text('commentaire')->nullable();
            $table->foreignId('approuve_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisions_salaire');
    }
};
