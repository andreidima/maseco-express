@extends('layouts.app')

@section('content')
{{-- <div class="container"> --}}
<div class="mx-2">
    <div class="row justify-content-center">
        <div class="col-md-6 mb-5">
            <div class="card culoare2">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    Bine ai venit <b>{{ auth()->user()->name ?? '' }}</b>!
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-5" id="wysiwyg">
            <tiptap-editor
            {{-- inputvalue='@json(old('content', $post->content ?? ''))'
            inputname="content"
            :height="600" --}}
            ></tiptap-editor>
        </div>
    </div>

</div>
@endsection

