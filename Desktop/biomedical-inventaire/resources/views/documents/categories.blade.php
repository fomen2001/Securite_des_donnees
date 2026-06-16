@extends('layouts.app')

@section('title', 'Catégories de documents')
@section('page-title', 'Catégories de documents')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">Documents</a></li>
    <li class="breadcrumb-item active">Catégories</li>
@endsection

@section('content')
<div class="row g-4">

    {{-- Liste des catégories --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header fw-semibold">Catégories existantes</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Catégorie</th>
                            <th class="text-center">Documents</th>
                            <th>Description</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                        <tr>
                            <td>
                                <i class="{{ $cat->icone }} text-{{ $cat->couleur }} me-2"></i>
                                <span class="fw-semibold">{{ $cat->nom }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('documents.index', ['categorie_id' => $cat->id]) }}"
                                   class="badge bg-{{ $cat->couleur }} text-decoration-none">
                                    {{ $cat->documents_count }}
                                </a>
                            </td>
                            <td class="text-muted small">{{ $cat->description ?? '—' }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary"
                                        onclick="editerCat({{ $cat->id }}, '{{ addslashes($cat->nom) }}', '{{ $cat->couleur }}', '{{ $cat->icone }}', '{{ addslashes($cat->description ?? '') }}')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @if($cat->documents_count === 0)
                                <form action="{{ route('documents.categories.destroy', $cat) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Supprimer la catégorie ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Aucune catégorie. Créez-en une ci-contre.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Formulaire création / édition --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header fw-semibold" id="form-titre">Nouvelle catégorie</div>
            <div class="card-body">
                <form id="form-cat" action="{{ route('documents.categories.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="method-field" value="POST">
                    <input type="hidden" id="cat-id" value="">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" id="inp-nom" class="form-control @error('nom') is-invalid @enderror"
                               placeholder="Ex: Juridique, RH, Finance…" required>
                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Couleur</label>
                        <div class="d-flex gap-2 flex-wrap" id="couleur-group">
                            @foreach(['primary','success','danger','warning','info','secondary','dark'] as $c)
                            <label class="d-flex align-items-center gap-1" style="cursor:pointer">
                                <input type="radio" name="couleur" value="{{ $c }}" class="d-none couleur-radio"
                                       {{ old('couleur', 'primary') === $c ? 'checked' : '' }}
                                       onchange="majApercu()">
                                <span class="rounded-circle d-inline-block border couleur-opt"
                                      style="width:28px;height:28px;background:var(--bs-{{ $c }});"
                                      data-val="{{ $c }}"></span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Icône Bootstrap</label>
                        <div class="input-group">
                            <span class="input-group-text"><i id="apercu-icone" class="bi bi-folder"></i></span>
                            <input type="text" name="icone" id="inp-icone" class="form-control"
                                   value="{{ old('icone', 'bi-folder') }}" placeholder="bi-folder, bi-file-earmark…"
                                   oninput="majApercu()">
                        </div>
                        <small class="text-muted">
                            <a href="https://icons.getbootstrap.com/" target="_blank">Liste des icônes Bootstrap</a>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="inp-desc" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-save me-1"></i>Enregistrer
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const routes = {
    store:  '{{ route('documents.categories.store') }}',
    update: (id) => '/documents/categories/' + id,
};

function editerCat(id, nom, couleur, icone, desc) {
    document.getElementById('form-titre').textContent = 'Modifier la catégorie';
    document.getElementById('cat-id').value  = id;
    document.getElementById('inp-nom').value = nom;
    document.getElementById('inp-icone').value = icone;
    document.getElementById('inp-desc').value  = desc;
    document.getElementById('method-field').value = 'PUT';
    document.getElementById('form-cat').action = routes.update(id);

    document.querySelectorAll('.couleur-radio').forEach(r => r.checked = r.value === couleur);
    majApercu();
    document.getElementById('inp-nom').focus();
}

function resetForm() {
    document.getElementById('form-titre').textContent = 'Nouvelle catégorie';
    document.getElementById('form-cat').reset();
    document.getElementById('method-field').value = 'POST';
    document.getElementById('form-cat').action = routes.store;
    document.getElementById('cat-id').value = '';
    majApercu();
}

function majApercu() {
    const icone = document.getElementById('inp-icone').value || 'bi-folder';
    document.getElementById('apercu-icone').className = 'bi ' + icone;

    document.querySelectorAll('.couleur-opt').forEach(el => {
        const radio = document.querySelector(`.couleur-radio[value="${el.dataset.val}"]`);
        el.style.outline = radio?.checked ? '3px solid #333' : 'none';
        el.style.outlineOffset = '2px';
    });
}

// Clic sur pastille couleur
document.querySelectorAll('.couleur-opt').forEach(el => {
    el.addEventListener('click', () => {
        document.querySelector(`.couleur-radio[value="${el.dataset.val}"]`).checked = true;
        majApercu();
    });
});

majApercu();
</script>
@endpush
