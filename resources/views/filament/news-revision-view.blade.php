{{-- Read-only preview of a stored news revision (Tajik source shown). --}}
<div class="prose max-w-none dark:prose-invert">
    <h3>{{ data_get($data, 'title.tj') }}</h3>
    <p><em>{{ data_get($data, 'excerpt.tj') }}</em></p>
    {!! data_get($data, 'body.tj') !!}
</div>
