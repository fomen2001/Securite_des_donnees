<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Equipement extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => "Équipement créé : {$this->designation}",
                'updated' => "Équipement modifié : {$this->designation}",
                'deleted' => "Équipement supprimé : {$this->designation}",
                default   => $eventName,
            });
    }

    protected $table = 'equipements';

    protected $fillable = [
        'code_inventaire', 'designation', 'marque', 'modele', 'numero_serie',
        'categorie_id', 'fournisseur_id', 'service_id',
        'date_acquisition', 'date_mise_en_service', 'date_fin_garantie',
        'prix_achat', 'quantite', 'quantite_min', 'etat', 'classe_risque',
        'numero_lot', 'date_expiration', 'description', 'image',
        'periodicite_maintenance', 'prochaine_maintenance',
    ];

    protected $casts = [
        'date_acquisition'       => 'date',
        'date_mise_en_service'   => 'date',
        'date_fin_garantie'      => 'date',
        'date_expiration'        => 'date',
        'prochaine_maintenance'  => 'date',
        'prix_achat'             => 'decimal:2',
    ];

    // ---- Relations ----

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function mouvements()
    {
        return $this->hasMany(MouvementStock::class, 'equipement_id')->latest('date_mouvement');
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'equipement_id')->latest('date_planifiee');
    }

    public function derniereMaintenance()
    {
        return $this->hasOne(Maintenance::class, 'equipement_id')
            ->whereIn('statut', ['terminee'])
            ->latest('date_fin');
    }

    // ---- Accesseurs ----

    public function getEtatLabelAttribute(): string
    {
        return match ($this->etat) {
            'operationnel'   => 'Opérationnel',
            'en_maintenance' => 'En maintenance',
            'hors_service'   => 'Hors service',
            'en_attente'     => 'En attente',
            'reformé'        => 'Réformé',
            default          => $this->etat,
        };
    }

    public function getEtatBadgeAttribute(): string
    {
        return match ($this->etat) {
            'operationnel'   => 'success',
            'en_maintenance' => 'warning',
            'hors_service'   => 'danger',
            'en_attente'     => 'info',
            'reformé'        => 'secondary',
            default          => 'secondary',
        };
    }

    public function getGarantieExpireeAttribute(): bool
    {
        return $this->date_fin_garantie && $this->date_fin_garantie->isPast();
    }

    public function getStockAlertAttribute(): bool
    {
        return $this->quantite <= $this->quantite_min;
    }

    public function getMaintenanceEchueAttribute(): bool
    {
        return $this->prochaine_maintenance && $this->prochaine_maintenance->isPast();
    }

    // ---- Générateur de code inventaire ----

    public static function genererCode(): string
    {
        $annee = Carbon::now()->year;
        $dernier = self::withTrashed()
            ->where('code_inventaire', 'like', "BIO-{$annee}-%")
            ->orderByDesc('id')
            ->first();

        $sequence = $dernier
            ? (int) substr($dernier->code_inventaire, -4) + 1
            : 1;

        return sprintf('BIO-%d-%04d', $annee, $sequence);
    }

    // ---- Scopes ----

    public function scopeOperationnel($query)
    {
        return $query->where('etat', 'operationnel');
    }

    public function scopeStockBas($query)
    {
        return $query->whereColumn('quantite', '<=', 'quantite_min');
    }

    public function scopeMaintenanceEchue($query)
    {
        return $query->whereNotNull('prochaine_maintenance')
            ->where('prochaine_maintenance', '<', now());
    }

    public function scopeGarantieExpiree($query)
    {
        return $query->whereNotNull('date_fin_garantie')
            ->where('date_fin_garantie', '<', now());
    }
}
