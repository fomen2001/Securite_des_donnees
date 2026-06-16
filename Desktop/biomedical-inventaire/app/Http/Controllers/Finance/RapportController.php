<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BulletinPaie;
use App\Models\Depense;
use App\Models\Vente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RapportController extends Controller
{
    public function index(Request $request)
    {
        $annee = (int) $request->get('annee', now()->year);
        $mois  = $request->get('mois'); // null = rapport annuel

        // ── Résumé mensuel sur toute l'année ────────────────────
        $moisLabels = [
            1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',
            5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',
            9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre',
        ];

        $rapport = collect(range(1, 12))->map(function ($m) use ($annee) {
            $ca = Vente::whereIn('statut', ['payee', 'livree', 'facturee'])
                ->whereMonth('date_vente', $m)->whereYear('date_vente', $annee)
                ->sum('total_ttc');

            $dep = Depense::approuvees()
                ->whereMonth('date_depense', $m)->whereYear('date_depense', $annee)
                ->sum('montant_ttc');

            $sal = BulletinPaie::where('mois', $m)->where('annee', $annee)
                ->sum(DB::raw('salaire_brut + cotisation_cnps_employeur'));

            return [
                'mois'           => $m,
                'mois_label'     => [1=>'Jan',2=>'Fév',3=>'Mar',4=>'Avr',5=>'Mai',6=>'Juin',7=>'Juil',8=>'Aoû',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Déc'][$m],
                'recettes'       => (float) $ca,
                'depenses'       => (float) $dep,
                'masse_salariale'=> (float) $sal,
                'charges_totales'=> (float) ($dep + $sal),
                'resultat'       => (float) ($ca - $dep - $sal),
            ];
        });

        // ── Totaux annuels ───────────────────────────────────────
        $totaux = [
            'recettes'        => $rapport->sum('recettes'),
            'depenses'        => $rapport->sum('depenses'),
            'masse_salariale' => $rapport->sum('masse_salariale'),
            'charges_totales' => $rapport->sum('charges_totales'),
            'resultat'        => $rapport->sum('resultat'),
        ];

        // ── Détail dépenses par catégorie ────────────────────────
        $depParCat = Depense::approuvees()
            ->whereYear('date_depense', $annee)
            ->join('categories_depenses', 'depenses.categorie_depense_id', '=', 'categories_depenses.id')
            ->select(
                'categories_depenses.nom',
                'categories_depenses.couleur',
                'categories_depenses.type',
                DB::raw('SUM(depenses.montant_ttc) as total'),
                DB::raw('COUNT(*) as nb')
            )
            ->groupBy('categories_depenses.id', 'categories_depenses.nom', 'categories_depenses.couleur', 'categories_depenses.type')
            ->orderByDesc('total')
            ->get();

        // ── Top clients (recettes) ───────────────────────────────
        $topClients = Vente::whereIn('ventes.statut', ['payee', 'livree', 'facturee'])
            ->whereYear('ventes.date_vente', $annee)
            ->join('clients', 'ventes.client_id', '=', 'clients.id')
            ->select('clients.nom', DB::raw('SUM(ventes.total_ttc) as total'), DB::raw('COUNT(*) as nb'))
            ->groupBy('clients.id', 'clients.nom')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $annees = range(now()->year, now()->year - 4);

        return view('finance.rapports.index', compact(
            'rapport', 'totaux', 'depParCat', 'topClients',
            'annee', 'annees', 'moisLabels'
        ));
    }

    public function tresorerie(Request $request)
    {
        $annee = (int) $request->get('annee', now()->year);

        // Solde cumulatif mois par mois
        $solde = 0;
        $flux = collect(range(1, 12))->map(function ($m) use ($annee, &$solde) {
            $entrees = Vente::where('statut', 'payee')
                ->whereMonth('date_vente', $m)->whereYear('date_vente', $annee)
                ->sum('montant_paye');

            $sorties_dep = Depense::where('statut', 'payee')
                ->whereMonth('date_depense', $m)->whereYear('date_depense', $annee)
                ->sum('montant_ttc');

            $sorties_sal = BulletinPaie::where('statut', 'paye')
                ->where('mois', $m)->where('annee', $annee)
                ->sum('net_a_payer');

            $sorties = $sorties_dep + $sorties_sal;
            $net     = $entrees - $sorties;
            $solde  += $net;

            return [
                'mois'     => [1=>'Jan',2=>'Fév',3=>'Mar',4=>'Avr',5=>'Mai',6=>'Juin',7=>'Juil',8=>'Aoû',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Déc'][$m],
                'entrees'  => (float) $entrees,
                'sorties'  => (float) $sorties,
                'net'      => (float) $net,
                'solde'    => (float) $solde,
            ];
        });

        $annees = range(now()->year, now()->year - 4);

        return view('finance.tresorerie', compact('flux', 'annee', 'annees'));
    }
}
