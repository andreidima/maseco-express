@php
    $files = $fisiere ?? $masina->fisiereGenerale;
@endphp

<ul class="list-unstyled mb-0" data-general-files-list>
    @forelse ($files as $fisier)
        @php
            $previewRoute = route('masini-mementouri.fisiere-generale.preview', [$masina, $fisier]);
            $downloadRoute = route('masini-mementouri.fisiere-generale.download', [$masina, $fisier]);
            $deleteRoute = route('masini-mementouri.fisiere-generale.destroy', [$masina, $fisier]);
            $deleteModalId = 'deleteGeneralFileModal-' . $fisier->id;
            $deleteFormId = 'delete-general-file-form-' . $fisier->id;

            $details = [number_format(($fisier->dimensiune ?? 0) / 1024, 1) . ' KB'];

            if ($fisier->uploaded_by_name) {
                $details[] = $fisier->uploaded_by_name;
            }

            if ($fisier->created_at) {
                $details[] = $fisier->created_at->format('d.m.Y H:i');
            }
        @endphp
        <li class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 py-2 border-bottom" data-general-file data-file-id="{{ $fisier->id }}">
            <div class="d-flex align-items-start gap-3 flex-grow-1">
                <i class="fa-solid {{ $fisier->iconClass() }} fs-5"></i>
                <div>
                    <div class="fw-semibold">{{ $fisier->nume_original }}</div>
                    <div class="text-muted small">{{ implode(' • ', array_filter($details)) }}</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="btn-group btn-group-sm" role="group">
                    @if ($fisier->isPreviewable())
                        <a href="{{ $previewRoute }}" class="btn btn-outline-primary border" target="_blank" rel="noopener" title="{{ __('Previzualizează fișierul') }}">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    @endif
                    <a href="{{ $downloadRoute }}" class="btn btn-outline-secondary border" download="{{ $fisier->downloadName() }}" title="{{ __('Descarcă fișierul') }}">
                        <i class="fa-solid fa-download"></i>
                    </a>
                    <button type="button" class="btn btn-outline-danger border" title="{{ __('Șterge fișierul') }}" data-bs-toggle="modal" data-bs-target="#{{ $deleteModalId }}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>

            <form id="{{ $deleteFormId }}" method="POST" action="{{ $deleteRoute }}" class="d-none">
                @csrf
                @method('DELETE')
            </form>

            <div class="modal fade" id="{{ $deleteModalId }}" tabindex="-1" aria-labelledby="{{ $deleteModalId }}Label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="{{ $deleteModalId }}Label">{{ __('Confirmă ștergerea') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Închide') }}"></button>
                        </div>
                        <div class="modal-body">
                            {{ __('Ești sigur că dorești să ștergi fișierul „:nume”? Această acțiune nu poate fi anulată.', ['nume' => $fisier->nume_original]) }}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Anulează') }}</button>
                            <button type="button" class="btn btn-danger" onclick="document.getElementById('{{ $deleteFormId }}').submit();">
                                <i class="fa-solid fa-trash me-1"></i>{{ __('Șterge fișierul') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    @empty
        <li class="text-muted" data-general-files-empty>{{ __('Nu există fișiere încărcate.') }}</li>
    @endforelse
</ul>
