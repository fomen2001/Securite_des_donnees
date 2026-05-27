<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Depense extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $e) => match ($e) {
                'created' => "Dépense enregistrée : {$this->libelle} ({$this->montant_ttc} FCFA)",
                'updated' => "Dépense modifiée : {$this->libelle}",
                'deleted' => "Dépense supprimée : {$this->libelle}",
                default   => $e,
            });
    }

    protected $fillable = [
        'reference', 'categorie_depense_id', 'libelle',
        'montant_ht', 'tva', 'montant_ttc',
        'date_depense', 'mode_paiement', 'beneficiaire',
        'fournisseur_id', 'numero_piece', 'statut',
        'approuve_par', 'date_approbation', 'notes', 'document_path', 'created_by',
    ];

    protected $casts = [
        'date_depense'     => 'date',
        'date_approbation' => 'datetime',
        'montant_ht'       => 'decimal:2',
        'tva'              => 'decimal:2',
        'montant_ttc'      => 'decimal:2',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function categorie()
    {
        return $this->belongsTo(CategorieDepense::class, 'categorie_depense_id');
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function approbateur()
    {
        return $this->belongsTo(User::class, 'approuve_par');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Accesseurs ────────────────────────────────────────────────

    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => 'warning',
            'approuvee'  => 'primary',
            'payee'      => 'success',
            'rejetee'    => 'danger',
            default      => 'secondary',
        };
    }

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            'en_attente' => 'En attente',
            'approuvee'  => 'Approuvée',
            'payee'      => 'Payée',
            'rejetee'    => 'Rejetée',
            default      => $this->statut,
        };
    }

    // ── Générateur de référence ───────────────────────────────────

    public static function genererReference(): string
    {
        $annee = now()->year;
        $count = self::withTrashed()->whereYear('created_at', $annee)->count() + 1;
        return sprintf('DEP-%d-%04d', $annee, $count);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeApprouvees($query)
    {
        return $query->whereIn('statut', ['approuvee', 'payee']);
    }

    public function scopeParMois($query, int $mois, int $annee)
    {
        return $query->whereMonth('date_depense', $mois)->whereYear('date_depense', $annee);
    }
}
