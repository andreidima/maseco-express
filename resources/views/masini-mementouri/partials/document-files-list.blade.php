<ul class="list-unstyled mb-0" data-document-files-list>
    @forelse ($document->fisiere as $fisier)
        @php
            $iconClass = $fisier->iconClass();
            $previewRoute = route('masini-mementouri.documente.fisiere.preview', [$masina, $document, $fisier]);
            $downloadRoute = route('masini-mementouri.documente.fisiere.download', [$masina, $document, $fisier]);
            $deleteRoute = route('masini-mementouri.documente.fisiere.destroy', [$masina, $document, $fisier]);
            $deleteModalId = 'deleteFileModal-' . $fisier->id;
            $deleteFormId = 'delete-file-form-' . $fisier->id;
        @endphp
        <li class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 py-2 border-bottom" data-document-file data-file-id="{{ $fisier->id }}">
            <div class="d-flex align-items-center gap-2 flex-grow-1">
                <i class="fa-solid {{ $iconClass }} fs-5"></i>
                <div>
                    <div class="fw-semibold">{{ $fisier->nume_original }}</div>
                    <div class="text-muted small">{{ number_format(($fisier->dimensiune ?? 0) / 1024, 1) }} KB</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="btn-group btn-group-sm" role="group">
                    @if ($fisier->isPreviewable())
                        <a href="{{ $previewRoute }}" class="btn btn-outline-primary border" target="_blank" rel="noopener" title="Deschide în filă nouă">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    @endif
                    <a href="{{ $downloadRoute }}" class="btn btn-outline-secondary border" download="{{ $fisier->downloadName() }}" title="Descarcă fișierul">
                        <i class="fa-solid fa-download"></i>
                    </a>
                    <button type="button" class="btn btn-outline-danger border" title="Șterge fișierul" data-bs-toggle="modal" data-bs-target="#{{ $deleteModalId }}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
            <form id="{{ $deleteFormId }}" method="POST" action="{{ $deleteRoute }}" class="d-none" data-document-delete>
                @csrf
                @method('DELETE')
            </form>

            <div class="modal fade" id="{{ $deleteModalId }}" tabindex="-1" aria-labelledby="{{ $deleteModalId }}Label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="{{ $deleteModalId }}Label">Confirmă ștergerea</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
                        </div>
                        <div class="modal-body">
                            Ești sigur că dorești să ștergi fișierul „{{ $fisier->nume_original }}”? Această acțiune nu poate fi anulată.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Anulează</button>
                            <button type="button" class="btn btn-danger" onclick="document.getElementById('{{ $deleteFormId }}').submit();">
                                <i class="fa-solid fa-trash me-1"></i>Șterge fișierul
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    @empty
        <li class="text-muted" data-document-files-empty>Nu există fișiere încărcate.</li>
    @endforelse
</ul>
