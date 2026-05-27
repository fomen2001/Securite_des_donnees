<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Parametre extends Model
{
    protected $table = 'parametres';

    protected $fillable = ['cle', 'valeur'];

    /**
     * Lire un paramètre (avec cache 60 min)
     */
    public static function get(string $cle, mixed $defaut = null): mixed
    {
        return Cache::remember("param_{$cle}", 3600, function () use ($cle, $defaut) {
            $row = self::where('cle', $cle)->first();
            return $row ? $row->valeur : $defaut;
        });
    }

    /**
     * Écrire un paramètre et invalider le cache
     */
    public static function set(string $cle, mixed $valeur): void
    {
        self::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
        Cache::forget("param_{$cle}");
    }

    /**
     * Retourner tous les paramètres sous forme de tableau associatif clé => valeur
     */
    public static function tous(): array
    {
        return self::all()->pluck('valeur', 'cle')->toArray();
    }

    /**
     * Valeurs par défaut de l'application
     */
    public static function defauts(): array
    {
        return [
            // Identité
            'entreprise_nom'       => 'BioMédical Inventaire SARL',
            'entreprise_slogan'    => 'Distribution d\'équipements médicaux',
            'entreprise_niu'       => '',
            'entreprise_rc'        => '',
            // Coordonnées
            'entreprise_adresse'   => '',
            'entreprise_ville'     => 'Yaoundé',
            'entreprise_pays'      => 'Cameroun',
            'entreprise_telephone' => '',
            'entreprise_email'     => '',
            'entreprise_site_web'  => '',
            // Logo
            'entreprise_logo'      => null,
            // Facture
            'facture_tva_defaut'   => '19.25',
            'facture_prefix'       => 'FAC',
            'facture_conditions'   => 'Règlement à 30 jours. Tout règlement doit être effectué à l\'ordre de ' . 'BioMédical Inventaire SARL.',
            'facture_mentions'     => 'Les marchandises voyagent aux risques et périls du destinataire. Merci pour votre confiance.',
            'facture_pied'         => '',
            // Divers
            'monnaie'              => 'FCFA',
            'monnaie_symbole'      => 'FCFA',
        ];
    }
}
