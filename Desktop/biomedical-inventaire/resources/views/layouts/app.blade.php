<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Inventaire Biomédical')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background-color: #f0f4f8; }

        .sidebar {
            height: 100vh;
            background: linear-gradient(180deg, #1a3a5c 0%, #0d2137 100%);
            width: 260px;
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            padding-top: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.2) transparent;
        }

        .sidebar .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.2);
            border-radius: 2px;
        }

        .sidebar .brand {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
            background: rgba(255,255,255,.05);
        }

        .sidebar .brand h6 { color: #7ec8e3; font-size: .7rem; letter-spacing: 1px; text-transform: uppercase; }
        .sidebar .brand strong { color: #fff; font-size: 1.1rem; }

        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            padding: .55rem 1.5rem;
            font-size: .9rem;
            border-radius: 0;
            transition: background .2s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,.12);
            color: #fff;
        }
        .sidebar .nav-link .bi { width: 20px; margin-right: 8px; }

        .sidebar .nav-section {
            padding: .4rem 1.5rem .2rem;
            font-size: .7rem;
            color: rgba(255,255,255,.4);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        .topbar {
            background: #fff;
            border-bottom: 1px solid #e0e6ed;
            padding: .75rem 1.5rem;
        }

        .stat-card {
            border-radius: 12px;
            border: none;
            transition: transform .2s;
        }
        .stat-card:hover { transform: translateY(-2px); }

        .alert-banner {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: .5rem 1rem;
            border-radius: 4px;
            margin-bottom: .5rem;
        }
    </style>

    @stack('styles')
</head>
<body>

<!-- Sidebar -->
<nav class="sidebar">
    <div class="brand">
        <h6 class="mb-0">Gestion</h6>
        <strong><i class="bi bi-hospital me-1"></i>Inventaire Biomédical</strong>
    </div>

    <div class="sidebar-nav">
    <ul class="nav flex-column mt-2">
        @can('dashboard.voir')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
               href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i>Tableau de bord
            </a>
        </li>
        @endcan

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
                <i class="bi bi-truck"></i>Fournisseurs
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
                <i class="bi bi-receipt"></i>Dépenses
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

        @can('parametres.gerer')
        <li class="nav-section mt-2">Configuration</li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('parametres.*') ? 'active' : '' }}"
               href="{{ route('parametres.index') }}">
                <i class="bi bi-gear"></i>Paramètres
            </a>
        </li>
        @endcan

        @role('admin')
        <li class="nav-section mt-2">Administration</li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.utilisateurs.*') ? 'active' : '' }}"
               href="{{ route('admin.utilisateurs.index') }}">
                <i class="bi bi-people-fill"></i>Utilisateurs & Rôles
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}"
               href="{{ route('admin.logs.index') }}">
                <i class="bi bi-clock-history"></i>Journal d'activité
            </a>
        </li>
        @endrole

        <li class="nav-section mt-2">Compte</li>
        <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link btn btn-link text-start w-100">
                    <i class="bi bi-box-arrow-left"></i>Déconnexion
                </button>
            </form>
        </li>
    </ul>
    </div>
</nav>

<!-- Main content -->
<div class="main-content">
    <div class="topbar d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-0 fw-semibold text-dark">@yield('page-title', 'Tableau de bord')</h5>
            @hasSection('breadcrumb')
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">@yield('breadcrumb')</ol>
                </nav>
            @endif
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small">{{ auth()->user()->name }}</span>
            <a href="{{ route('equipements.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Nouvel équipement
            </a>
        </div>
    </div>

    <div class="p-4">
        {{-- Alertes flash --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <ul class="mb-0">
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
