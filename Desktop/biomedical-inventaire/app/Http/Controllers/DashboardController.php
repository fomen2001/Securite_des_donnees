<?php

namespace App\Http\Controllers;

use App\Models\Equipement;
use App\Models\Maintenance;
use App\Models\MouvementStock;
use App\Models\Service;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_equipements'       => Equipement::count(),
            'operationnels'           => Equipement::where('etat', 'operationnel')->count(),
            'en_maintenance'          => Equipement::where('etat', 'en_maintenance')->count(),
            'hors_service'            => Equipement::where('etat', 'hors_service')->count(),
            'stock_bas'               => Equipement::stockBas()->count(),
            'maintenance_echue'       => Equipement::maintenanceEchue()->count(),
            'garantie_expiree'        => Equipement::garantieExpiree()->count(),
        ];

        $maintenancesProchaines = Maintenance::with('equipement')
            ->where('statut', 'planifiee')
            ->where('date_planifiee', '>=', now())
            ->orderBy('date_planifiee')
            ->limit(5)
            ->get();

        $alertesStock = Equipement::with(['categorie', 'service'])
            ->stockBas()
            ->whereNotIn('etat', ['reformé', 'hors_service'])
            ->limit(5)
            ->get();

        $derniersMovements = MouvementStock::with(['equipement', 'user'])
            ->latest()
            ->limit(8)
            ->get();

        $equipementsParService = Service::withCount('equipements')
            ->whereHas('equipements')
            ->orderByDesc('equipements_count')
            ->get();

        $equipementsParEtat = Equipement::selectRaw('etat, count(*) as total')
            ->groupBy('etat')
            ->pluck('total', 'etat');

        return view('dashboard.index', compact(
            'stats',
            'maintenancesProchaines',
            'alertesStock',
            'derniersMovements',
            'equipementsParService',
            'equipementsParEtat',
        ));
    }
}
