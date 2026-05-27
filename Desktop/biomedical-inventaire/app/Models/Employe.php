<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Employe extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $e) => match ($e) {
                'created' => "Employé créé : {$this->prenom} {$this->nom}",
                'updated' => "Employé modifié : {$this->prenom} {$this->nom}",
                'deleted' => "Employé supprimé : {$this->prenom} {$this->nom}",
                default   => $e,
            });
    }

    protected $fillable = [
        'matricule', 'nom', 'prenom', 'date_naissance', 'lieu_naissance',
        'nationalite', 'sexe', 'situation_matrimoniale', 'nombre_enfants',
        'telephone', 'email', 'adresse', 'ville', 'photo',
        'numero_cni', 'numero_cnps', 'numero_contribuable',
        'date_embauche', 'type_contrat', 'date_fin_contrat',
        'poste', 'departement', 'service_id',
        'categorie_professionnelle', 'salaire_base',
        'solde_conge', 'statut', 'notes',
    ];

    protected $casts = [
        'date_naissance'   => 'date',
        'date_embauche'    => 'date',
        'date_fin_contrat' => 'date',
        'salaire_base'     => 'decimal:2',
        'nombre_enfants'   => 'integer',
        'solde_conge'      => 'integer',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function bulletinsPaie()
    {
        return $this->hasMany(BulletinPaie::class);
    }

    public function conges()
    {
        return $this->hasMany(Conge::class);
    }

    // ── Accesseurs ────────────────────────────────────────────────

    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    public function getAncienneteAttribute(): int
    {
        return (int) $this->date_embauche->diffInYears(now());
    }

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            'actif'       => 'Actif',
            'conge'       => 'En congé',
            'suspendu'    => 'Suspendu',
            'demissionne' => 'Démissionné',
            'licencie'    => 'Licencié',
            default       => $this->statut,
        };
    }

    public function getSituationLabelAttribute(): string
    {
        return match ($this->situation_matrimoniale) {
            'celibataire' => 'Célibataire',
            'marie'       => 'Marié(e)',
            'divorce'     => 'Divorcé(e)',
            'veuf'        => 'Veuf/Veuve',
            default       => $this->situation_matrimoniale,
        };
    }

    public function getContratLabelAttribute(): string
    {
        return match ($this->type_contrat) {
            'CDI'        => 'Contrat à Durée Indéterminée',
            'CDD'        => 'Contrat à Durée Déterminée',
            'stage'      => 'Stage',
            'consultant' => 'Consultant',
            default      => $this->type_contrat,
        };
    }

    // ── Droits à congé selon le Code du Travail du Cameroun ───────

    public function droitCongeAnnuel(): int
    {
        $annees = $this->anciennete;
        $base   = 18; // 1,5 j/mois × 12 mois
        $bonus  = match (true) {
            $annees >= 20 => 4,
            $annees >= 15 => 3,
            $annees >= 10 => 2,
            $annees >= 5  => 1,
            default       => 0,
        };
        return $base + $bonus;
    }

    // ── Générateur de matricule ────────────────────────────────────

    public static function genererMatricule(): string
    {
        $annee   = now()->year;
        $dernier = self::withTrashed()
            ->where('matricule', 'like', "EMP-{$annee}-%")
            ->orderByDesc('id')
            ->first();
        $seq = $dernier ? (int) substr($dernier->matricule, -4) + 1 : 1;
        return sprintf('EMP-%d-%04d', $annee, $seq);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }
}
