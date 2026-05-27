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

    // ── Administration (admin uniquement) ────────────────────────
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::resource('utilisateurs', UserController::class)
            ->except(['show']);
        Route::get('/logs', [ActivityController::class, 'index'])->name('logs.index');
    });
});

require __DIR__.'/auth.php';
