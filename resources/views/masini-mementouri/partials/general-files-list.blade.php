@php($files = $fisiere ?? $masina->fisiereGenerale)

<ul class="list-unstyled mb-0">
    @forelse ($files as $fisier)
        @php
            $previewRoute = route('masini-mementouri.fisiere-generale.preview', [$masina, $fisier]);
            $downloadRoute = route('masini-mementouri.fisiere-generale.download', [$masina, $fisier]);
            $deleteRoute = route('masini-mementouri.fisiere-generale.destroy', [$masina, $fisier]);
        @endphp
        <li class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center py-2 border-bottom">
            <div class="me-md-3">
                <i class="fa-solid {{ $fisier->iconClass() }} me-2"></i>
                <span class="fw-semibold">{{ $fisier->nume_original }}</span>
                <small class="text-muted ms-2">{{ number_format(($fisier->dimensiune ?? 0) / 1024, 1) }} KB</small>
                @if ($fisier->uploaded_by_name)
                    <small class="text-muted ms-2">
                        {{ $fisier->uploaded_by_name }}
                        @if ($fisier->uploaded_by_email)
                            &lt;{{ $fisier->uploaded_by_email }}&gt;
                        @endif
                    </small>
                @endif
                @if ($fisier->created_at)
                    <small class="text-muted ms-2">{{ $fisier->created_at->format('d.m.Y H:i') }}</small>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="btn-group btn-group-sm" role="group">
                    @if ($fisier->isPreviewable())
                        <a href="{{ $previewRoute }}" class="btn btn-outline-primary border" target="_blank" rel="noopener" title="{{ __('Previzualizează fișierul') }}">
                            <i class="fa-solid fa-eye me-1"></i>
                            <span class="d-none d-sm-inline">{{ __('Previzualizează') }}</span>
                        </a>
                    @endif
                    <a href="{{ $downloadRoute }}"
                       class="btn btn-outline-secondary border"
                       download="{{ $fisier->downloadName() }}"
                       title="{{ __('Descarcă fișierul') }}">
                        <i class="fa-solid fa-download me-1"></i>
                        <span class="d-none d-sm-inline">{{ __('Descarcă') }}</span>
                    </a>
                </div>
                <form method="POST" action="{{ $deleteRoute }}"
                      onsubmit="return confirm('{{ __('Ștergi fișierul?') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger border border-dark">
                        <i class="fa-solid fa-trash me-1"></i>{{ __('Șterge') }}
                    </button>
                </form>
            </div>
        </li>
    @empty
        <li class="text-muted">{{ __('Nu există fișiere încărcate.') }}</li>
    @endforelse
</ul>
