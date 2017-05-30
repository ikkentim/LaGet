@extends('app')

@section('title')
    Browse
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col m12">
                <h1>Available Packages</h1>
                @if($filter == 'most')
                    <p>
                        Below all available packages are listed by most downloaded.
                    </p>
                @elseif($filter == 'least')
                    <p>
                        Below all available packages are listed by least downloaded.
                    </p>
                @elseif($filter == 'new')
                    <p>
                        Below all available packages are listed from newest to oldest.
                    </p>
                @elseif($filter == 'old')
                    <p>
                        Below all available packages are listed from oldest to newest.
                    </p>
                @elseif($filter == 'title')
                    <p>
                        Below all available packages are listed by alphabetically.
                    </p>
                @else
                    <p>
                        Below all available packages in this repository are listed.
                    </p>
                @endif
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