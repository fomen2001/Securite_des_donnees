<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Document extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['titre', 'statut', 'confidentialite'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $e) => match ($e) {
                'created' => "Document ajouté : {$this->titre}",
                'updated' => "Document modifié : {$this->titre}",
                'deleted' => "Document supprimé : {$this->titre}",
                default   => $e,
            });
    }

    protected $fillable = [
        'reference', 'titre', 'document_categorie_id', 'user_id',
        'type', 'confidentialite', 'statut', 'description', 'tags',
        'date_document', 'date_expiration',
        'fichier_chemin', 'fichier_nom_original', 'fichier_mime', 'fichier_taille',
        'telechargements',
    ];

    protected $casts = [
        'date_document'   => 'date',
        'date_expiration' => 'date',
        'fichier_taille'  => 'integer',
        'telechargements' => 'integer',
    ];

    // ── Relations ────────────────────────────────────────────────

    public function categorie()
    {
        return $this->belongsTo(DocumentCategorie::class, 'document_categorie_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Accesseurs ───────────────────────────────────────────────

    public function getStatutLabelAttribute(): string
    {
        return match ($this->statut) {
            'actif'   => 'Actif',
            'archive' => 'Archivé',
            'expire'  => 'Expiré',
            default   => $this->statut,
        };
    }

    public function getStatutBadgeAttribute(): string
    {
        return match ($this->statut) {
            'actif'   => 'success',
            'archive' => 'secondary',
            'expire'  => 'danger',
            default   => 'secondary',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'contrat'       => 'Contrat',
            'attestation'   => 'Attestation',
            'facture'       => 'Facture',
            'licence'       => 'Licence',
            'rapport'       => 'Rapport',
            'proces_verbal' => 'Procès-verbal',
            'convention'    => 'Convention',
            default         => 'Autre',
        };
    }

    public function getConfidentialiteLabelAttribute(): string
    {
        return match ($this->confidentialite) {
            'public'       => 'Public',
            'interne'      => 'Interne',
            'confidentiel' => 'Confidentiel',
            default        => $this->confidentialite,
        };
    }

    public function getConfidentialiteBadgeAttribute(): string
    {
        return match ($this->confidentialite) {
            'public'       => 'success',
            'interne'      => 'primary',
            'confidentiel' => 'danger',
            default        => 'secondary',
        };
    }

    public function getTailleLisibleAttribute(): string
    {
        $octets = $this->fichier_taille;
        if ($octets < 1024)       return "{$octets} o";
        if ($octets < 1048576)    return round($octets / 1024, 1) . ' Ko';
        if ($octets < 1073741824) return round($octets / 1048576, 1) . ' Mo';
        return round($octets / 1073741824, 2) . ' Go';
    }

    public function getTagsListAttribute(): array
    {
        if (!$this->tags) return [];
        return array_filter(array_map('trim', explode(',', $this->tags)));
    }

    public function getIconeMimeAttribute(): string
    {
        return match (true) {
            str_contains($this->fichier_mime, 'pdf')   => 'bi-file-earmark-pdf text-danger',
            str_contains($this->fichier_mime, 'word')  => 'bi-file-earmark-word text-primary',
            str_contains($this->fichier_mime, 'excel') ||
            str_contains($this->fichier_mime, 'sheet') => 'bi-file-earmark-excel text-success',
            str_contains($this->fichier_mime, 'image') => 'bi-file-earmark-image text-info',
            str_contains($this->fichier_mime, 'zip')   => 'bi-file-earmark-zip text-warning',
            default                                    => 'bi-file-earmark text-secondary',
        };
    }

    public function getEstExpireAttribute(): bool
    {
        return $this->date_expiration && $this->date_expiration->isPast();
    }

    public function getExpireBientotAttribute(): bool
    {
        return $this->date_expiration
            && !$this->date_expiration->isPast()
            && $this->date_expiration->diffInDays(now()) <= 30;
    }

    public function getJoursAvantExpirationAttribute(): ?int
    {
        if (!$this->date_expiration) return null;
        return (int) now()->diffInDays($this->date_expiration, false);
    }

    // ── Numérotation ─────────────────────────────────────────────

    public static function genererReference(): string
    {
        $annee   = Carbon::now()->year;
        $dernier = self::withTrashed()
            ->where('reference', 'like', "DOC-{$annee}-%")
            ->orderByDesc('id')->first();
        $seq = $dernier ? (int) substr($dernier->reference, -4) + 1 : 1;
        return sprintf('DOC-%d-%04d', $annee, $seq);
    }

    // ── Mise à jour automatique du statut ────────────────────────

    public function mettreAJourStatut(): void
    {
        if ($this->statut === 'archive') return;
        if ($this->est_expire) {
            $this->update(['statut' => 'expire']);
        }
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeActifs($q)
    {
        return $q->where('statut', 'actif');
    }

    public function scopeExpirantBientot($q, int $jours = 30)
    {
        return $q->where('statut', 'actif')
            ->whereNotNull('date_expiration')
            ->whereDate('date_expiration', '>=', now())
            ->whereDate('date_expiration', '<=', now()->addDays($jours));
    }
}
