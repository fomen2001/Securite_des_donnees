<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ParametreController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipementController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MouvementStockController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VenteController;
use App\Http\Controllers\RH\RhDashboardController;
use App\Http\Controllers\RH\EmployeController;
use App\Http\Controllers\RH\BulletinPaieController;
use App\Http\Controllers\RH\CongeController;
use App\Http\Controllers\RH\AvanceSalaireController;
use App\Http\Controllers\RH\RevisionSalaireController;
use App\Http\Controllers\RH\MasseController;
use App\Http\Controllers\Finance\FinanceDashboardController;
use App\Http\Controllers\Finance\DepenseController;
use App\Http\Controllers\Finance\RapportController;
use App\Http\Controllers\Impots\ImpotDashboardController;
use App\Http\Controllers\Impots\DeclarationTVAController;
use App\Http\Controllers\Impots\DeclarationISController;
use App\Http\Controllers\Impots\BilanComptableController;
use App\Http\Controllers\Impots\PatenteController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Achats\BonCommandeController;
use App\Http\Controllers\Achats\BonReceptionController;
use App\Http\Controllers\Achats\BonLivraisonController;
use App\Http\Controllers\Secretariat\VisiteurController;
use App\Http\Controllers\Secretariat\MessageController;
use App\Http\Controllers\Secretariat\RapportReunionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function () {

    // Tableau de bord
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.voir')
        ->name('dashboard');

    // Équipements
    Route::get('/equipements/export', [EquipementController::class, 'export'])
        ->middleware('permission:equipements.exporter')
        ->name('equipements.export');
    Route::resource('equipements', EquipementController::class)
        ->middleware([
            'index'   => 'permission:equipements.voir',
            'show'    => 'permission:equipements.voir',
            'create'  => 'permission:equipements.creer',
            'store'   => 'permission:equipements.creer',
            'edit'    => 'permission:equipements.modifier',
            'update'  => 'permission:equipements.modifier',
            'destroy' => 'permission:equipements.supprimer',
        ]);

    // Mouvements de stock
    Route::resource('mouvements', MouvementStockController::class)
        ->only(['index', 'create', 'store'])
        ->middleware([
            'index'  => 'permission:mouvements.voir',
            'create' => 'permission:mouvements.creer',
            'store'  => 'permission:mouvements.creer',
        ]);

    // Maintenances
    Route::resource('maintenances', MaintenanceController::class)
        ->middleware([
            'index'   => 'permission:maintenances.voir',
            'show'    => 'permission:maintenances.voir',
            'create'  => 'permission:maintenances.creer',
            'store'   => 'permission:maintenances.creer',
            'edit'    => 'permission:maintenances.modifier',
            'update'  => 'permission:maintenances.modifier',
            'destroy' => 'permission:maintenances.supprimer',
        ]);

    // Catégories
    Route::get('/categories', [CategorieController::class, 'index'])
        ->middleware('permission:referentiels.voir')
        ->name('categories.index');
    Route::post('/categories', [CategorieController::class, 'store'])
        ->middleware('permission:referentiels.gerer')
        ->name('categories.store');
    Route::put('/categories/{categorie}', [CategorieController::class, 'update'])
        ->middleware('permission:referentiels.gerer')
        ->name('categories.update');
    Route::delete('/categories/{categorie}', [CategorieController::class, 'destroy'])
        ->middleware('permission:referentiels.gerer')
        ->name('categories.destroy');

    // Fournisseurs
    Route::resource('fournisseurs', FournisseurController::class)
        ->middleware([
            'index'   => 'permission:fournisseurs.voir',
            'show'    => 'permission:fournisseurs.voir',
            'create'  => 'permission:fournisseurs.gerer',
            'store'   => 'permission:fournisseurs.gerer',
            'edit'    => 'permission:fournisseurs.gerer',
            'update'  => 'permission:fournisseurs.gerer',
            'destroy' => 'permission:fournisseurs.gerer',
        ]);

    // Clients
    Route::resource('clients', ClientController::class)
        ->middleware([
            'index'   => 'permission:clients.voir',
            'show'    => 'permission:clients.voir',
            'create'  => 'permission:clients.creer',
            'store'   => 'permission:clients.creer',
            'edit'    => 'permission:clients.modifier',
            'update'  => 'permission:clients.modifier',
            'destroy' => 'permission:clients.supprimer',
        ]);

    // Ventes & Facturation
    Route::resource('ventes', VenteController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->middleware([
            'index'  => 'permission:ventes.voir',
            'show'   => 'permission:ventes.voir',
            'create' => 'permission:ventes.creer',
            'store'  => 'permission:ventes.creer',
        ]);
    Route::get('/ventes/{vente}/facture', [VenteController::class, 'facture'])
        ->middleware('permission:ventes.voir')
        ->name('ventes.facture');
    Route::post('/ventes/{vente}/paiement', [VenteController::class, 'paiement'])
        ->middleware('permission:ventes.paiement')
        ->name('ventes.paiement');
    Route::post('/ventes/{vente}/annuler', [VenteController::class, 'annuler'])
        ->middleware('permission:ventes.annuler')
        ->name('ventes.annuler');
    Route::post('/ventes/{vente}/livrer', [VenteController::class, 'livrer'])
        ->middleware('permission:ventes.livrer')
        ->name('ventes.livrer');

    // Paramètres
    Route::get('/parametres', [ParametreController::class, 'index'])
        ->middleware('permission:parametres.gerer')
        ->name('parametres.index');
    Route::post('/parametres', [ParametreController::class, 'update'])
        ->middleware('permission:parametres.gerer')
        ->name('parametres.update');

    // Services hospitaliers
    Route::get('/services', [ServiceController::class, 'index'])
        ->middleware('permission:referentiels.voir')
        ->name('services.index');
    Route::post('/services', [ServiceController::class, 'store'])
        ->middleware('permission:referentiels.gerer')
        ->name('services.store');
    Route::put('/services/{service}', [ServiceController::class, 'update'])
        ->middleware('permission:referentiels.gerer')
        ->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])
        ->middleware('permission:referentiels.gerer')
        ->name('services.destroy');

    // ── Module RH ─────────────────────────────────────────────────
    Route::prefix('rh')->name('rh.')->group(function () {

        // Tableau de bord RH
        Route::get('/', [RhDashboardController::class, 'index'])
            ->middleware('permission:rh.voir')
            ->name('dashboard');

        // Employés
        Route::resource('employes', EmployeController::class)
            ->middleware([
                'index'   => 'permission:rh.employes.voir',
                'show'    => 'permission:rh.employes.voir',
                'create'  => 'permission:rh.employes.creer',
                'store'   => 'permission:rh.employes.creer',
                'edit'    => 'permission:rh.employes.modifier',
                'update'  => 'permission:rh.employes.modifier',
                'destroy' => 'permission:rh.employes.supprimer',
            ]);
        Route::post('/employes/{employe}/crediter-conge', [EmployeController::class, 'crediterConge'])
            ->middleware('permission:rh.conges.gerer')
            ->name('employes.crediter-conge');

        // Bulletins de paie
        Route::resource('bulletins', BulletinPaieController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->middleware([
                'index'  => 'permission:rh.bulletins.voir',
                'show'   => 'permission:rh.bulletins.voir',
                'create' => 'permission:rh.bulletins.creer',
                'store'  => 'permission:rh.bulletins.creer',
            ]);
        Route::post('/bulletins/{bulletin}/valider', [BulletinPaieController::class, 'valider'])
            ->middleware('permission:rh.bulletins.valider')
            ->name('bulletins.valider');
        Route::post('/bulletins/{bulletin}/payer', [BulletinPaieController::class, 'payer'])
            ->middleware('permission:rh.bulletins.payer')
            ->name('bulletins.payer');
        Route::post('/bulletins/simuler', [BulletinPaieController::class, 'simuler'])
            ->middleware('permission:rh.bulletins.creer')
            ->name('bulletins.simuler');

        // Congés
        Route::resource('conges', CongeController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->middleware([
                'index'  => 'permission:rh.voir',
                'show'   => 'permission:rh.voir',
                'create' => 'permission:rh.voir',
                'store'  => 'permission:rh.voir',
            ]);
        Route::post('/conges/{conge}/approuver', [CongeController::class, 'approuver'])
            ->middleware('permission:rh.conges.gerer')
            ->name('conges.approuver');
        Route::post('/conges/{conge}/refuser', [CongeController::class, 'refuser'])
            ->middleware('permission:rh.conges.gerer')
            ->name('conges.refuser');
        Route::post('/conges/{conge}/annuler', [CongeController::class, 'annuler'])
            ->middleware('permission:rh.voir')
            ->name('conges.annuler');

        // Avances sur salaire
        Route::resource('avances', AvanceSalaireController::class)
            ->only(['index', 'create', 'store'])
            ->middleware([
                'index'  => 'permission:rh.employes.voir',
                'create' => 'permission:rh.employes.modifier',
                'store'  => 'permission:rh.employes.modifier',
            ]);
        Route::post('/avances/{avance}/approuver', [AvanceSalaireController::class, 'approuver'])
            ->middleware('permission:rh.conges.gerer')
            ->name('avances.approuver');
        Route::post('/avances/{avance}/annuler', [AvanceSalaireController::class, 'annuler'])
            ->middleware('permission:rh.conges.gerer')
            ->name('avances.annuler');

        // Révisions salariales
        Route::resource('revisions', RevisionSalaireController::class)
            ->only(['index', 'create', 'store'])
            ->middleware([
                'index'  => 'permission:rh.employes.voir',
                'create' => 'permission:rh.employes.modifier',
                'store'  => 'permission:rh.employes.modifier',
            ]);

        // Génération en masse
        Route::get('/masse', [MasseController::class, 'create'])
            ->middleware('permission:rh.bulletins.creer')
            ->name('masse.create');
        Route::post('/masse', [MasseController::class, 'store'])
            ->middleware('permission:rh.bulletins.creer')
            ->name('masse.store');
    });

    // ── Module Finance ────────────────────────────────────────────
    Route::prefix('finance')->name('finance.')->group(function () {

        // Tableau de bord Finance
        Route::get('/', [FinanceDashboardController::class, 'index'])
            ->middleware('permission:finance.voir')
            ->name('dashboard');

        // Dépenses
        Route::resource('depenses', DepenseController::class)
            ->middleware([
                'index'   => 'permission:finance.depenses.creer',
                'show'    => 'permission:finance.depenses.creer',
                'create'  => 'permission:finance.depenses.creer',
                'store'   => 'permission:finance.depenses.creer',
                'edit'    => 'permission:finance.depenses.modifier',
                'update'  => 'permission:finance.depenses.modifier',
                'destroy' => 'permission:finance.depenses.modifier',
            ]);
        Route::post('/depenses/{depense}/approuver', [DepenseController::class, 'approuver'])
            ->middleware('permission:finance.depenses.approuver')
            ->name('depenses.approuver');
        Route::post('/depenses/{depense}/payer', [DepenseController::class, 'payer'])
            ->middleware('permission:finance.depenses.approuver')
            ->name('depenses.payer');
        Route::post('/depenses/{depense}/rejeter', [DepenseController::class, 'rejeter'])
            ->middleware('permission:finance.depenses.approuver')
            ->name('depenses.rejeter');

        // Rapports
        Route::get('/rapports', [RapportController::class, 'index'])
            ->middleware('permission:finance.rapports.voir')
            ->name('rapports.index');
        Route::get('/tresorerie', [RapportController::class, 'tresorerie'])
            ->middleware('permission:finance.rapports.voir')
            ->name('tresorerie');
    });

    // ── Module Impôts ─────────────────────────────────────────────
    Route::prefix('impots')->name('impots.')->group(function () {

        Route::get('/', [ImpotDashboardController::class, 'index'])
            ->middleware('permission:impots.voir')
            ->name('dashboard');

        // TVA
        Route::get('/tva', [DeclarationTVAController::class, 'index'])
            ->middleware('permission:impots.tva.voir')->name('tva.index');
        Route::get('/tva/creer', [DeclarationTVAController::class, 'create'])
            ->middleware('permission:impots.tva.gerer')->name('tva.create');
        Route::post('/tva', [DeclarationTVAController::class, 'store'])
            ->middleware('permission:impots.tva.gerer')->name('tva.store');
        Route::get('/tva/{tva}', [DeclarationTVAController::class, 'show'])
            ->middleware('permission:impots.tva.voir')->name('tva.show');
        Route::post('/tva/{tva}/soumettre', [DeclarationTVAController::class, 'soumettre'])
            ->middleware('permission:impots.tva.gerer')->name('tva.soumettre');
        Route::post('/tva/{tva}/payer', [DeclarationTVAController::class, 'payer'])
            ->middleware('permission:impots.tva.gerer')->name('tva.payer');

        // IS
        Route::get('/is', [DeclarationISController::class, 'index'])
            ->middleware('permission:impots.is.voir')->name('is.index');
        Route::get('/is/creer', [DeclarationISController::class, 'create'])
            ->middleware('permission:impots.is.gerer')->name('is.create');
        Route::post('/is', [DeclarationISController::class, 'store'])
            ->middleware('permission:impots.is.gerer')->name('is.store');
        Route::get('/is/{is}', [DeclarationISController::class, 'show'])
            ->middleware('permission:impots.is.voir')->name('is.show');
        Route::post('/is/{is}/soumettre', [DeclarationISController::class, 'soumettre'])
            ->middleware('permission:impots.is.gerer')->name('is.soumettre');
        Route::post('/is/{is}/payer', [DeclarationISController::class, 'payer'])
            ->middleware('permission:impots.is.gerer')->name('is.payer');

        // Bilan comptable
        Route::get('/bilan', [BilanComptableController::class, 'index'])
            ->middleware('permission:impots.bilan.voir')->name('bilan.index');
        Route::get('/bilan/creer', [BilanComptableController::class, 'create'])
            ->middleware('permission:impots.bilan.gerer')->name('bilan.create');
        Route::post('/bilan', [BilanComptableController::class, 'store'])
            ->middleware('permission:impots.bilan.gerer')->name('bilan.store');
        Route::get('/bilan/{exercice}', [BilanComptableController::class, 'show'])
            ->middleware('permission:impots.bilan.voir')->name('bilan.show');
        Route::post('/bilan/{exercice}/valider', [BilanComptableController::class, 'valider'])
            ->middleware('permission:impots.bilan.gerer')->name('bilan.valider');
        Route::post('/bilan/{exercice}/deposer', [BilanComptableController::class, 'deposer'])
            ->middleware('permission:impots.bilan.gerer')->name('bilan.deposer');

        // Patente
        Route::get('/patente', [PatenteController::class, 'index'])
            ->middleware('permission:impots.voir')->name('patente.index');
        Route::get('/patente/creer', [PatenteController::class, 'create'])
            ->middleware('permission:impots.tva.gerer')->name('patente.create');
        Route::post('/patente', [PatenteController::class, 'store'])
            ->middleware('permission:impots.tva.gerer')->name('patente.store');
        Route::post('/patente/{patente}/soumettre', [PatenteController::class, 'soumettre'])
            ->middleware('permission:impots.tva.gerer')->name('patente.soumettre');
        Route::post('/patente/{patente}/payer', [PatenteController::class, 'payer'])
            ->middleware('permission:impots.tva.gerer')->name('patente.payer');
        Route::post('/patente/calculer', [PatenteController::class, 'calculer'])
            ->middleware('permission:impots.voir')->name('patente.calculer');
    });

    // ── Module GED (Gestion Électronique de Documents) ────────────
    Route::prefix('documents')->name('documents.')->middleware('permission:documents.voir')->group(function () {

        Route::get('/',                [DocumentController::class, 'index'])  ->name('index');
        Route::get('/ajouter',         [DocumentController::class, 'create']) ->middleware('permission:documents.creer')->name('create');
        Route::post('/',               [DocumentController::class, 'store'])  ->middleware('permission:documents.creer')->name('store');
        Route::get('/{document}',      [DocumentController::class, 'show'])   ->name('show');
        Route::get('/{document}/modifier', [DocumentController::class, 'edit'])   ->middleware('permission:documents.modifier')->name('edit');
        Route::put('/{document}',      [DocumentController::class, 'update']) ->middleware('permission:documents.modifier')->name('update');
        Route::delete('/{document}',   [DocumentController::class, 'destroy'])->middleware('permission:documents.supprimer')->name('destroy');
        Route::get('/{document}/telecharger', [DocumentController::class, 'download'])->name('download');
        Route::patch('/{document}/archiver',  [DocumentController::class, 'archiver']) ->middleware('permission:documents.modifier')->name('archiver');
        Route::patch('/{document}/restaurer', [DocumentController::class, 'restaurer'])->middleware('permission:documents.modifier')->name('restaurer');

        // Catégories
        Route::get('/categories/gerer',       [DocumentController::class, 'categories'])    ->middleware('permission:documents.gerer')->name('categories');
        Route::post('/categories',             [DocumentController::class, 'storeCategorie'])->middleware('permission:documents.gerer')->name('categories.store');
        Route::put('/categories/{categorie}',  [DocumentController::class, 'updateCategorie'])->middleware('permission:documents.gerer')->name('categories.update');
        Route::delete('/categories/{categorie}',[DocumentController::class, 'destroyCategorie'])->middleware('permission:documents.gerer')->name('categories.destroy');
    });

    // ── Module Achats (Bons de commande, réception, livraison) ───
    Route::prefix('achats')->name('achats.')->group(function () {

        // Bons de commande
        Route::get('/commandes', [BonCommandeController::class, 'index'])
            ->middleware('permission:achats.voir')->name('commandes.index');
        Route::get('/commandes/creer', [BonCommandeController::class, 'create'])
            ->middleware('permission:achats.commandes.creer')->name('commandes.create');
        Route::post('/commandes', [BonCommandeController::class, 'store'])
            ->middleware('permission:achats.commandes.creer')->name('commandes.store');
        Route::get('/commandes/{bonCommande}', [BonCommandeController::class, 'show'])
            ->middleware('permission:achats.voir')->name('commandes.show');
        Route::patch('/commandes/{bonCommande}/confirmer', [BonCommandeController::class, 'confirmer'])
            ->middleware('permission:achats.commandes.modifier')->name('commandes.confirmer');
        Route::patch('/commandes/{bonCommande}/annuler', [BonCommandeController::class, 'annuler'])
            ->middleware('permission:achats.commandes.annuler')->name('commandes.annuler');

        // Bons de réception
        Route::get('/receptions', [BonReceptionController::class, 'index'])
            ->middleware('permission:achats.voir')->name('receptions.index');
        Route::get('/receptions/creer', [BonReceptionController::class, 'create'])
            ->middleware('permission:achats.receptions.creer')->name('receptions.create');
        Route::post('/receptions', [BonReceptionController::class, 'store'])
            ->middleware('permission:achats.receptions.creer')->name('receptions.store');
        Route::get('/receptions/{bonReception}', [BonReceptionController::class, 'show'])
            ->middleware('permission:achats.voir')->name('receptions.show');
        Route::patch('/receptions/{bonReception}/valider', [BonReceptionController::class, 'valider'])
            ->middleware('permission:achats.receptions.valider')->name('receptions.valider');
        Route::patch('/receptions/{bonReception}/rejeter', [BonReceptionController::class, 'rejeter'])
            ->middleware('permission:achats.receptions.valider')->name('receptions.rejeter');

        // Bons de livraison
        Route::get('/livraisons', [BonLivraisonController::class, 'index'])
            ->middleware('permission:achats.voir')->name('livraisons.index');
        Route::get('/livraisons/creer', [BonLivraisonController::class, 'create'])
            ->middleware('permission:achats.livraisons.creer')->name('livraisons.create');
        Route::post('/livraisons', [BonLivraisonController::class, 'store'])
            ->middleware('permission:achats.livraisons.creer')->name('livraisons.store');
        Route::get('/livraisons/{bonLivraison}', [BonLivraisonController::class, 'show'])
            ->middleware('permission:achats.voir')->name('livraisons.show');
        Route::patch('/livraisons/{bonLivraison}/expedier', [BonLivraisonController::class, 'expedier'])
            ->middleware('permission:achats.livraisons.expedier')->name('livraisons.expedier');
        Route::patch('/livraisons/{bonLivraison}/livrer', [BonLivraisonController::class, 'livrer'])
            ->middleware('permission:achats.livraisons.expedier')->name('livraisons.livrer');
        Route::patch('/livraisons/{bonLivraison}/annuler', [BonLivraisonController::class, 'annuler'])
            ->middleware('permission:achats.livraisons.creer')->name('livraisons.annuler');
    });

    // ── Module Secrétariat ───────────────────────────────────────
    Route::prefix('secretariat')->name('secretariat.')->middleware('permission:secretariat.voir')->group(function () {
        // Visiteurs
        Route::get('/visiteurs',                    [VisiteurController::class, 'index'])   ->name('visiteurs.index');
        Route::get('/visiteurs/nouveau',            [VisiteurController::class, 'create'])  ->middleware('permission:secretariat.visiteurs.gerer')->name('visiteurs.create');
        Route::post('/visiteurs',                   [VisiteurController::class, 'store'])   ->middleware('permission:secretariat.visiteurs.gerer')->name('visiteurs.store');
        Route::get('/visiteurs/{visiteur}',         [VisiteurController::class, 'show'])    ->name('visiteurs.show');
        Route::patch('/visiteurs/{visiteur}/recevoir', [VisiteurController::class, 'recevoir'])->middleware('permission:secretariat.visiteurs.gerer')->name('visiteurs.recevoir');
        Route::patch('/visiteurs/{visiteur}/sortir',   [VisiteurController::class, 'sortir'])  ->middleware('permission:secretariat.visiteurs.gerer')->name('visiteurs.sortir');
        Route::patch('/visiteurs/{visiteur}/annuler',  [VisiteurController::class, 'annuler']) ->middleware('permission:secretariat.visiteurs.gerer')->name('visiteurs.annuler');

        // Messages clients
        Route::get('/messages',                        [MessageController::class, 'index'])  ->name('messages.index');
        Route::get('/messages/nouveau',                [MessageController::class, 'create']) ->middleware('permission:secretariat.messages.envoyer')->name('messages.create');
        Route::post('/messages',                       [MessageController::class, 'store'])  ->middleware('permission:secretariat.messages.envoyer')->name('messages.store');
        Route::get('/messages/{message}',              [MessageController::class, 'show'])   ->name('messages.show');
        Route::post('/messages/{message}/envoyer',                  [MessageController::class, 'envoyer'])               ->middleware('permission:secretariat.messages.envoyer')->name('messages.envoyer');
        Route::delete('/messages/{message}',                        [MessageController::class, 'destroy'])               ->middleware('permission:secretariat.messages.envoyer')->name('messages.destroy');
        Route::get('/messages/pieces/{piece}/telecharger',          [MessageController::class, 'telechargerPieceJointe'])->name('messages.pieces.telecharger');
        Route::delete('/messages/pieces/{piece}',                   [MessageController::class, 'supprimerPieceJointe'])  ->middleware('permission:secretariat.messages.envoyer')->name('messages.pieces.supprimer');

        // Rapports de réunion
        Route::get('/reunions',                        [RapportReunionController::class, 'index'])    ->name('reunions.index');
        Route::get('/reunions/nouveau',                [RapportReunionController::class, 'create'])   ->middleware('permission:secretariat.reunions.gerer')->name('reunions.create');
        Route::post('/reunions',                       [RapportReunionController::class, 'store'])    ->middleware('permission:secretariat.reunions.gerer')->name('reunions.store');
        Route::get('/reunions/{reunion}',              [RapportReunionController::class, 'show'])     ->name('reunions.show');
        Route::get('/reunions/{reunion}/modifier',     [RapportReunionController::class, 'edit'])     ->middleware('permission:secretariat.reunions.gerer')->name('reunions.edit');
        Route::put('/reunions/{reunion}',              [RapportReunionController::class, 'update'])   ->middleware('permission:secretariat.reunions.gerer')->name('reunions.update');
        Route::delete('/reunions/{reunion}',           [RapportReunionController::class, 'destroy'])  ->middleware('permission:secretariat.reunions.gerer')->name('reunions.destroy');
        Route::get('/reunions/{reunion}/imprimer',     [RapportReunionController::class, 'imprimer']) ->name('reunions.imprimer');
    });

    // ── Administration (admin uniquement) ────────────────────────
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::resource('utilisateurs', UserController::class)
            ->except(['show']);
        Route::get('/logs', [ActivityController::class, 'index'])->name('logs.index');
    });
});

require __DIR__.'/auth.php';
