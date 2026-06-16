<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentCategorie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    private const EXTENSIONS_AUTORISEES = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'zip'];
    private const TAILLE_MAX_MB = 20;

    public function index(Request $request)
    {
        // Mettre à jour les statuts expirés
        Document::where('statut', 'actif')
            ->whereNotNull('date_expiration')
            ->whereDate('date_expiration', '<', now())
            ->update(['statut' => 'expire']);

        $query = Document::with(['categorie', 'user'])->latest('date_document');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($s) => $s->where('titre', 'like', "%{$q}%")
                ->orWhere('reference', 'like', "%{$q}%")
                ->orWhere('tags', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%"));
        }
        if ($request->filled('categorie_id')) {
            $query->where('document_categorie_id', $request->categorie_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('confidentialite')) {
            $query->where('confidentialite', $request->confidentialite);
        }

        // Masquer les docs confidentiels aux non-autorisés
        if (!auth()->user()->can('documents.confidentiels')) {
            $query->where('confidentialite', '!=', 'confidentiel');
        }

        $documents  = $query->paginate(20)->withQueryString();
        $categories = DocumentCategorie::withCount('documents')->orderBy('nom')->get();

        $alertes = Document::expirantBientot(30)
            ->when(!auth()->user()->can('documents.confidentiels'), fn ($q) => $q->where('confidentialite', '!=', 'confidentiel'))
            ->orderBy('date_expiration')->get();

        $stats = [
            'total'      => Document::count(),
            'actifs'     => Document::where('statut', 'actif')->count(),
            'expires'    => Document::where('statut', 'expire')->count(),
            'bientot'    => Document::expirantBientot(30)->count(),
            'taille'     => Document::sum('fichier_taille'),
        ];

        return view('documents.index', compact('documents', 'categories', 'alertes', 'stats'));
    }

    public function create()
    {
        $categories = DocumentCategorie::orderBy('nom')->get();
        return view('documents.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre'                 => 'required|string|max:255',
            'document_categorie_id' => 'required|exists:document_categories,id',
            'type'                  => 'required|in:contrat,attestation,facture,licence,rapport,proces_verbal,convention,autre',
            'confidentialite'       => 'required|in:public,interne,confidentiel',
            'date_document'         => 'required|date',
            'date_expiration'       => 'nullable|date|after:date_document',
            'description'           => 'nullable|string|max:2000',
            'tags'                  => 'nullable|string|max:500',
            'fichier'               => 'required|file|max:' . (self::TAILLE_MAX_MB * 1024)
                . '|mimes:' . implode(',', self::EXTENSIONS_AUTORISEES),
        ], [
            'fichier.mimes' => 'Formats acceptés : ' . implode(', ', self::EXTENSIONS_AUTORISEES) . '.',
            'fichier.max'   => 'Le fichier ne doit pas dépasser ' . self::TAILLE_MAX_MB . ' Mo.',
        ]);

        $fichier = $request->file('fichier');
        $chemin  = $fichier->store('documents', 'local');

        $statut = 'actif';
        if ($request->date_expiration && now()->gt($request->date_expiration)) {
            $statut = 'expire';
        }

        Document::create([
            'reference'              => Document::genererReference(),
            'titre'                  => $request->titre,
            'document_categorie_id'  => $request->document_categorie_id,
            'user_id'                => auth()->id(),
            'type'                   => $request->type,
            'confidentialite'        => $request->confidentialite,
            'statut'                 => $statut,
            'description'            => $request->description,
            'tags'                   => $request->tags,
            'date_document'          => $request->date_document,
            'date_expiration'        => $request->date_expiration,
            'fichier_chemin'         => $chemin,
            'fichier_nom_original'   => $fichier->getClientOriginalName(),
            'fichier_mime'           => $fichier->getMimeType(),
            'fichier_taille'         => $fichier->getSize(),
            'telechargements'        => 0,
        ]);

        return redirect()->route('documents.index')
            ->with('success', 'Document ajouté avec succès.');
    }

    public function show(Document $document)
    {
        $this->autoriserAcces($document);
        $document->load(['categorie', 'user']);
        return view('documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        $this->autoriserAcces($document);
        $categories = DocumentCategorie::orderBy('nom')->get();
        return view('documents.edit', compact('document', 'categories'));
    }

    public function update(Request $request, Document $document)
    {
        $this->autoriserAcces($document);

        $request->validate([
            'titre'                 => 'required|string|max:255',
            'document_categorie_id' => 'required|exists:document_categories,id',
            'type'                  => 'required|in:contrat,attestation,facture,licence,rapport,proces_verbal,convention,autre',
            'confidentialite'       => 'required|in:public,interne,confidentiel',
            'date_document'         => 'required|date',
            'date_expiration'       => 'nullable|date|after:date_document',
            'description'           => 'nullable|string|max:2000',
            'tags'                  => 'nullable|string|max:500',
            'fichier'               => 'nullable|file|max:' . (self::TAILLE_MAX_MB * 1024)
                . '|mimes:' . implode(',', self::EXTENSIONS_AUTORISEES),
        ]);

        $data = [
            'titre'                 => $request->titre,
            'document_categorie_id' => $request->document_categorie_id,
            'type'                  => $request->type,
            'confidentialite'       => $request->confidentialite,
            'date_document'         => $request->date_document,
            'date_expiration'       => $request->date_expiration,
            'description'           => $request->description,
            'tags'                  => $request->tags,
            'statut'                => $request->statut ?? $document->statut,
        ];

        if ($request->hasFile('fichier')) {
            Storage::disk('local')->delete($document->fichier_chemin);
            $fichier = $request->file('fichier');
            $chemin  = $fichier->store('documents', 'local');
            $data['fichier_chemin']       = $chemin;
            $data['fichier_nom_original'] = $fichier->getClientOriginalName();
            $data['fichier_mime']         = $fichier->getMimeType();
            $data['fichier_taille']       = $fichier->getSize();
        }

        $document->update($data);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document mis à jour.');
    }

    public function destroy(Document $document)
    {
        Storage::disk('local')->delete($document->fichier_chemin);
        $document->delete();
        return redirect()->route('documents.index')
            ->with('success', 'Document supprimé.');
    }

    public function download(Document $document)
    {
        $this->autoriserAcces($document);

        abort_if(!Storage::disk('local')->exists($document->fichier_chemin), 404, 'Fichier introuvable.');

        $document->increment('telechargements');

        return Storage::disk('local')->download(
            $document->fichier_chemin,
            $document->fichier_nom_original
        );
    }

    public function archiver(Document $document)
    {
        abort_if($document->statut === 'archive', 422);
        $document->update(['statut' => 'archive']);
        return back()->with('success', 'Document archivé.');
    }

    public function restaurer(Document $document)
    {
        $statut = ($document->date_expiration && $document->date_expiration->isPast()) ? 'expire' : 'actif';
        $document->update(['statut' => $statut]);
        return back()->with('success', 'Document restauré.');
    }

    // ── Gestion des catégories ────────────────────────────────────

    public function categories()
    {
        $categories = DocumentCategorie::withCount('documents')->orderBy('nom')->get();
        return view('documents.categories', compact('categories'));
    }

    public function storeCategorie(Request $request)
    {
        $request->validate([
            'nom'     => 'required|string|max:100|unique:document_categories,nom',
            'couleur' => 'required|in:primary,secondary,success,danger,warning,info,dark',
            'icone'   => 'required|string|max:50',
        ]);
        DocumentCategorie::create($request->only('nom', 'couleur', 'icone', 'description'));
        return back()->with('success', 'Catégorie créée.');
    }

    public function updateCategorie(Request $request, DocumentCategorie $categorie)
    {
        $request->validate([
            'nom'     => 'required|string|max:100|unique:document_categories,nom,' . $categorie->id,
            'couleur' => 'required|in:primary,secondary,success,danger,warning,info,dark',
            'icone'   => 'required|string|max:50',
        ]);
        $categorie->update($request->only('nom', 'couleur', 'icone', 'description'));
        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroyCategorie(DocumentCategorie $categorie)
    {
        abort_if($categorie->documents()->count() > 0, 422, 'Catégorie non vide.');
        $categorie->delete();
        return back()->with('success', 'Catégorie supprimée.');
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function autoriserAcces(Document $document): void
    {
        if ($document->confidentialite === 'confidentiel') {
            abort_unless(auth()->user()->can('documents.confidentiels'), 403);
        }
    }
}
