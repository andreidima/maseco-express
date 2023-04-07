@extends('layouts.app')

@section('content')
    <style>
        .fm {
            box-shadow: 0px 5px 20px #8d8d8d;
        }
        .fm-modal .modal-dialog {
            background-color: white;
            margin: 20px;
            padding: 20px;
        }
    </style>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12" id="fm-main-block" style="height: 700px;">
                <div id="fm" style="height: 700px;"></div>
            </div>
            <div class="col-md-12">
                <br>
                <p class="ps-4">
                    *Nu se poate face Upload la mai mult de 100MB odată. Dacă doriți sa faceți upload la mai multe fișiere ce depășesc 100MB, împărțiți pe grupuri mai mici și faceți upload de mai multe ori.
                </p>
            </div>
        </div>
    </div>


    <!-- File manager -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
    <script src="{{ asset('vendor/file-manager/js/file-manager.js') }}"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // document.getElementById('fm-main-block').setAttribute('style', 'height:' + window.innerHeight + 'px');

        fm.$store.commit('fm/setFileCallBack', function(fileUrl) {
          window.opener.fmSetLink(fileUrl);
          window.close();
        });
      });
    </script>
@endsection

