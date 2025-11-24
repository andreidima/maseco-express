<div
    class="modal fade"
    id="valabilitati-price-modal"
    tabindex="-1"
    aria-labelledby="valabilitati-price-modal-label"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="valabilitati-price-modal-label">
                    Tarife valabilitate
                    <span class="text-primary" data-price-modal-name></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" role="alert" data-modal-alert></div>

                <div class="d-flex flex-column align-items-center py-4 gap-2 d-none" data-modal-loading>
                    <div class="spinner-border" role="status" aria-hidden="true"></div>
                    <p class="text-muted mb-0">Se încarcă tarifele valabilității...</p>
                </div>

                <div class="d-none" data-modal-form-wrapper>
                    <p class="text-muted">
                        Introdu tarifele pe kilometru cu trei zecimale. Lasă câmpul gol dacă nu se aplică.
                    </p>
                    <p class="text-muted d-none" data-modal-empty-state>
                        Nu există câmpuri configurabile pentru această valabilitate.
                    </p>
                    <form id="valabilitati-price-form" data-price-form data-valabilitate-form novalidate>
                        @csrf
                        <div class="mb-3" data-field-wrapper="flash_pret_km_gol">
                            <label for="valabilitate-pret-km-gol" class="form-label">FLASH - preț km gol</label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    class="form-control"
                                    id="valabilitate-pret-km-gol"
                                    name="flash_pret_km_gol"
                                    step="0.001"
                                    min="0"
                                    data-format-decimal
                                >
                                <span class="input-group-text">/ km</span>
                                <div class="invalid-feedback" data-error-for="flash_pret_km_gol"></div>
                            </div>
                            <div class="form-text">Valoare cu până la trei zecimale.</div>
                        </div>

                        <div class="mb-3" data-field-wrapper="flash_pret_km_plin">
                            <label for="valabilitate-pret-km-plin" class="form-label">FLASH - preț km plin</label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    class="form-control"
                                    id="valabilitate-pret-km-plin"
                                    name="flash_pret_km_plin"
                                    step="0.001"
                                    min="0"
                                    data-format-decimal
                                >
                                <span class="input-group-text">/ km</span>
                                <div class="invalid-feedback" data-error-for="flash_pret_km_plin"></div>
                            </div>
                            <div class="form-text">Valoare cu până la trei zecimale.</div>
                        </div>

                        <div class="mb-3" data-field-wrapper="flash_pret_km_cu_taxa">
                            <label for="valabilitate-pret-km-taxa" class="form-label">FLASH - preț km cu taxă</label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    class="form-control"
                                    id="valabilitate-pret-km-taxa"
                                    name="flash_pret_km_cu_taxa"
                                    step="0.001"
                                    min="0"
                                    data-format-decimal
                                >
                                <span class="input-group-text">/ km</span>
                                <div class="invalid-feedback" data-error-for="flash_pret_km_cu_taxa"></div>
                            </div>
                            <div class="form-text">Valoare cu până la trei zecimale.</div>
                        </div>

                        <div class="mb-3" data-field-wrapper="flash_contributie_zilnica">
                            <label for="valabilitate-contributie-zilnica" class="form-label">FLASH - contribuție zilnică</label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    class="form-control"
                                    id="valabilitate-contributie-zilnica"
                                    name="flash_contributie_zilnica"
                                    step="0.001"
                                    min="0"
                                    data-format-decimal
                                >
                                <span class="input-group-text">/ zi</span>
                                <div class="invalid-feedback" data-error-for="flash_contributie_zilnica"></div>
                            </div>
                            <div class="form-text">Valoare cu până la trei zecimale.</div>
                        </div>

                        <div class="mb-3" data-field-wrapper="timestar_pret_km_bord">
                            <label for="valabilitate-pret-km-bord" class="form-label">TIMESTAR - preț km bord</label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    class="form-control"
                                    id="valabilitate-pret-km-bord"
                                    name="timestar_pret_km_bord"
                                    step="0.001"
                                    min="0"
                                    data-format-decimal
                                >
                                <span class="input-group-text">/ km</span>
                                <div class="invalid-feedback" data-error-for="timestar_pret_km_bord"></div>
                            </div>
                            <div class="form-text">Valoare cu până la trei zecimale.</div>
                        </div>

                        <div class="mb-3" data-field-wrapper="timestar_pret_nr_zile_lucrate">
                            <label for="valabilitate-pret-zile-lucrate" class="form-label">TIMESTAR - preț nr zile lucrate</label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    class="form-control"
                                    id="valabilitate-pret-zile-lucrate"
                                    name="timestar_pret_nr_zile_lucrate"
                                    step="0.001"
                                    min="0"
                                    data-format-decimal
                                >
                                <span class="input-group-text">/ zi</span>
                                <div class="invalid-feedback" data-error-for="timestar_pret_nr_zile_lucrate"></div>
                            </div>
                            <div class="form-text">Valoare cu până la trei zecimale.</div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Închide</button>
                <button type="submit" class="btn btn-primary" form="valabilitati-price-form" data-modal-submit>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" data-modal-submit-spinner></span>
                    <span data-modal-submit-label>Salvează</span>
                </button>
            </div>
        </div>
    </div>
</div>
