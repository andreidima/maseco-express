@extends('layouts.app')

@section('content')
<div class="card mx-3 px-3" style="border-radius:1rem">
  <div class="card-header d-flex align-items-center" style="border-radius:1rem 1rem 0 0">
    <h5 class="mb-0 me-auto"><i class="fa-solid fa-envelope-open-text"></i> Scraped Emails</h5>
    <a href="{{ route('scraped-emails.create') }}"
       class="btn btn-sm btn-success">
      <i class="fa-solid fa-plus"></i> New
    </a>
  </div>
  <div class="card-body">
    <form method="GET" class="row g-2 mb-3">
      <div class="col-md-4">
        <input name="searchSubject" type="text" class="form-control"
               placeholder="Subject" value="{{ $searchSubject }}">
      </div>
      <div class="col-md-4">
        <input name="searchSender" type="text" class="form-control"
               placeholder="Sender email" value="{{ $searchSender }}">
      </div>
      <div class="col-md-4">
        <button class="btn btn-primary">Filter</button>
        <a href="{{ route('scraped-emails.index') }}"
           class="btn btn-secondary">Reset</a>
      </div>
    </form>

    @include('errors')

    <div class="table-responsive">
      <table class="table table-hover">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Subject</th>
            <th>Sender</th>
            <th>Received</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($emails as $email)
          <tr>
            <td>{{ ($emails->currentPage()-1)*$emails->perPage()+$loop->index+1 }}</td>
            <td>{{ Str::limit($email->email_subject, 40) }}</td>
            <td>{{ $email->from_email }}</td>
            <td>{{ $email->date_received }}</td>
            <td class="text-end">
              <a href="{{ route('scraped-emails.show', $email) }}"
                 class="btn btn-sm btn-outline-success">View</a>
              <a href="{{ route('scraped-emails.edit', $email) }}"
                 class="btn btn-sm btn-outline-primary">Edit</a>
              <button data-bs-toggle="modal"
                      data-bs-target="#deleteModal{{ $email->id }}"
                      class="btn btn-sm btn-outline-danger">Delete</button>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center text-muted">
              No records found.
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $emails->appends(request()->except('page'))->links() }}
  </div>
</div>

@foreach($emails as $email)
<div class="modal fade" id="deleteModal{{ $email->id }}" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Delete Email?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Delete “{{ $email->email_subject }}”?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary"
                data-bs-dismiss="modal">Cancel</button>
        <form method="POST"
              action="{{ route('scraped-emails.destroy', $email) }}">
          @csrf @method('DELETE')
          <button class="btn btn-danger">Yes, Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endforeach
@endsection
