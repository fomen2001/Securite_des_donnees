<?php

namespace App\Http\Controllers;

use App\Models\Parametre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ParametreController extends Controller
{
    public function index()
    {
        // Fusionner les défauts avec les valeurs en base
        $defauts = Parametre::defauts();
        $enBase  = Parametre::tous();
        $params  = array_merge($defauts, $enBase);

        return view('parametres.index', compact('params'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'entreprise_nom'       => 'required|string|max:200',
            'entreprise_slogan'    => 'nullable|string|max:255',
            'entreprise_niu'       => 'nullable|string|max:50',
            'entreprise_rc'        => 'nullable|string|max:80',
            'entreprise_adresse'   => 'nullable|string|max:500',
            'entreprise_ville'     => 'nullable|string|max:100',
            'entreprise_pays'      => 'nullable|string|max:80',
            'entreprise_telephone' => 'nullable|string|max:30',
            'entreprise_email'     => 'nullable|email|max:150',
            'entreprise_site_web'  => 'nullable|url|max:200',
            'logo'                 => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'facture_tva_defaut'   => 'required|numeric|min:0|max:100',
            'facture_prefix'       => 'required|string|max:10',
            'facture_conditions'   => 'nullable|string|max:1000',
            'facture_mentions'     => 'nullable|string|max:1000',
            'facture_pied'         => 'nullable|string|max:500',
            'monnaie'              => 'required|string|max:10',
            'monnaie_symbole'      => 'required|string|max:10',
        ]);

        // Upload du logo — stocké directement dans public/images/logo.{ext}
        if ($request->hasFile('logo')) {
            // Supprimer tout fichier logo.* existant
            foreach (glob(public_path('images/logo.*')) ?: [] as $ancien) {
                @unlink($ancien);
            }
            $ext      = strtolower($request->file('logo')->getClientOriginalExtension());
            $filename = 'logo.' . $ext;
            $request->file('logo')->move(public_path('images'), $filename);
            Parametre::set('entreprise_logo', $filename);
        }

        // Supprimer le logo si la case est cochée
        if ($request->boolean('supprimer_logo')) {
            $ancienLogo = Parametre::get('entreprise_logo');
            if ($ancienLogo && file_exists(public_path('images/' . $ancienLogo))) {
                @unlink(public_path('images/' . $ancienLogo));
            }
            Parametre::set('entreprise_logo', null);
        }

        // Sauvegarder tous les autres paramètres
        $champs = [
            'entreprise_nom', 'entreprise_slogan', 'entreprise_niu', 'entreprise_rc',
            'entreprise_adresse', 'entreprise_ville', 'entreprise_pays',
            'entreprise_telephone', 'entreprise_email', 'entreprise_site_web',
            'facture_tva_defaut', 'facture_prefix', 'facture_conditions',
            'facture_mentions', 'facture_pied', 'monnaie', 'monnaie_symbole',
        ];

        foreach ($champs as $cle) {
            Parametre::set($cle, $request->input($cle, ''));
        }

        return back()->with('success', 'Paramètres enregistrés.');
    }
}
