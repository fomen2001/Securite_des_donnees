<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\BulletinPaie;
use App\Models\Conge;
use App\Models\Employe;

class RhDashboardController extends Controller
{
    public function index()
    {
        $mois  = now()->month;
        $annee = now()->year;

        $stats = [
            'employes_actifs'       => Employe::where('statut', 'actif')->count(),
            'conges_en_attente'     => Conge::where('statut', 'en_attente')->count(),
            'bulletins_mois'        => BulletinPaie::where('mois', $mois)->where('annee', $annee)->count(),
            'masse_salariale_mois'  => BulletinPaie::where('mois', $mois)->where('annee', $annee)->sum('net_a_payer'),
        ];

        $congesEnAttente = Conge::with('employe')
            ->where('statut', 'en_attente')
            ->latest()
            ->limit(6)
            ->get();

        $derniersBulletins = BulletinPaie::with('employe')
            ->where('mois', $mois)
            ->where('annee', $annee)
            ->latest()
            ->limit(6)
            ->get();

        return view('rh.dashboard', compact('stats', 'congesEnAttente', 'derniersBulletins'));
    }
}
