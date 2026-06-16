@extends('layouts.app')
@section('title', 'Nouveau message')
@section('page-title', 'Rédiger un message')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('secretariat.messages.index') }}">Messages</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
@endsection

@push('styles')
<style>
.drop-zone {
    border: 2px dashed var(--bs-border-color);
    border-radius: 10px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
}
.drop-zone:hover, .drop-zone.drag-over {
    border-color: var(--saa-blue, #0D47A1);
    background: rgba(13,71,161,.04);
}
.pj-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: .5rem .75rem;
    margin-top: .5rem;
}
.pj-item .pj-name  { flex: 1; font-size: .875rem; font-weight: 500; }
.pj-item .pj-size  { font-size: .75rem; color: #6b7280; }
.pj-item .pj-del   { color: #dc2626; cursor: pointer; border: none; background: none; padding: 0; }
</style>
@endpush

@section('content')
<form action="{{ route('secretariat.messages.store') }}" method="POST"
      enctype="multipart/form-data" id="form-message">
@csrf
<div class="row g-4">

    {{-- ── Rédaction ───────────────────────────────────────────── --}}
    <div class="col-lg-8">

        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-envelope-paper me-2 text-primary"></i>Message
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Objet <span class="text-danger">*</span></label>
                        <input type="text" name="objet" value="{{ old('objet') }}"
                               class="form-control @error('objet') is-invalid @enderror"
                               placeholder="Objet du message" required>
                        @error('objet')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Canal d'envoi</label>
                        <select name="canal" class="form-select" id="sel-canal">
                            <option value="email"     {{ old('canal','email') === 'email'     ? 'selected' : '' }}>📧 Email</option>
                            <option value="sms"       {{ old('canal')         === 'sms'       ? 'selected' : '' }}>📱 SMS</option>
                            <option value="email_sms" {{ old('canal')         === 'email_sms' ? 'selected' : '' }}>📧📱 Email + SMS</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Corps du message <span class="text-danger">*</span></label>
                        <textarea name="corps" id="corps"
                                  class="form-control @error('corps') is-invalid @enderror"
                                  rows="10"
                                  placeholder="Rédigez votre message ici…&#10;&#10;Le nom du destinataire sera automatiquement ajouté en salutation."
                                  required>{{ old('corps') }}</textarea>
                        @error('corps')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="d-flex justify-content-end mt-1">
                            <small class="text-muted" id="char-count">0 caractères</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Pièces jointes ─────────────────────────────────── --}}
        <div class="card mb-4" id="card-pj">
            <div class="card-header fw-semibold">
                <i class="bi bi-paperclip me-2 text-success"></i>Pièces jointes
                <span class="badge bg-secondary ms-1" id="nb-pj">0</span>
            </div>
            <div class="card-body">
                {{-- Zone drag & drop --}}
                <div class="drop-zone" id="drop-zone" onclick="document.getElementById('input-pj').click()">
                    <i class="bi bi-cloud-upload fs-2 text-primary d-block mb-2"></i>
                    <div class="fw-semibold">Glisser-déposer ou cliquer pour ajouter des fichiers</div>
                    <div class="small text-muted mt-1">
                        PDF, Word, Excel, PowerPoint, Images, ZIP — max 10 Mo par fichier
                    </div>
                </div>
                <input type="file" id="input-pj" name="pieces_jointes[]"
                       multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.txt"
                       class="d-none">

                {{-- Liste des fichiers sélectionnés --}}
                <div id="pj-list" class="mt-2"></div>

                @error('pieces_jointes.*')
                <div class="text-danger small mt-2"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Avertissement SMS --}}
        <div id="sms-warning" class="alert alert-warning d-none py-2 small">
            <i class="bi bi-exclamation-triangle me-2"></i>
            L'envoi SMS nécessite la configuration d'une API externe (ex: <strong>Africa's Talking</strong>).
            Les SMS seront marqués "échoué" tant que l'API n'est pas configurée.
            <strong>Les pièces jointes ne sont pas supportées par SMS.</strong>
        </div>
    </div>

    {{-- ── Destinataires ──────────────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bi bi-people me-2 text-success"></i>Destinataires
            </div>
            <div class="card-body">
                <input type="text" id="filtre-clients" class="form-control form-control-sm mb-2"
                       placeholder="Rechercher un client…">
                <div style="max-height:340px;overflow-y:auto" class="border rounded p-2">
                    @foreach($clients as $client)
                    <div class="form-check client-item py-1 border-bottom" data-nom="{{ strtolower($client->nom) }}">
                        <input class="form-check-input" type="checkbox" name="client_ids[]"
                               value="{{ $client->id }}" id="cl_{{ $client->id }}"
                               {{ in_array($client->id, old('client_ids', [])) ? 'checked' : '' }}>
                        <label class="form-check-label w-100" for="cl_{{ $client->id }}">
                            <div class="fw-semibold small">{{ $client->nom }}</div>
                            <div class="text-muted" style="font-size:.72rem">
                                @if($client->email)<i class="bi bi-envelope me-1"></i>{{ Str::limit($client->email, 28) }}<br>@endif
                                @if($client->telephone)<i class="bi bi-telephone me-1"></i>{{ $client->telephone }}@endif
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>
                @error('client_ids')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                <div class="mt-2 d-flex gap-2">
                    <button type="button" class="btn btn-xs btn-outline-primary" onclick="tousClients(true)">Tout sélect.</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="tousClients(false)">Tout désélect.</button>
                </div>
                <div class="mt-2 text-muted small" id="nb-sel">0 client(s) sélectionné(s)</div>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" name="action" value="brouillon" class="btn btn-outline-secondary">
                <i class="bi bi-floppy me-1"></i>Enregistrer comme brouillon
            </button>
            <button type="submit" name="action" value="envoyer" class="btn btn-primary">
                <i class="bi bi-send me-1"></i>Envoyer maintenant
            </button>
        </div>
    </div>

</div>
</form>
@endsection

@push('scripts')
<script>
// ── Compteur caractères ─────────────────────────────────────────
const corpsEl = document.getElementById('corps');
const cntEl   = document.getElementById('char-count');
corpsEl.addEventListener('input', () => cntEl.textContent = corpsEl.value.length + ' caractères');

// ── Avertissement SMS ───────────────────────────────────────────
document.getElementById('sel-canal').addEventListener('change', function () {
    document.getElementById('sms-warning').classList.toggle('d-none', !['sms','email_sms'].includes(this.value));
});

// ── Filtre clients ──────────────────────────────────────────────
document.getElementById('filtre-clients').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.client-item').forEach(el =>
        el.style.display = el.dataset.nom.includes(q) ? '' : 'none'
    );
});

function tousClients(val) {
    document.querySelectorAll('.client-item input[type=checkbox]').forEach(cb => cb.checked = val);
    majCompte();
}
function majCompte() {
    const n = document.querySelectorAll('input[name="client_ids[]"]:checked').length;
    document.getElementById('nb-sel').textContent = n + ' client(s) sélectionné(s)';
}
document.querySelectorAll('input[name="client_ids[]"]').forEach(cb => cb.addEventListener('change', majCompte));
majCompte();

// ── Gestion pièces jointes ──────────────────────────────────────
const inputPj = document.getElementById('input-pj');
const pjList  = document.getElementById('pj-list');
const nbPj    = document.getElementById('nb-pj');
const dropZone = document.getElementById('drop-zone');

// Icône par extension
function icone(nom) {
    const ext = nom.split('.').pop().toLowerCase();
    const map = { pdf:'bi-file-earmark-pdf text-danger', doc:'bi-file-earmark-word text-primary',
                  docx:'bi-file-earmark-word text-primary', xls:'bi-file-earmark-excel text-success',
                  xlsx:'bi-file-earmark-excel text-success', ppt:'bi-file-earmark-ppt text-warning',
                  pptx:'bi-file-earmark-ppt text-warning', jpg:'bi-file-earmark-image text-info',
                  jpeg:'bi-file-earmark-image text-info', png:'bi-file-earmark-image text-info',
                  gif:'bi-file-earmark-image text-info', zip:'bi-file-earmark-zip text-warning',
                  txt:'bi-file-earmark-text text-secondary' };
    return map[ext] || 'bi-file-earmark text-secondary';
}
function formatSize(bytes) {
    if (bytes >= 1048576) return (bytes/1048576).toFixed(1) + ' Mo';
    if (bytes >= 1024)    return Math.round(bytes/1024) + ' Ko';
    return bytes + ' o';
}

let fichiers = []; // DataTransfer pour maintenir la liste

function majAffichage() {
    pjList.innerHTML = '';
    nbPj.textContent = fichiers.length;
    fichiers.forEach((f, i) => {
        pjList.insertAdjacentHTML('beforeend', `
        <div class="pj-item">
            <i class="bi ${icone(f.name)} fs-5"></i>
            <span class="pj-name">${f.name}</span>
            <span class="pj-size">${formatSize(f.size)}</span>
            <button type="button" class="pj-del" onclick="supprimerFichier(${i})" title="Retirer">
                <i class="bi bi-x-circle-fill"></i>
            </button>
        </div>`);
    });
    // Sync avec l'input file via DataTransfer
    const dt = new DataTransfer();
    fichiers.forEach(f => dt.items.add(f));
    inputPj.files = dt.files;
}

function ajouterFichiers(listeFiles) {
    const maxOctets = 10 * 1024 * 1024;
    Array.from(listeFiles).forEach(f => {
        if (f.size > maxOctets) { alert(`"${f.name}" dépasse 10 Mo.`); return; }
        if (!fichiers.find(x => x.name === f.name && x.size === f.size)) fichiers.push(f);
    });
    majAffichage();
}

function supprimerFichier(i) {
    fichiers.splice(i, 1);
    majAffichage();
}

inputPj.addEventListener('change', () => ajouterFichiers(inputPj.files));

// Drag & drop
dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    ajouterFichiers(e.dataTransfer.files);
});
</script>
@endpush

@push('styles')
<style>.btn-xs { padding:.2rem .5rem; font-size:.75rem; }</style>
@endpush
