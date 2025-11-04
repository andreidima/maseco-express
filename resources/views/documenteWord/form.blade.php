@csrf

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="client">
    <div class="col-lg-12 mb-0">
        <div class="row mb-0">
            <div class="col-lg-9 mb-4">
                <label for="nume" class="mb-0 ps-3">Nume<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('nume') ? 'is-invalid' : '' }}"
                    name="nume"
                    placeholder=""
                    value="{{ old('nume', $documentWord->nume) }}"
                    required>
            </div>
            @can('is-admin')
                <div class="col-lg-3 mb-4">
                    <label for="nivel_acces" class="mb-0 ps-3">Nivel acces<span class="text-danger">*</span></label>
                    <select class="form-select bg-white rounded-3 {{ $errors->has('nivel_acces') ? 'is-invalid' : '' }}" name="nivel_acces">
                        <option value="1" {{ old('nivel_acces', $documentWord->nivel_acces) == "1" ? 'selected' : '' }}>Admin</option>
                        <option value="2" {{ old('nivel_acces', $documentWord->nivel_acces) == "2" ? 'selected' : '' }}>Operator</option>
                    </select>
                </div>
            @endcan
            <div class="col-lg-12 mb-2" id="wysiwyg">
                <label for="continut" class="form-label mb-0 ps-3">Conținut</label>

                <!-- PRINT-AREA start -->
                <div id="print-area">
                    <tiptap-editor
                        :inputvalue='@json(old('continut', $documentWord->continut ?? ''))'
                        inputname="continut"
                        height="600px"
                        upload-url="{{ route('documente-word.images') }}"
                        :upload-headers='@json(['X-CSRF-TOKEN' => csrf_token()])'
                    ></tiptap-editor>
                </div>
                <!-- PRINT-AREA end -->

            </div>
        </div>
        <div class="row">
            <div class="col-lg-4">
            </div>
            <div class="col-lg-4 mb-2 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                @if ($documentWord->exists)
                    <a class="btn btn-secondary rounded-3" href="{{ route('documentWord.unlock', $documentWord->id) }}">Renunță</a>
                @else
                    <a class="btn btn-secondary rounded-3" href="{{ Session::get('documentWordReturnUrl') }}">Renunță</a>
                @endif
            </div>
            <div class="col-lg-4 text-end">

                {{-- <button type="button" onclick="window.print()">Print</button> --}}

                @if(Route::is('documente-word.create'))
                    <!-- Create Mode -->
                    <button type="text" class="btn btn-danger text-white rounded-3" disabled>Șterge Document Word</button>
                @elseif(Route::is('documente-word.edit'))
                    <a class="btn btn-danger rounded-3" href="#"
                        data-bs-toggle="modal"
                        data-bs-target="#stergeDocumentWord"
                        title="Șterge document word"
                        >Șterge Document Word</a>
                @endif
            </div>
        </div>
    </div>
</div>


<style>
    /* === print.css or inside a <style> === */
    @page {
    size: A4 landscape;
    margin: 0;
    }

    @media print {
        /* hide everything… */
        body * {
            visibility: hidden !important;
        }
        /* …except the editor container */
        #editor-content,
        #editor-content * {
            visibility: visible !important;
        }
        /* position it full-width at top left */
        #editor-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            overflow: visible !important;
        }

        /* hide tiptap toolbars/menus inside the editor */
        .editor-toolbar,
        .floating-menu {
            display: none !important;
        }

        /* make your ProseMirror tables scale to page width */
        .ProseMirror table {
            width: 100% !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
        }
        .ProseMirror table th,
        .ProseMirror table td {
            word-wrap: break-word;
            /* optional: shrink padding */
            padding: 0.2em !important;
        }
    }
</style>
