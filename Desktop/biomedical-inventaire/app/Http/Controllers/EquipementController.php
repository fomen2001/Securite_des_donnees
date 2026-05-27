<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Equipement;
use App\Models\Fournisseur;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EquipementController extends Controller
{
    public function index(Request $request)
    {
        $query = Equipement::with(['categorie', 'service', 'fournisseur']);

        if ($request->filled('recherche')) {
            $q = $request->recherche;
            $query->where(function ($sub) use ($q) {
                $sub->where('designation', 'like', "%{$q}%")
                    ->orWhere('code_inventaire', 'like', "%{$q}%")
                    ->orWhere('marque', 'like', "%{$q}%")
                    ->orWhere('modele', 'like', "%{$q}%")
                    ->orWhere('numero_serie', 'like', "%{$q}%");
            });
        }

        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->categorie_id);
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->filled('etat')) {
            $query->where('etat', $request->etat);
        }

        if ($request->filled('alerte')) {
            match ($request->alerte) {
                'stock_bas'          => $query->stockBas(),
                'maintenance_echue'  => $query->maintenanceEchue(),
                'garantie_expiree'   => $query->garantieExpiree(),
                default              => null,
            };
        }

        $equipements = $query->orderBy('designation')->paginate(15)->withQueryString();

        $categories = Categorie::orderBy('nom')->pluck('nom', 'id');
        $services   = Service::orderBy('nom')->pluck('nom', 'id');

        return view('equipements.index', compact('equipements', 'categories', 'services'));
    }

    public function create()
    {
        $categories  = Categorie::orderBy('nom')->pluck('nom', 'id');
        $fournisseurs = Fournisseur::where('statut', 'actif')->orderBy('nom')->pluck('nom', 'id');
        $services    = Service::orderBy('nom')->pluck('nom', 'id');
        $codeGenere  = Equipement::genererCode();

        return view('equipements.create', compact('categories', 'fournisseurs', 'services', 'codeGenere'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code_inventaire'        => 'required|string|unique:equipements',
            'designation'            => 'required|string|max:255',
            'marque'                 => 'nullable|string|max:100',
            'modele'                 => 'nullable|string|max:100',
            'numero_serie'           => 'nullable|string|unique:equipements',
            'categorie_id'           => 'required|exists:categories,id',
            'fournisseur_id'         => 'nullable|exists:fournisseurs,id',
            'service_id'             => 'nullable|exists:services,id',
            'date_acquisition'       => 'nullable|date',
            'date_mise_en_service'   => 'nullable|date',
            'date_fin_garantie'      => 'nullable|date',
            'prix_achat'             => 'nullable|numeric|min:0',
            'quantite'               => 'required|integer|min:0',
            'quantite_min'           => 'required|integer|min:0',
            'etat'                   => 'required|in:operationnel,en_maintenance,hors_service,en_attente,reformé',
            'classe_risque'          => 'nullable|in:I,IIa,IIb,III',
            'numero_lot'             => 'nullable|string',
            'date_expiration'        => 'nullable|date',
            'description'            => 'nullable|string',
            'image'                  => 'nullable|image|max:2048',
            'periodicite_maintenance'=> 'nullable|integer|min:1',
            'prochaine_maintenance'  => 'nullable|date',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('equipements', 'public');
        }

        $equipement = Equipement::create($validated);

        // Enregistrer le mouvement d'entrée initial
        if ($validated['quantite'] > 0) {
            $equipement->mouvements()->create([
                'type'                   => 'entree',
                'quantite'               => $validated['quantite'],
                'quantite_avant'         => 0,
                'quantite_apres'         => $validated['quantite'],
                'service_destination_id' => $validated['service_id'] ?? null,
                'motif'                  => 'Entrée initiale en stock',
                'user_id'                => auth()->id(),
                'date_mouvement'         => now(),
            ]);
        }

        return redirect()->route('equipements.show', $equipement)
            ->with('success', "L'équipement \"{$equipement->designation}\" a été créé.");
    }

    public function show(Equipement $equipement)
    {
        $equipement->load([
            'categorie', 'fournisseur', 'service',
            'mouvements.user', 'mouvements.serviceSource', 'mouvements.serviceDestination',
            'maintenances.user', 'maintenances.fournisseur',
        ]);

        return view('equipements.show', compact('equipement'));
    }

    public function edit(Equipement $equipement)
    {
        $categories   = Categorie::orderBy('nom')->pluck('nom', 'id');
        $fournisseurs = Fournisseur::where('statut', 'actif')->orderBy('nom')->pluck('nom', 'id');
        $services     = Service::orderBy('nom')->pluck('nom', 'id');

        return view('equipements.edit', compact('equipement', 'categories', 'fournisseurs', 'services'));
    }

    public function update(Request $request, Equipement $equipement)
    {
        $validated = $request->validate([
            'code_inventaire'        => "required|string|unique:equipements,code_inventaire,{$equipement->id}",
            'designation'            => 'required|string|max:255',
            'marque'                 => 'nullable|string|max:100',
            'modele'                 => 'nullable|string|max:100',
            'numero_serie'           => "nullable|string|unique:equipements,numero_serie,{$equipement->id}",
            'categorie_id'           => 'required|exists:categories,id',
            'fournisseur_id'         => 'nullable|exists:fournisseurs,id',
            'service_id'             => 'nullable|exists:services,id',
            'date_acquisition'       => 'nullable|date',
            'date_mise_en_service'   => 'nullable|date',
            'date_fin_garantie'      => 'nullable|date',
            'prix_achat'             => 'nullable|numeric|min:0',
            'quantite'               => 'required|integer|min:0',
            'quantite_min'           => 'required|integer|min:0',
            'etat'                   => 'required|in:operationnel,en_maintenance,hors_service,en_attente,reformé',
            'classe_risque'          => 'nullable|in:I,IIa,IIb,III',
            'numero_lot'             => 'nullable|string',
            'date_expiration'        => 'nullable|date',
            'description'            => 'nullable|string',
            'image'                  => 'nullable|image|max:2048',
            'periodicite_maintenance'=> 'nullable|integer|min:1',
            'prochaine_maintenance'  => 'nullable|date',
        ]);

        if ($request->hasFile('image')) {
            if ($equipement->image) {
                Storage::disk('public')->delete($equipement->image);
            }
            $validated['image'] = $request->file('image')->store('equipements', 'public');
        }

        // Ajustement de stock si la quantité change
        $ancienneQte = $equipement->quantite;
        $equipement->update($validated);

        if ($ancienneQte !== (int) $validated['quantite']) {
            $diff = (int) $validated['quantite'] - $ancienneQte;
            $equipement->mouvements()->create([
                'type'           => 'ajustement',
                'quantite'       => abs($diff),
                'quantite_avant' => $ancienneQte,
                'quantite_apres' => $validated['quantite'],
                'motif'          => 'Ajustement lors de la modification',
                'user_id'        => auth()->id(),
                'date_mouvement' => now(),
            ]);
        }

        return redirect()->route('equipements.show', $equipement)
            ->with('success', 'Équipement mis à jour.');
    }

    public function destroy(Equipement $equipement)
    {
        $equipement->delete();

        return redirect()->route('equipements.index')
            ->with('success', "L'équipement a été archivé.");
    }

    public function export(Request $request)
    {
        $equipements = Equipement::with(['categorie', 'service', 'fournisseur'])
            ->orderBy('code_inventaire')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="inventaire_' . now()->format('Ymd') . '.csv"',
        ];

        $callback = function () use ($equipements) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($handle, [
                'Code', 'Désignation', 'Marque', 'Modèle', 'N° Série',
                'Catégorie', 'Service', 'Fournisseur', 'État',
                'Quantité', 'Prix Achat', 'Date Acquisition', 'Fin Garantie',
            ], ';');

            foreach ($equipements as $e) {
                fputcsv($handle, [
                    $e->code_inventaire,
                    $e->designation,
                    $e->marque,
                    $e->modele,
                    $e->numero_serie,
                    $e->categorie?->nom,
                    $e->service?->nom,
                    $e->fournisseur?->nom,
                    $e->etat_label,
                    $e->quantite,
                    $e->prix_achat,
                    $e->date_acquisition?->format('d/m/Y'),
                    $e->date_fin_garantie?->format('d/m/Y'),
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
