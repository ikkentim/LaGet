@extends('app')

@section('title')
    Browse
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col m12">
                <h1>Available Packages</h1>
                <p>
                    Below all available packages in this repository are listed.
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col m12">
                <ul class="collection">
                    @foreach($packages as $package)
                        @include('partials.package', ['package' => $package])
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col m12">
                {!! with(new \Laget\Presenters\Pagination($packages))->render() !!}
            </div>
        </div>
    </div>
@endsection