<div class="col-md-3">
    <div class="gallery-item">
        <div class="vcenter">
            <a href="{{ action('OpusController@galleryShow', [$gallery->id, $opus->id]) }}">
                <img src="/{{ $opus->getThumbnail() }}" alt=""></a>
            <h5>
                <strong><a href="{{ action('OpusController@galleryShow', [$gallery->id, $opus->id]) }}">{{ $opus->title }}</a></strong>
                <br>
                <a href="{{ action('ProfileController@show', $opus->user->slug) }}">{{ $opus->user->name }}</a>
            </h5>
            @if(Auth::check() and (Auth::user()->isOwner($opus) or Auth::user()->atLeastHasRole(config('roles.globalModerator'))))
                @include('partials._operations', ['model' => $opus, 'controller' => 'OpusController'])
            @endif
        </div>
    </div>
</div>