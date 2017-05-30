<li class="collection-item avatar">
    <img src="{{ $package->getIconUrl() }}" alt="{{ $package->package_id }}" onerror="this.src='{{asset('images/packageDefaultIcon.png')}}';" class="">
    <span class="title">
        <a href="{{ $package->getGalleryUrl() }}" class="black-text">
            {{ $package->title }}
        </a>
    </span>
    <p>
        v{{ $package->version }}
        | <i class="tiny material-icons">file_download</i>
        <strong>
            {{ $package->download_count }}
        </strong>
        | <i class="tiny material-icons">alarm</i> {{ $package->created_at->toFormattedDateString() }}
        <br/>
        <span class="hide-on-small-and-down">
        {{ str_limit(empty($package->summary) ? $package->description : $package->summary, 50) }}
        @if(strlen(empty($package->summary) ? $package->description : $package->summary) > 50)
            <a href="{{ $package->getGalleryUrl() }}">More information</a>
        @endif
        </span>
    </p>
    <a href="{{ $package->getGalleryUrl() }}" class="secondary-content"><i class="material-icons">info</i></a>

</li>