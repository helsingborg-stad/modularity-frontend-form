@if (empty($hideTitle) && !empty($postTitle))
    <div class="c-card__header">
        @typography([
            'element' => $element ?? 'h2',
            'variant' => $variant ?? 'h2',
            'classList' => $classList ?? ['module-title', 'u-margin--0']
        ])
            {!! $postTitle !!}
        @endtypography
    </div>
@endif