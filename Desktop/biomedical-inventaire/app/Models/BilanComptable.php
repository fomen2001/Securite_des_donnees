<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BilanComptable extends Model
{
    protected $table = 'bilans_comptables';

    protected $fillable = [
        'exercice',
        // Actif
        'immob_incorporelles', 'immob_corporelles', 'immob_financieres', 'total_actif_immobilise',
        'stocks', 'creances_clients', 'tva_recuperable', 'autres_creances', 'total_actif_circulant',
        'banques_caisse', 'total_actif',
        // Passif
        'capital_social', 'reserves', 'report_a_nouveau', 'resultat_exercice', 'total_capitaux_propres',
        'emprunts_long_terme', 'autres_dettes_financieres', 'total_dettes_financieres',
        'dettes_fournisseurs', 'dettes_fiscales', 'dettes_sociales', 'autres_dettes_court_terme',
        'total_passif_circulant', 'total_passif',
        // Compte de résultat
        'chiffre_affaires', 'autres_produits', 'achats_consommes', 'charges_personnel',
        'dotations_amortissements', 'autres_charges_exploitation', 'resultat_exploitation',
        'produits_financiers', 'charges_financieres', 'resultat_avant_impot',
        'is_exerce', 'resultat_net',
        // Statut
        'statut', 'date_depot', 'notes', 'created_by', 'validated_by',
    ];

    protected $casts = [
        'date_depot' => 'date',
    ];

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function getStatutBadgeAttribute(): string
    {
        return match($this->statut) {
            'brouillon' => 'secondary',
            'valide'    => 'primary',
            'depose'    => 'success',
            default     => 'secondary',
        };
    }

    public function getStatutLabelAttribute(): string
    {
        return match($this->statut) {
            'brouillon' => 'Brouillon',
            'valide'    => 'Validé',
            'depose'    => 'Déposé (DSF)',
            default     => $this->statut,
        };
    }

    public function getEquilibreAttribute(): bool
    {
        return round($this->total_actif, 0) === round($this->total_passif, 0);
    }

    /** Recalcule tous les sous-totaux */
    public function recalculerTotaux(): void
    {
        $this->total_actif_immobilise  = $this->immob_incorporelles + $this->immob_corporelles + $this->immob_financieres;
        $this->total_actif_circulant   = $this->stocks + $this->creances_clients + $this->tva_recuperable + $this->autres_creances;
        $this->total_actif             = $this->total_actif_immobilise + $this->total_actif_circulant + $this->banques_caisse;

        $this->total_capitaux_propres  = $this->capital_social + $this->reserves + $this->report_a_nouveau + $this->resultat_exercice;
        $this->total_dettes_financieres= $this->emprunts_long_terme + $this->autres_dettes_financieres;
        $this->total_passif_circulant  = $this->dettes_fournisseurs + $this->dettes_fiscales + $this->dettes_sociales + $this->autres_dettes_court_terme;
        $this->total_passif            = $this->total_capitaux_propres + $this->total_dettes_financieres + $this->total_passif_circulant;

        $totalProduits   = $this->chiffre_affaires + $this->autres_produits;
        $totalChargesExp = $this->achats_consommes + $this->charges_personnel + $this->dotations_amortissements + $this->autres_charges_exploitation;
        $this->resultat_exploitation   = $totalProduits - $totalChargesExp;
        $this->resultat_avant_impot    = $this->resultat_exploitation + $this->produits_financiers - $this->charges_financieres;
        $this->resultat_net            = $this->resultat_avant_impot - $this->is_exerce;
        $this->resultat_exercice       = $this->resultat_net;
    }
}
