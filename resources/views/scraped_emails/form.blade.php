@csrf
<div class="row mb-4 pt-2 rounded-3" style="border:1px solid #e9ecef; border-left:0.25rem darkcyan solid; background-color:rgb(241, 250, 250)">
    <div class="col-lg-6 mb-4">
        <label for="email_subject" class="mb-0 ps-3">Subject<span class="text-danger">*</span></label>
        <input type="text" name="email_subject" id="email_subject" required
               class="form-control bg-white rounded-3 @error('email_subject') is-invalid @enderror"
               value="{{ old('email_subject', $scraped_email->email_subject ?? '') }}">
    </div>

    <div class="col-lg-6 mb-4">
        <label for="from_email" class="mb-0 ps-3">Sender Email<span class="text-danger">*</span></label>
        <input type="email" name="from_email" id="from_email" required
               class="form-control bg-white rounded-3 @error('from_email') is-invalid @enderror"
               value="{{ old('from_email', $scraped_email->from_email ?? '') }}">
    </div>

    <div class="col-lg-6 mb-4">
        <label for="date_received" class="mb-0 ps-3">Date Received<span class="text-danger">*</span></label>
        <input type="datetime-local" name="date_received" id="date_received" required
               class="form-control bg-white rounded-3 @error('date_received') is-invalid @enderror"
               value="{{ old('date_received', \Carbon\Carbon::parse($scraped_email->date_received ?? now())->format('Y-m-d\TH:i')) }}">
    </div>

    <div class="col-lg-6 mb-4">
        <label for="gmail_link" class="mb-0 ps-3">Gmail Link<span class="text-danger">*</span></label>
        <input type="url" name="gmail_link" id="gmail_link" required
               class="form-control bg-white rounded-3 @error('gmail_link') is-invalid @enderror"
               value="{{ old('gmail_link', $scraped_email->gmail_link ?? '') }}">
    </div>
</div>

<div class="row mb-4 pt-2 rounded-3" style="border:1px solid #e9ecef; border-left:0.25rem #e66800 solid; background-color:rgb(241, 250, 250)">
    <div class="col-lg-3 mb-4">
        <label for="load_postal_code" class="mb-0 ps-3">Load Code</label>
        <input type="text" name="load_postal_code" id="load_postal_code"
               class="form-control bg-white rounded-3"
               value="{{ old('load_postal_code', $scraped_email->load_postal_code ?? '') }}">
    </div>
    <div class="col-lg-3 mb-4">
        <label for="load_city" class="mb-0 ps-3">Load City</label>
        <input type="text" name="load_city" id="load_city"
               class="form-control bg-white rounded-3"
               value="{{ old('load_city', $scraped_email->load_city ?? '') }}">
    </div>
    <div class="col-lg-6 mb-4">
        <label for="load_interval" class="mb-0 ps-3">Load Interval</label>
        <input type="text" name="load_interval" id="load_interval"
               class="form-control bg-white rounded-3"
               value="{{ old('load_interval', $scraped_email->load_interval ?? '') }}">
    </div>

    <div class="col-lg-3 mb-4">
        <label for="unload_postal_code" class="mb-0 ps-3">Unload Code</label>
        <input type="text" name="unload_postal_code" id="unload_postal_code"
               class="form-control bg-white rounded-3"
               value="{{ old('unload_postal_code', $scraped_email->unload_postal_code ?? '') }}">
    </div>
    <div class="col-lg-3 mb-4">
        <label for="unload_city" class="mb-0 ps-3">Unload City</label>
        <input type="text" name="unload_city" id="unload_city"
               class="form-control bg-white rounded-3"
               value="{{ old('unload_city', $scraped_email->unload_city ?? '') }}">
    </div>
    <div class="col-lg-6 mb-4">
        <label for="unload_interval" class="mb-0 ps-3">Unload Interval</label>
        <input type="text" name="unload_interval" id="unload_interval"
               class="form-control bg-white rounded-3"
               value="{{ old('unload_interval', $scraped_email->unload_interval ?? '') }}">
    </div>
</div>

<div class="row mb-4 pt-2 rounded-3" style="border:1px solid #e9ecef; border-left:0.25rem darkcyan solid; background-color:rgb(241, 250, 250)">
    <div class="col-lg-12 mb-4">
        <label for="details" class="mb-0 ps-3">Details</label>
        <textarea name="details" id="details" rows="4"
                  class="form-control bg-white rounded-3">{{ old('details', $scraped_email->details ?? '') }}</textarea>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 mb-2 d-flex justify-content-center">
        <button type="submit" class="btn btn-primary text-white me-3 rounded-3">
            <i class="fa-solid fa-save me-1"></i> {{ $buttonText }}
        </button>
        <a class="btn btn-secondary rounded-3" href="{{ Session::get('returnUrl', route('scraped_emails.index')) }}">
            Renunță
        </a>
    </div>
</div>
