@extends('layouts.app')

@section('title', 'Bulletins de paie')
@section('page-title', 'Bulletins de paie')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item active">Bulletins de paie</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <span class="text-muted">{{ $bulletins->total() }} bulletin(s)</span>
    @can('rh.bulletins.creer')
    <a href="{{ route('rh.bulletins.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Générer un bulletin
    </a>
    @endcan
</div>

<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <select name="employe_id" class="form-select form-select-sm">
                    <option value="">Tous les employés</option>
                    @foreach($employes as $e)
                        <option value="{{ $e->id }}" @selected(request('employe_id') == $e->id)>{{ $e->nom_complet }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="mois" class="form-select form-select-sm">
                    <option value="">Tous mois</option>
                    @foreach(['1'=>'Janvier','2'=>'Février','3'=>'Mars','4'=>'Avril','5'=>'Mai','6'=>'Juin','7'=>'Juillet','8'=>'Août','9'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'] as $m=>$l)
                        <option value="{{ $m }}" @selected(request('mois') == $m)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="annee" class="form-select form-select-sm">
                    <option value="">Toutes années</option>
                    @foreach($annees as $a)
                        <option value="{{ $a }}" @selected(request('annee') == $a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    <option value="brouillon" @selected(request('statut')=='brouillon')>Brouillon</option>
                    <option value="valide" @selected(request('statut')=='valide')>Validé</option>
                    <option value="paye" @selected(request('statut')=='paye')>Payé</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button class="btn btn-sm btn-secondary flex-fill"><i class="bi bi-search"></i> Filtrer</button>
                <a href="{{ route('rh.bulletins.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Numéro</th>
                    <th>Employé</th>
                    <th>Période</th>
                    <th class="text-end">Salaire brut</th>
                    <th class="text-end">Retenues</th>
                    <th class="text-end">Net à payer</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($bulletins as $b)
                <tr>
                    <td><code>{{ $b->numero }}</code></td>
                    <td>
                        <a href="{{ route('rh.employes.show', $b->employe) }}" class="text-decoration-none fw-semibold">
                            {{ $b->employe->nom_complet }}
                        </a>
                    </td>
                    <td>{{ $b->periode_label }}</td>
                    <td class="text-end">{{ number_format($b->salaire_brut, 0, ',', ' ') }}</td>
                    <td class="text-end text-danger">{{ number_format($b->total_retenues, 0, ',', ' ') }}</td>
                    <td class="text-end fw-bold text-success">{{ number_format($b->net_a_payer, 0, ',', ' ') }}</td>
                    <td><span class="badge bg-{{ $b->statut_badge }}">{{ ucfirst($b->statut) }}</span></td>
                    <td>
                        <a href="{{ route('rh.bulletins.show', $b) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-file-earmark-text fs-3 d-block mb-2"></i>Aucun bulletin trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($bulletins->hasPages())
    <div class="card-footer">{{ $bulletins->links() }}</div>
    @endif
</div>
@endsection
