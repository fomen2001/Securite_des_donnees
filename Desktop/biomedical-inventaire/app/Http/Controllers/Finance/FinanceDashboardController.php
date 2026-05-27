<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BulletinPaie;
use App\Models\Depense;
use App\Models\Vente;
use Illuminate\Support\Facades\DB;

class FinanceDashboardController extends Controller
{
    public function index()
    {
        $annee = now()->year;
        $mois  = now()->month;

        // ── KPIs du mois courant ────────────────────────────────
        $ca_mois = Vente::whereIn('statut', ['payee', 'livree', 'facturee'])
            ->whereMonth('date_vente', $mois)->whereYear('date_vente', $annee)
            ->sum('total_ttc');

        $depenses_mois = Depense::approuvees()
            ->whereMonth('date_depense', $mois)->whereYear('date_depense', $annee)
            ->sum('montant_ttc');

        $masse_salariale_mois = BulletinPaie::where('mois', $mois)->where('annee', $annee)
            ->sum('net_a_payer');

        $resultat_mois = $ca_mois - $depenses_mois - $masse_salariale_mois;

        // ── Évolution mensuelle sur 12 mois (graphe) ────────────
        $evolution = collect(range(11, 0))->map(function ($i) use ($annee, $mois) {
            $date = now()->subMonths($i);
            $m = $date->month;
            $a = $date->year;

            $ca = Vente::whereIn('statut', ['payee', 'livree', 'facturee'])
                ->whereMonth('date_vente', $m)->whereYear('date_vente', $a)
                ->sum('total_ttc');

            $dep = Depense::approuvees()
                ->whereMonth('date_depense', $m)->whereYear('date_depense', $a)
                ->sum('montant_ttc');

            $sal = BulletinPaie::where('mois', $m)->where('annee', $a)->sum('net_a_payer');

            return [
                'mois'      => $date->locale('fr')->isoFormat('MMM YY'),
                'ca'        => (float) $ca,
                'depenses'  => (float) ($dep + $sal),
                'resultat'  => (float) ($ca - $dep - $sal),
            ];
        });

        // ── Répartition des dépenses par catégorie ──────────────
        $depenses_par_cat = Depense::approuvees()
            ->whereYear('date_depense', $annee)
            ->join('categories_depenses', 'depenses.categorie_depense_id', '=', 'categories_depenses.id')
            ->select('categories_depenses.nom', 'categories_depenses.couleur', DB::raw('SUM(depenses.montant_ttc) as total'))
            ->groupBy('categories_depenses.id', 'categories_depenses.nom', 'categories_depenses.couleur')
            ->orderByDesc('total')
            ->get();

        // ── KPIs annuels ────────────────────────────────────────
        $ca_annuel = Vente::whereIn('statut', ['payee', 'livree', 'facturee'])
            ->whereYear('date_vente', $annee)->sum('total_ttc');

        $depenses_annuel = Depense::approuvees()
            ->whereYear('date_depense', $annee)->sum('montant_ttc');

        $masse_salariale_annuelle = BulletinPaie::where('annee', $annee)->sum('net_a_payer');

        $resultat_annuel = $ca_annuel - $depenses_annuel - $masse_salariale_annuelle;

        // ── Dernières dépenses ───────────────────────────────────
        $dernieresDepenses = Depense::with('categorie')
            ->latest()->limit(8)->get();

        return view('finance.dashboard', compact(
            'ca_mois', 'depenses_mois', 'masse_salariale_mois', 'resultat_mois',
            'ca_annuel', 'depenses_annuel', 'masse_salariale_annuelle', 'resultat_annuel',
            'evolution', 'depenses_par_cat', 'dernieresDepenses', 'annee', 'mois'
        ));
    }
}
