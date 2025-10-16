<div class="modal fade" id="stockDetailsModal" tabindex="-1" aria-labelledby="stockDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockDetailsModalLabel">Detalii stoc</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h5 class="mb-1" data-stock-details="name">—</h5>
                    <div class="text-muted small d-none" data-stock-details="code"></div>
                </div>
                <dl class="row mb-4">
                    <dt class="col-sm-5 col-lg-4">Cantitate inițială</dt>
                    <dd class="col-sm-7 col-lg-8" data-stock-details="initial">—</dd>
                    <dt class="col-sm-5 col-lg-4">Cantitate alocată</dt>
                    <dd class="col-sm-7 col-lg-8" data-stock-details="used">0.00</dd>
                    <dt class="col-sm-5 col-lg-4">Cantitate rămasă</dt>
                    <dd class="col-sm-7 col-lg-8" data-stock-details="remaining">—</dd>
                </dl>
                <h6 class="mb-2">Alocări pe mașini</h6>
                <div class="table-responsive mb-2">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Mașină</th>
                                <th>Data</th>
                                <th class="text-end">Cantitate</th>
                            </tr>
                        </thead>
                        <tbody data-stock-details="machines-body"></tbody>
                    </table>
                </div>
                <div class="text-muted" data-stock-details="machines-empty">Nu există alocări pentru această piesă.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Închide</button>
            </div>
        </div>
    </div>
</div>

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('stockDetailsModal');

            if (!modal) {
                return;
            }

            const nameEl = modal.querySelector('[data-stock-details="name"]');
            const codeEl = modal.querySelector('[data-stock-details="code"]');
            const initialEl = modal.querySelector('[data-stock-details="initial"]');
            const usedEl = modal.querySelector('[data-stock-details="used"]');
            const remainingEl = modal.querySelector('[data-stock-details="remaining"]');
            const machinesBody = modal.querySelector('[data-stock-details="machines-body"]');
            const noMachinesEl = modal.querySelector('[data-stock-details="machines-empty"]');

            modal.addEventListener('show.bs.modal', function (event) {
                const trigger = event.relatedTarget;

                if (!trigger) {
                    return;
                }

                const name = trigger.getAttribute('data-piece-name') || '—';
                const code = trigger.getAttribute('data-piece-code') || '';
                const initial = trigger.getAttribute('data-piece-initial');
                const remaining = trigger.getAttribute('data-piece-remaining');
                const used = trigger.getAttribute('data-piece-used');
                const machinesJson = trigger.getAttribute('data-piece-machines') || '[]';

                let machines = [];

                try {
                    const parsed = JSON.parse(machinesJson);
                    machines = Array.isArray(parsed) ? parsed : [];
                } catch (error) {
                    machines = [];
                }

                nameEl.textContent = name || '—';

                if (code) {
                    codeEl.textContent = `Cod: ${code}`;
                    codeEl.classList.remove('d-none');
                } else {
                    codeEl.textContent = '';
                    codeEl.classList.add('d-none');
                }

                initialEl.textContent = initial && initial !== '' ? initial : '—';
                usedEl.textContent = used && used !== '' ? used : '0.00';
                remainingEl.textContent = remaining && remaining !== '' ? remaining : '—';

                machinesBody.innerHTML = '';

                if (!machines.length) {
                    noMachinesEl.classList.remove('d-none');
                    return;
                }

                noMachinesEl.classList.add('d-none');

                machines.forEach((machine) => {
                    const row = document.createElement('tr');
                    const masinaCell = document.createElement('td');
                    const number = machine.numar_inmatriculare || '';
                    const label = machine.denumire || '';
                    masinaCell.textContent = number
                        ? label
                            ? `${number} – ${label}`
                            : number
                        : label || '—';

                    const dateCell = document.createElement('td');
                    dateCell.textContent = machine.data || '—';

                    const qtyCell = document.createElement('td');
                    qtyCell.className = 'text-end';
                    const qty = Number.parseFloat(machine.cantitate ?? 0);
                    qtyCell.textContent = Number.isFinite(qty) ? qty.toFixed(2) : '0.00';

                    row.appendChild(masinaCell);
                    row.appendChild(dateCell);
                    row.appendChild(qtyCell);
                    machinesBody.appendChild(row);
                });
            });
        });
    </script>
@endpush
