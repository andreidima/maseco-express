{{-- Renders ONLY the <tr> rows for the table body. No layout changes. --}}
{{-- Safe, tiny addition: data-id (useful later for targeted updates). --}}

@forelse ($oferte as $oferta)
    <tr data-id="{{ $oferta->id }}">
        <td class="small">{{ \Carbon\Carbon::parse($oferta->data_primirii)->format('d.m.Y H:i') }}</td>
        <td class="text-nowrap">{{ $oferta->incarcare_cod_postal }}</td>
        <td>{{ $oferta->incarcare_localitate }}</td>
        <td>{{ $oferta->incarcare_data_ora }}</td>
        <td class="text-nowrap">{{ $oferta->descarcare_cod_postal }}</td>
        <td>{{ $oferta->descarcare_localitate }}</td>
        <td>{{ $oferta->descarcare_data_ora }}</td>
        <td>{{ $oferta->greutate }}</td>
        <td>{{ Str::limit($oferta->detalii_cursa, 150) }}</td>
        <td class="text-center">
            @if($oferta->gmail_link)
                <a href="{{ $oferta->gmail_link }}" target="_blank">
                    <i class="fa-brands fa-google fs-5"></i>
                </a>
            @else
                -
            @endif
        </td>
        <td>
            <div class="d-flex align-items-end justify-content-end">
                <a href="{{ $oferta->path() }}" title="Vizualizează">
                    <span class="badge px-1 bg-success me-1"><i class="fa-solid fa-eye text-white"></i></span>
                </a>
                <a href="{{ $oferta->path('edit') }}" title="Modifică">
                    <span class="badge px-1 bg-primary me-1"><i class="fa-solid fa-edit text-white"></i></span>
                </a>
                <a href="#" data-bs-toggle="modal" data-bs-target="#stergeOferta{{ $oferta->id }}" title="Șterge">
                    <span class="badge px-1 bg-danger"><i class="fa-solid fa-trash text-white"></i></span>
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr>
        {{-- Keep your original colspan to avoid layout or styling changes --}}
        <td colspan="10" class="text-center text-muted py-3">
            <i class="fa-solid fa-exclamation-circle me-1"></i> Nu există oferte.
        </td>
    </tr>
@endforelse
