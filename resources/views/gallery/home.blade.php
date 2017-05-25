@extends('app')

@section('title')
    Home
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col m12">
                <h1>What is LaGet?</h1>
                <p>
                    LaGet is a NuGet package manager repository server.
                    To browse this repository, add <code>{{ route('api.index') }}</code> to your NuGet
                    Package Manager configuration.
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col m4">
                <div class="card teal lighten-2">
                    <div class="card-content">
                        <span class="card-title">Unique Packages</span>
                        <p><strong>{{ $uniquePackages }}</strong> unique packages.</p>
                    </div>
                </div>
            </div>
            <div class="col m4">
                <div class="card teal lighten-2">
                    <div class="card-content">
                        <span class="card-title">Total Downloads</span>
                        <p><strong>{{ $totalDownloads }}</strong> total package downloads.</p>
                    </div>
                </div>
            </div>
            <div class="col m4">
                <div class="card teal lighten-2">
                    <div class="card-content">
                        <span class="card-title">Total Packages</span>
                        <p><strong>{{ $totalPackages }}</strong> total packages.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col m12">
                <a class="waves-effect waves-light btn" href="{{ route('packages.index', ['by' => 'most']) }}">
                    <i class="material-icons left">library_books</i>
                    Browse Packages
                </a>
            </div>
        </div>
    </div>
@endsection
