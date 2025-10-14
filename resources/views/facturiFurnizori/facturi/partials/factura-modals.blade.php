@foreach ($facturi as $factura)
    <div class="modal fade text-dark" id="stergeFactura{{ $factura->id }}" tabindex="-1" role="dialog" aria-labelledby="stergeFacturaLabel{{ $factura->id }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="stergeFacturaLabel{{ $factura->id }}">Factura {{ $factura->numar_factura }}</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Sigur ștergi factura <strong>{{ $factura->numar_factura }}</strong> de la <strong>{{ $factura->denumire_furnizor }}</strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                    <form action="{{ route('facturi-furnizori.facturi.destroy', $factura) }}" method="POST" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Șterge factura</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
