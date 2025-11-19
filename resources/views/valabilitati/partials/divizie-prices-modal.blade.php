<div
    class="modal fade"
    id="valabilitati-divizie-modal"
    tabindex="-1"
    aria-labelledby="valabilitati-divizie-modal-label"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="valabilitati-divizie-modal-label">
                    Tarife divizie
                    <span class="text-primary" data-divizie-name></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" role="alert" data-modal-alert></div>

                <div class="d-flex flex-column align-items-center py-4 gap-2 d-none" data-modal-loading>
                    <div class="spinner-border" role="status" aria-hidden="true"></div>
                    <p class="text-muted mb-0">Se încarcă informațiile diviziei...</p>
                </div>

                <div class="d-none" data-modal-form-wrapper>
                    <p class="text-muted">
                        Introdu tarifele pe kilometru cu trei zecimale. Lasă câmpul gol dacă nu se aplică.
                    </p>
                    <form id="valabilitati-divizie-form" data-divizie-form novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="divizie-pret-km-gol" class="form-label">Preț km gol</label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    class="form-control"
                                    id="divizie-pret-km-gol"
                                    name="pret_km_gol"
                                    step="0.001"
                                    min="0"
                                    data-format-decimal
                                >
                                <span class="input-group-text">/ km</span>
                                <div class="invalid-feedback" data-error-for="pret_km_gol"></div>
                            </div>
                            <div class="form-text">Valoare cu până la trei zecimale.</div>
                        </div>

                        <div class="mb-3">
                            <label for="divizie-pret-km-plin" class="form-label">Preț km plin</label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    class="form-control"
                                    id="divizie-pret-km-plin"
                                    name="pret_km_plin"
                                    step="0.001"
                                    min="0"
                                    data-format-decimal
                                >
                                <span class="input-group-text">/ km</span>
                                <div class="invalid-feedback" data-error-for="pret_km_plin"></div>
                            </div>
                            <div class="form-text">Valoare cu până la trei zecimale.</div>
                        </div>

                        <div class="mb-3">
                            <label for="divizie-pret-km-taxa" class="form-label">Preț km cu taxă</label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    class="form-control"
                                    id="divizie-pret-km-taxa"
                                    name="pret_km_cu_taxa"
                                    step="0.001"
                                    min="0"
                                    data-format-decimal
                                >
                                <span class="input-group-text">/ km</span>
                                <div class="invalid-feedback" data-error-for="pret_km_cu_taxa"></div>
                            </div>
                            <div class="form-text">Valoare cu până la trei zecimale.</div>
                        </div>

                        <div class="mb-3">
                            <label for="divizie-contributie-zilnica" class="form-label">Contribuție zilnică</label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    class="form-control"
                                    id="divizie-contributie-zilnica"
                                    name="contributie_zilnica"
                                    step="0.001"
                                    min="0"
                                    data-format-decimal
                                >
                                <span class="input-group-text">/ zi</span>
                                <div class="invalid-feedback" data-error-for="contributie_zilnica"></div>
                            </div>
                            <div class="form-text">Valoare cu până la trei zecimale.</div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Închide</button>
                <button type="submit" class="btn btn-primary" form="valabilitati-divizie-form" data-modal-submit>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" data-modal-submit-spinner></span>
                    <span data-modal-submit-label>Salvează</span>
                </button>
            </div>
        </div>
    </div>
</div>
