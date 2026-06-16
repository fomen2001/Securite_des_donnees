<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SAA Biomédical SARL')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* ── Variables couleurs SAA Biomédical ─────────────────── */
        :root {
            --saa-blue:       #0D47A1;
            --saa-blue-mid:   #0A3A8C;
            --saa-blue-dark:  #072870;
            --saa-green:      #2DB84B;
            --saa-green-dark: #1E9438;
            --saa-bg:         #f0f4f8;
            --saa-border:     #e0e8f0;
        }

        /* ── Bootstrap overrides ──────────────────────────────── */
        .btn-primary {
            background-color: var(--saa-blue);
            border-color: var(--saa-blue);
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--saa-blue-mid);
            border-color: var(--saa-blue-mid);
        }
        .btn-success {
            background-color: var(--saa-green);
            border-color: var(--saa-green);
        }
        .btn-success:hover {
            background-color: var(--saa-green-dark);
            border-color: var(--saa-green-dark);
        }
        .btn-outline-primary {
            color: var(--saa-blue);
            border-color: var(--saa-blue);
        }
        .btn-outline-primary:hover {
            background-color: var(--saa-blue);
            border-color: var(--saa-blue);
        }
        .text-primary { color: var(--saa-blue) !important; }
        .bg-primary   { background-color: var(--saa-blue) !important; }
        .border-primary { border-color: var(--saa-blue) !important; }
        .badge.bg-primary { background-color: var(--saa-blue) !important; }
        .badge.bg-success { background-color: var(--saa-green) !important; }
        a { color: var(--saa-blue); }
        a:hover { color: var(--saa-blue-mid); }
        .list-group-item.active {
            background-color: var(--saa-blue);
            border-color: var(--saa-blue);
        }
        .form-check-input:checked {
            background-color: var(--saa-blue);
            border-color: var(--saa-blue);
        }
        .progress-bar { background-color: var(--saa-green); }
        .nav-pills .nav-link.active {
            background-color: var(--saa-blue);
        }

        /* ── Page ─────────────────────────────────────────────── */
        body {
            background-color: var(--saa-bg);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        /* ── Sidebar ──────────────────────────────────────────── */
        .sidebar {
            height: 100vh;
            background: linear-gradient(180deg, var(--saa-blue) 0%, var(--saa-blue-dark) 100%);
            width: 265px;
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 4px 0 20px rgba(7, 40, 112, .25);
        }

        /* Zone logo / marque */
        .sidebar .brand {
            padding: 0;
            border-bottom: 1px solid rgba(255,255,255,.12);
            background: rgba(0,0,0,.15);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            flex-shrink: 0;
        }
        .sidebar .brand .logo-wrap {
            padding: 1rem 1rem .5rem;
        }
        .sidebar .brand .logo-wrap img {
            width: 72px;
            height: 72px;
            object-fit: contain;
            border-radius: 12px;
            background: #fff;
            padding: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,.3);
        }
        .sidebar .brand .logo-fallback {
            width: 72px;
            height: 72px;
            border-radius: 12px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            box-shadow: 0 2px 8px rgba(0,0,0,.3);
        }
        .sidebar .brand .logo-fallback span {
            font-weight: 900;
            font-size: 1.6rem;
            background: linear-gradient(135deg, var(--saa-blue) 40%, var(--saa-green) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
        }
        .sidebar .brand .brand-name {
            color: #fff;
            font-size: .82rem;
            font-weight: 700;
            letter-spacing: .5px;
            padding: 0 .75rem .25rem;
            line-height: 1.2;
        }
        .sidebar .brand .brand-slogan {
            color: rgba(255,255,255,.5);
            font-size: .65rem;
            font-style: italic;
            padding: 0 .75rem .75rem;
        }
        /* Barre verte décorative sous le logo */
        .sidebar .brand::after {
            content: '';
            display: block;
            width: 40px;
            height: 3px;
            background: var(--saa-green);
            border-radius: 2px;
            margin: 0 auto .75rem;
        }

        /* Navigation scrollable */
        .sidebar .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.15) transparent;
            padding-bottom: .5rem;
        }
        .sidebar .sidebar-nav::-webkit-scrollbar { width: 3px; }
        .sidebar .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.2);
            border-radius: 2px;
        }

        /* Sections */
        .sidebar .nav-section {
            padding: .6rem 1.25rem .15rem;
            font-size: .62rem;
            color: rgba(255,255,255,.35);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
        }

        /* Liens */
        .sidebar .nav-link {
            color: rgba(255,255,255,.72);
            padding: .5rem 1.25rem .5rem 1.5rem;
            font-size: .875rem;
            border-radius: 0;
            transition: all .18s ease;
            position: relative;
            border-left: 3px solid transparent;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .sidebar .nav-link .bi {
            font-size: 1rem;
            width: 18px;
            flex-shrink: 0;
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,.09);
            color: #fff;
            border-left-color: rgba(45,184,75,.5);
        }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,.12);
            color: #fff;
            border-left-color: var(--saa-green);
            font-weight: 600;
        }
        .sidebar .nav-link.active .bi {
            color: var(--saa-green);
        }

        /* Bouton déconnexion */
        .sidebar .btn-logout {
            color: rgba(255,255,255,.55);
            padding: .5rem 1.25rem .5rem 1.5rem;
            font-size: .875rem;
            text-align: left;
            width: 100%;
            background: none;
            border: none;
            border-left: 3px solid transparent;
            transition: all .18s;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .sidebar .btn-logout:hover {
            background: rgba(220,53,69,.15);
            color: #ff8080;
            border-left-color: #ff4444;
        }

        /* ── Topbar ───────────────────────────────────────────── */
        .main-content {
            margin-left: 265px;
            min-height: 100vh;
        }

        .topbar {
            background: #fff;
            border-bottom: 3px solid var(--saa-border);
            border-image: linear-gradient(to right, var(--saa-blue) 0%, var(--saa-green) 100%) 1;
            padding: .7rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 500;
            box-shadow: 0 2px 8px rgba(13,71,161,.08);
        }

        .topbar h5 {
            color: var(--saa-blue);
            font-weight: 700;
        }

        /* ── Cartes statistiques ──────────────────────────────── */
        .stat-card {
            border-radius: 12px;
            border: none;
            transition: transform .2s, box-shadow .2s;
            border-left: 4px solid var(--saa-blue);
            background: #fff;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(13,71,161,.12);
        }
        .stat-card.green  { border-left-color: var(--saa-green); }
        .stat-card.blue   { border-left-color: var(--saa-blue); }
        .stat-card.orange { border-left-color: #fd7e14; }
        .stat-card.red    { border-left-color: #dc3545; }

        /* ── Alertes flash ────────────────────────────────────── */
        .alert-banner {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: .5rem 1rem;
            border-radius: 4px;
            margin-bottom: .5rem;
        }

        /* ── Breadcrumb ───────────────────────────────────────── */
        .breadcrumb-item a {
            color: var(--saa-blue);
            text-decoration: none;
        }
        .breadcrumb-item.active { color: #6c757d; }

        /* ── Cards générales ──────────────────────────────────── */
        .card {
            border-radius: 10px;
            border: 1px solid var(--saa-border);
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }
        .card-header {
            background: #f8fafc;
            border-bottom: 1px solid var(--saa-border);
            border-radius: 10px 10px 0 0 !important;
        }

        /* ── Tables ───────────────────────────────────────────── */
        .table thead th {
            color: var(--saa-blue);
            font-size: .8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 2px solid var(--saa-border);
        }
        .table-hover tbody tr:hover {
            background-color: rgba(13,71,161,.04);
        }
    </style>

    @stack('styles')
</head>
<body>

<!-- ════════════════════════════════════════════════════════════
     SIDEBAR
════════════════════════════════════════════════════════════ -->
<nav class="sidebar">

    {{-- Logo / Marque --}}
    @php
        $sidebarLogoFile = \App\Models\Parametre::get('entreprise_logo');
        $sidebarLogoOk   = $sidebarLogoFile && file_exists(public_path('images/' . $sidebarLogoFile));
    @endphp
    <div class="brand">
        <div class="logo-wrap">
            @if($sidebarLogoOk)
                <img src="{{ asset('images/' . $sidebarLogoFile) }}" alt="SAA Biomédical SARL">
            @else
                <div class="logo-fallback">
                    <span>SB</span>
                </div>
            @endif
        </div>
        <div class="brand-name">SAA BIOMÉDICAL SARL</div>
        <div class="brand-slogan">La qualité exige le prix</div>
    </div>

    {{-- Navigation --}}
    <div class="sidebar-nav">
    <ul class="nav flex-column mt-1">

        @can('dashboard.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
               href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i>Tableau de bord
            </a>
        </li>
        @endcan

        {{-- Équipements --}}
        @canany(['equipements.voir', 'mouvements.voir', 'maintenances.voir'])
        <li class="nav-section mt-2">Équipements</li>
        @endcanany
        @can('equipements.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('equipements.*') ? 'active' : '' }}"
               href="{{ route('equipements.index') }}">
                <i class="bi bi-clipboard2-pulse"></i>Inventaire
            </a>
        </li>
        @endcan
        @can('mouvements.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('mouvements.*') ? 'active' : '' }}"
               href="{{ route('mouvements.index') }}">
                <i class="bi bi-arrow-left-right"></i>Mouvements stock
            </a>
        </li>
        @endcan
        @can('maintenances.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('maintenances.*') ? 'active' : '' }}"
               href="{{ route('maintenances.index') }}">
                <i class="bi bi-tools"></i>Maintenances
            </a>
        </li>
        @endcan

        {{-- Ventes --}}
        @canany(['ventes.voir', 'clients.voir'])
        <li class="nav-section mt-2">Ventes & Facturation</li>
        @endcanany
        @can('ventes.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('ventes.*') ? 'active' : '' }}"
               href="{{ route('ventes.index') }}">
                <i class="bi bi-receipt"></i>Ventes
            </a>
        </li>
        @endcan
        @can('clients.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}"
               href="{{ route('clients.index') }}">
                <i class="bi bi-people"></i>Clients
            </a>
        </li>
        @endcan

        {{-- Achats --}}
        @canany(['achats.voir', 'achats.commandes.creer', 'achats.receptions.creer', 'achats.livraisons.creer'])
        <li class="nav-section mt-2">Achats</li>
        @endcanany
        @can('achats.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('achats.commandes.*') ? 'active' : '' }}"
               href="{{ route('achats.commandes.index') }}">
                <i class="bi bi-cart3"></i>Bons de commande
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('achats.receptions.*') ? 'active' : '' }}"
               href="{{ route('achats.receptions.index') }}">
                <i class="bi bi-box-arrow-in-down"></i>Bons de réception
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('achats.livraisons.*') ? 'active' : '' }}"
               href="{{ route('achats.livraisons.index') }}">
                <i class="bi bi-truck"></i>Bons de livraison
            </a>
        </li>
        @endcan

        {{-- Référentiels --}}
        @canany(['referentiels.voir', 'fournisseurs.voir'])
        <li class="nav-section mt-2">Référentiels</li>
        @endcanany
        @can('referentiels.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"
               href="{{ route('categories.index') }}">
                <i class="bi bi-tags"></i>Catégories
            </a>
        </li>
        @endcan
        @can('fournisseurs.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('fournisseurs.*') ? 'active' : '' }}"
               href="{{ route('fournisseurs.index') }}">
                <i class="bi bi-building-gear"></i>Fournisseurs
            </a>
        </li>
        @endcan
        @can('referentiels.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}"
               href="{{ route('services.index') }}">
                <i class="bi bi-building"></i>Services
            </a>
        </li>
        @endcan

        {{-- Secrétariat --}}
        @can('secretariat.voir')
        <li class="nav-section mt-2">Secrétariat</li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('secretariat.visiteurs.*') ? 'active' : '' }}"
               href="{{ route('secretariat.visiteurs.index') }}">
                <i class="bi bi-person-badge"></i>Registre visiteurs
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('secretariat.messages.*') ? 'active' : '' }}"
               href="{{ route('secretariat.messages.index') }}">
                <i class="bi bi-envelope-paper"></i>Messagerie clients
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('secretariat.reunions.*') ? 'active' : '' }}"
               href="{{ route('secretariat.reunions.index') }}">
                <i class="bi bi-journal-richtext"></i>Rapports de réunion
            </a>
        </li>
        @endcan

        {{-- Documents --}}
        @can('documents.voir')
        <li class="nav-section mt-2">Documents</li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('documents.*') ? 'active' : '' }}"
               href="{{ route('documents.index') }}">
                <i class="bi bi-folder2-open"></i>Gestion documentaire
            </a>
        </li>
        @endcan

        {{-- RH --}}
        @canany(['rh.voir', 'rh.employes.voir', 'rh.bulletins.voir'])
        <li class="nav-section mt-2">Ressources Humaines</li>
        @endcanany
        @can('rh.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('rh.dashboard') ? 'active' : '' }}"
               href="{{ route('rh.dashboard') }}">
                <i class="bi bi-people-fill"></i>Tableau de bord RH
            </a>
        </li>
        @endcan
        @can('rh.employes.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('rh.employes.*') ? 'active' : '' }}"
               href="{{ route('rh.employes.index') }}">
                <i class="bi bi-person-badge"></i>Employés
            </a>
        </li>
        @endcan
        @can('rh.bulletins.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('rh.bulletins.*') ? 'active' : '' }}"
               href="{{ route('rh.bulletins.index') }}">
                <i class="bi bi-file-earmark-text"></i>Bulletins de paie
            </a>
        </li>
        @endcan
        @can('rh.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('rh.conges.*') ? 'active' : '' }}"
               href="{{ route('rh.conges.index') }}">
                <i class="bi bi-calendar-x"></i>Congés
            </a>
        </li>
        @endcan
        @can('rh.employes.modifier')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('rh.avances.*') ? 'active' : '' }}"
               href="{{ route('rh.avances.index') }}">
                <i class="bi bi-cash-coin"></i>Avances sur salaire
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('rh.revisions.*') ? 'active' : '' }}"
               href="{{ route('rh.revisions.index') }}">
                <i class="bi bi-graph-up-arrow"></i>Révisions salariales
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('rh.masse.*') ? 'active' : '' }}"
               href="{{ route('rh.masse.create') }}">
                <i class="bi bi-lightning-charge"></i>Paie en masse
            </a>
        </li>
        @endcan

        {{-- Finance --}}
        @canany(['finance.voir', 'finance.depenses.creer', 'finance.rapports.voir'])
        <li class="nav-section mt-2">Finance</li>
        @endcanany
        @can('finance.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('finance.dashboard') ? 'active' : '' }}"
               href="{{ route('finance.dashboard') }}">
                <i class="bi bi-bar-chart-line-fill"></i>Tableau de bord Finance
            </a>
        </li>
        @endcan
        @can('finance.depenses.creer')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('finance.depenses.*') ? 'active' : '' }}"
               href="{{ route('finance.depenses.index') }}">
                <i class="bi bi-wallet2"></i>Dépenses
            </a>
        </li>
        @endcan
        @can('finance.rapports.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('finance.rapports.*') ? 'active' : '' }}"
               href="{{ route('finance.rapports.index') }}">
                <i class="bi bi-file-earmark-bar-graph"></i>Rapport P&amp;L
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('finance.tresorerie') ? 'active' : '' }}"
               href="{{ route('finance.tresorerie') }}">
                <i class="bi bi-cash-stack"></i>Trésorerie
            </a>
        </li>
        @endcan

        {{-- Fiscalité --}}
        @canany(['impots.voir', 'impots.tva.voir', 'impots.is.voir', 'impots.bilan.voir'])
        <li class="nav-section mt-2">Fiscalité</li>
        @endcanany
        @can('impots.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('impots.dashboard') ? 'active' : '' }}"
               href="{{ route('impots.dashboard') }}">
                <i class="bi bi-journal-bookmark-fill"></i>Tableau de bord Fiscal
            </a>
        </li>
        @endcan
        @can('impots.tva.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('impots.tva.*') ? 'active' : '' }}"
               href="{{ route('impots.tva.index') }}">
                <i class="bi bi-percent"></i>Déclarations TVA
            </a>
        </li>
        @endcan
        @can('impots.is.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('impots.is.*') ? 'active' : '' }}"
               href="{{ route('impots.is.index') }}">
                <i class="bi bi-bank"></i>Impôt sur les Sociétés
            </a>
        </li>
        @endcan
        @can('impots.bilan.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('impots.bilan.*') ? 'active' : '' }}"
               href="{{ route('impots.bilan.index') }}">
                <i class="bi bi-journal-text"></i>Bilan SYSCOHADA
            </a>
        </li>
        @endcan
        @can('impots.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('impots.patente.*') ? 'active' : '' }}"
               href="{{ route('impots.patente.index') }}">
                <i class="bi bi-award"></i>Patente
            </a>
        </li>
        @endcan

        {{-- Configuration --}}
        @can('parametres.gerer')
        <li class="nav-section mt-2">Configuration</li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('parametres.*') ? 'active' : '' }}"
               href="{{ route('parametres.index') }}">
                <i class="bi bi-gear"></i>Paramètres
            </a>
        </li>
        @endcan

        {{-- Administration --}}
        @role('admin')
        <li class="nav-section mt-2">Administration</li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.utilisateurs.*') ? 'active' : '' }}"
               href="{{ route('admin.utilisateurs.index') }}">
                <i class="bi bi-shield-person"></i>Utilisateurs & Rôles
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}"
               href="{{ route('admin.logs.index') }}">
                <i class="bi bi-clock-history"></i>Journal d'activité
            </a>
        </li>
        @endrole

        {{-- Déconnexion --}}
        <li class="nav-section mt-2">Compte</li>
        <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="bi bi-box-arrow-left"></i>Déconnexion
                </button>
            </form>
        </li>

    </ul>
    </div>
</nav>

<!-- ════════════════════════════════════════════════════════════
     CONTENU PRINCIPAL
════════════════════════════════════════════════════════════ -->
<div class="main-content">

    {{-- Topbar --}}
    <div class="topbar d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-0">@yield('page-title', 'Tableau de bord')</h5>
            @hasSection('breadcrumb')
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">@yield('breadcrumb')</ol>
                </nav>
            @endif
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:34px;height:34px;background:linear-gradient(135deg,var(--saa-blue),var(--saa-green));color:#fff;font-weight:700;font-size:.85rem;flex-shrink:0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <span class="text-muted small d-none d-md-inline">{{ auth()->user()->name }}</span>
            </div>
            @can('equipements.creer')
            <a href="{{ route('equipements.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Nouvel équipement
            </a>
            @endcan
        </div>
    </div>

    {{-- Contenu --}}
    <div class="p-4">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
