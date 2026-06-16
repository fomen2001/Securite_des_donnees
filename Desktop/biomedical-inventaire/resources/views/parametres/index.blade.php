@extends('layouts.app')

@section('title', 'Paramètres')
@section('page-title', 'Paramètres de l\'entreprise')

@push('styles')
<style>
    .logo-preview { max-height: 100px; max-width: 280px; object-fit: contain; border-radius: 6px; }
    .logo-zone { border: 2px dashed #dee2e6; border-radius: 10px; padding: 1.5rem; text-align: center; transition: border-color .2s; cursor: pointer; }
    .logo-zone:hover { border-color: #0d6efd; background: #f8f9ff; }
    .nav-pills .nav-link { color: #555; font-size: .9rem; }
    .nav-pills .nav-link.active { background: #1a3a5c; color: #fff; }
    .section-title { font-size: .75rem; text-transform: uppercase; letter-spacing: 1px; color: #aaa; margin-bottom: 1rem; margin-top: .5rem; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('parametres.update') }}" enctype="multipart/form-data" id="formParams">
    @csrf

    <div class="row g-4">

        {{-- Navigation latérale --}}
        <div class="col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body p-2">
                    <nav class="nav nav-pills flex-column gap-1" id="paramTabs">
                        <a class="nav-link active" href="#identite" onclick="scrollTo(this)">
                            <i class="bi bi-building me-2"></i>Identité
                        </a>
                        <a class="nav-link" href="#coordonnees" onclick="scrollTo(this)">
                            <i class="bi bi-geo-alt me-2"></i>Coordonnées
                        </a>
                        <a class="nav-link" href="#logo" onclick="scrollTo(this)">
                            <i class="bi bi-image me-2"></i>Logo
                        </a>
                        <a class="nav-link" href="#facture" onclick="scrollTo(this)">
                            <i class="bi bi-receipt me-2"></i>Facturation
                        </a>
                        <a class="nav-link" href="#divers" onclick="scrollTo(this)">
                            <i class="bi bi-sliders me-2"></i>Divers
                        </a>
                    </nav>
                </div>
            </div>

            {{-- Aperçu mini --}}
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white fw-semibold small">
                    <i class="bi bi-eye me-1"></i>Aperçu
                </div>
                <div class="card-body text-center py-3">
                    @if($params['entreprise_logo'] && file_exists(public_path('images/' . $params['entreprise_logo'])))
                        <img src="{{ asset('images/' . $params['entreprise_logo']) }}"
                             id="logoApercu" class="logo-preview mb-2 d-block mx-auto">
                    @else
                        <div id="logoApercu" class="text-muted mb-2">
                            <i class="bi bi-image fs-1 d-block"></i>
                            <small>Aucun logo</small>
                        </div>
                    @endif
                    <strong id="apercu_nom" class="d-block">{{ $params['entreprise_nom'] }}</strong>
                    <small class="text-muted" id="apercu_slogan">{{ $params['entreprise_slogan'] }}</small>
                </div>
            </div>
        </div>

        {{-- Formulaire --}}
        <div class="col-lg-9">

            {{-- ===== Identité ===== --}}
            <div class="card shadow-sm mb-4" id="identite">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-building me-2 text-primary"></i>Identité de l'entreprise</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nom de l'entreprise <span class="text-danger">*</span></label>
                            <input type="text" name="entreprise_nom" id="inp_nom"
                                class="form-control @error('entreprise_nom') is-invalid @enderror"
                                value="{{ old('entreprise_nom', $params['entreprise_nom']) }}"
                                required placeholder="ex: BioMédical Santé SARL">
                            @error('entreprise_nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Slogan / Activité</label>
                            <input type="text" name="entreprise_slogan" id="inp_slogan"
                                class="form-control"
                                value="{{ old('entreprise_slogan', $params['entreprise_slogan']) }}"
                                placeholder="ex: Distribution d'équipements médicaux">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                N° Unique d'Identification (NIU)
                                <span class="text-muted small">— N° fiscal Cameroun</span>
                            </label>
                            <input type="text" name="entreprise_niu" class="form-control"
                                value="{{ old('entreprise_niu', $params['entreprise_niu']) }}"
                                placeholder="ex: M012345678901A">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Registre du commerce (RC)</label>
                            <input type="text" name="entreprise_rc" class="form-control"
                                value="{{ old('entreprise_rc', $params['entreprise_rc']) }}"
                                placeholder="ex: RC/YAO/2020/B/1234">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Coordonnées ===== --}}
            <div class="card shadow-sm mb-4" id="coordonnees">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-geo-alt me-2 text-info"></i>Coordonnées</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Adresse</label>
                            <textarea name="entreprise_adresse" class="form-control" rows="2"
                                placeholder="BP 1234, Rue des Hôpitaux...">{{ old('entreprise_adresse', $params['entreprise_adresse']) }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ville</label>
                            <input type="text" name="entreprise_ville" class="form-control"
                                value="{{ old('entreprise_ville', $params['entreprise_ville']) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pays</label>
                            <input type="text" name="entreprise_pays" class="form-control"
                                value="{{ old('entreprise_pays', $params['entreprise_pays']) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Téléphone</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="entreprise_telephone" class="form-control"
                                    value="{{ old('entreprise_telephone', $params['entreprise_telephone']) }}"
                                    placeholder="+237 6XX XXX XXX">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="entreprise_email" class="form-control @error('entreprise_email') is-invalid @enderror"
                                    value="{{ old('entreprise_email', $params['entreprise_email']) }}"
                                    placeholder="contact@entreprise.cm">
                                @error('entreprise_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Site web</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-globe"></i></span>
                                <input type="url" name="entreprise_site_web" class="form-control @error('entreprise_site_web') is-invalid @enderror"
                                    value="{{ old('entreprise_site_web', $params['entreprise_site_web']) }}"
                                    placeholder="https://www.entreprise.cm">
                                @error('entreprise_site_web')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Logo ===== --}}
            <div class="card shadow-sm mb-4" id="logo">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-image me-2 text-success"></i>Logo de l'entreprise</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4 align-items-center">
                        {{-- Logo actuel --}}
                        <div class="col-md-4 text-center">
                            @if($params['entreprise_logo'] && file_exists(public_path('images/' . $params['entreprise_logo'])))
                                <img src="{{ asset('images/' . $params['entreprise_logo']) }}"
                                     id="currentLogo" class="logo-preview mb-3 d-block mx-auto shadow-sm">
                                <div class="form-check d-inline-flex align-items-center gap-2">
                                    <input type="checkbox" name="supprimer_logo" value="1"
                                           class="form-check-input" id="chkSupprLogo">
                                    <label class="form-check-label text-danger small" for="chkSupprLogo">
                                        <i class="bi bi-trash me-1"></i>Supprimer le logo
                                    </label>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-image fs-1 d-block mb-1 text-secondary"></i>
                                    <small>Aucun logo actuellement</small>
                                </div>
                            @endif
                        </div>

                        {{-- Upload nouveau logo --}}
                        <div class="col-md-8">
                            <label class="logo-zone w-100" for="inputLogo" id="dropZone">
                                <i class="bi bi-cloud-upload fs-2 text-primary d-block mb-2"></i>
                                <span class="fw-semibold">Cliquer pour choisir un logo</span><br>
                                <span class="text-muted small">PNG, JPG ou SVG — max 2 Mo</span><br>
                                <span class="text-muted small">Recommandé : fond transparent (PNG), min 300×100 px</span>
                            </label>
                            <input type="file" name="logo" id="inputLogo" class="d-none"
                                   accept="image/png,image/jpeg,image/jpg,image/svg+xml">

                            {{-- Aperçu du nouveau fichier --}}
                            <div id="nouveauLogoPreview" class="mt-3 text-center d-none">
                                <img id="nouveauLogoImg" src="" class="logo-preview shadow-sm">
                                <div class="small text-success mt-1">
                                    <i class="bi bi-check-circle me-1"></i>
                                    <span id="nouveauLogoNom"></span>
                                </div>
                            </div>

                            @error('logo')
                                <div class="text-danger small mt-2"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Facturation ===== --}}
            <div class="card shadow-sm mb-4" id="facture">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-receipt me-2 text-warning"></i>Paramètres de facturation</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">
                                TVA par défaut (%)
                                <span class="text-muted small">— appliqué aux nouvelles ventes</span>
                            </label>
                            <input type="number" name="facture_tva_defaut" class="form-control"
                                value="{{ old('facture_tva_defaut', $params['facture_tva_defaut']) }}"
                                min="0" max="100" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">
                                Préfixe numéro de facture
                            </label>
                            <div class="input-group">
                                <input type="text" name="facture_prefix" class="form-control"
                                    value="{{ old('facture_prefix', $params['facture_prefix']) }}"
                                    maxlength="10" style="max-width:100px">
                                <span class="input-group-text text-muted">-2026-0001</span>
                            </div>
                            <div class="form-text">Résultat : <strong>{{ $params['facture_prefix'] }}-2026-0001</strong></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Devise / Monnaie</label>
                            <div class="row g-1">
                                <div class="col-7">
                                    <input type="text" name="monnaie" class="form-control"
                                        value="{{ old('monnaie', $params['monnaie']) }}"
                                        placeholder="FCFA" maxlength="10">
                                    <div class="form-text">Libellé complet</div>
                                </div>
                                <div class="col-5">
                                    <input type="text" name="monnaie_symbole" class="form-control"
                                        value="{{ old('monnaie_symbole', $params['monnaie_symbole']) }}"
                                        placeholder="FCFA" maxlength="10">
                                    <div class="form-text">Symbole</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Conditions de paiement</label>
                            <textarea name="facture_conditions" class="form-control" rows="3"
                                placeholder="ex: Règlement à 30 jours. Tout règlement doit être effectué à l'ordre de ...">{{ old('facture_conditions', $params['facture_conditions']) }}</textarea>
                            <div class="form-text">Apparaît dans la zone « Notes » de chaque facture par défaut.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mentions légales / bas de facture</label>
                            <textarea name="facture_mentions" class="form-control" rows="3"
                                placeholder="ex: Les marchandises voyagent aux risques du destinataire...">{{ old('facture_mentions', $params['facture_mentions']) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Texte pied de page additionnel</label>
                            <input type="text" name="facture_pied" class="form-control"
                                value="{{ old('facture_pied', $params['facture_pied']) }}"
                                placeholder="ex: Merci de votre confiance !">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Divers ===== --}}
            <div class="card shadow-sm mb-4" id="divers">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-sliders me-2 text-secondary"></i>Divers</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info py-2 small mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        D'autres paramètres (gestion des rôles utilisateurs, notifications, sauvegardes) pourront être configurés ici.
                    </div>
                </div>
            </div>

            {{-- Bouton global --}}
            <div class="d-flex gap-2 pb-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-circle me-2"></i>Enregistrer tous les paramètres
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>

        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Aperçu en temps réel du nom / slogan
    document.getElementById('inp_nom')?.addEventListener('input', function () {
        document.getElementById('apercu_nom').textContent = this.value || '—';
    });
    document.getElementById('inp_slogan')?.addEventListener('input', function () {
        document.getElementById('apercu_slogan').textContent = this.value;
    });

    // Prévisualisation du logo sélectionné
    document.getElementById('inputLogo').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('nouveauLogoImg').src = e.target.result;
            document.getElementById('nouveauLogoNom').textContent = file.name;
            document.getElementById('nouveauLogoPreview').classList.remove('d-none');

            // Mettre à jour l'aperçu mini dans la sidebar
            const apercuLogo = document.getElementById('logoApercu');
            if (apercuLogo && apercuLogo.tagName === 'IMG') {
                apercuLogo.src = e.target.result;
            } else if (apercuLogo) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.id = 'logoApercu';
                img.className = 'logo-preview mb-2 d-block mx-auto';
                apercuLogo.replaceWith(img);
            }
        };
        reader.readAsDataURL(file);
    });

    // Glisser-déposer dans la zone de logo
    const dropZone = document.getElementById('dropZone');
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.style.borderColor = '#0d6efd'; });
    dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = ''; });
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.style.borderColor = '';
        const dt = new DataTransfer();
        dt.items.add(e.dataTransfer.files[0]);
        document.getElementById('inputLogo').files = dt.files;
        document.getElementById('inputLogo').dispatchEvent(new Event('change'));
    });

    // Navigation douce
    function scrollTo(link) {
        event.preventDefault();
        const id = link.getAttribute('href').slice(1);
        document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        document.querySelectorAll('#paramTabs .nav-link').forEach(l => l.classList.remove('active'));
        link.classList.add('active');
    }
</script>
@endpush
