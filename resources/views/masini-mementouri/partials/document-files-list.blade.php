<ul class="list-unstyled mb-0" data-document-files-list>
    @forelse ($document->fisiere as $fisier)
        @php
            $iconClass = $fisier->iconClass();
            $previewRoute = route('masini-mementouri.documente.fisiere.preview', [$masina, $document, $fisier]);
            $downloadRoute = route('masini-mementouri.documente.fisiere.download', [$masina, $document, $fisier]);
            $deleteRoute = route('masini-mementouri.documente.fisiere.destroy', [$masina, $document, $fisier]);
        @endphp
        <li class="d-flex flex-column gap-2 mb-3" data-document-file data-file-id="{{ $fisier->id }}">
            <div class="d-flex justify-content-between align-items-center gap-2">
                <div class="me-auto">
                    <i class="fa-solid {{ $iconClass }} me-2"></i>
                    <span class="fw-semibold">{{ $fisier->nume_original }}</span>
                    <small class="text-muted ms-2">{{ number_format(($fisier->dimensiune ?? 0) / 1024, 1) }} KB</small>
                </div>
                <div class="btn-group btn-group-sm" role="group">
                    @if ($fisier->isPreviewable())
                        <a href="{{ $previewRoute }}" class="btn btn-outline-primary border" target="_blank" rel="noopener" title="Deschide în filă nouă">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    @endif
                    <a href="{{ $downloadRoute }}" class="btn btn-outline-secondary border" download="{{ $fisier->downloadName() }}" title="Descarcă fișierul">
                        <i class="fa-solid fa-download"></i>
                    </a>
                </div>
            </div>
            <form method="POST" action="{{ $deleteRoute }}" class="text-end" data-document-delete>
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger border border-dark rounded-3">
                    <i class="fa-solid fa-trash me-1"></i>Șterge
                </button>
            </form>
        </li>
    @empty
        <li class="text-muted" data-document-files-empty>Nu există fișiere încărcate.</li>
    @endforelse
</ul>
