@csrf

{{-- @php
    dd($user, old('email', $user->email));

@endphp --}}

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px">
    <div class="col-lg-12 px-4 py-2 mb-0 mx-auto">
        <input type="hidden" name="id" value="{{ $user->id }}">

        <div class="row mb-0">
            <div class="col-lg-6 mb-4">
                <label for="name" class="mb-0 ps-3">Nume<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('name') ? 'is-invalid' : '' }}"
                    name="name"
                    placeholder=""
                    value="{{ old('name', $user->name) }}"
                    required>
            </div>
            <div class="col-lg-3 mb-4">
                <label for="role" class="mb-0 ps-3">Rol<span class="text-danger">*</span></label>
                <select class="form-select bg-white rounded-3 {{ $errors->has('role') ? 'is-invalid' : '' }}" name="role">
                    <option selected></option>
                    <option value="1" {{ old('role', $user->role) == "1" ? 'selected' : '' }}>Admin</option>
                    <option value="2" {{ old('role', $user->role) == "2" ? 'selected' : '' }}>Dispecer</option>
                </select>
            </div>
            <div class="col-lg-3 mb-4">
                <div class="text-center">
                    <label class="mb-0 ps-3">Cont activ<span class="text-danger">*</span></label>
                    <div class="d-flex py-1 justify-content-center">
                        <div class="form-check me-4">
                            <input class="form-check-input" type="radio" value="1" name="activ" id="activ_da"
                                {{ old('activ', $user->activ) == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="activ_da">DA</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="0" name="activ" id="activ_nu"
                                {{ old('activ', $user->activ) == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="activ_nu">NU</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <label for="email" class="mb-0 ps-3">Email<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    name="email"
                    placeholder=""
                    value="{{ old('email', $user->email) }}"
                    autocomplete="off">
            </div>
            <div class="col-lg-6 mb-4">
                <label for="telefon" class="mb-0 ps-3">Telefon</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('telefon') ? 'is-invalid' : '' }}"
                    name="telefon"
                    placeholder=""
                    value="{{ old('telefon', $user->telefon) }}"
                    required>
            </div>
            <div class="col-lg-6 mb-4">
                <label for="password" class="mb-0 ps-3">Parola</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="new-password"
                        placeholder="{{ str_contains(url()->current(), '/modifica') ? '********' : '' }}"
                    >
            </div>
            <div class="col-lg-6 mb-4">
                <label for="password_confirmation" class="mb-0 ps-3">Confirmare parolă</label>
                <input id="password_confirmation" type="password" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation"
                    placeholder="{{ str_contains(url()->current(), '/modifica') ? '********' : '' }}"
                >
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mb-0 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('userReturnUrl') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
