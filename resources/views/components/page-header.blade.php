<div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            @isset($pretitle)
                <div class="mb-2">
                    {{ $pretitle }}
                </div>
            @endisset
            <h1 class="text-3xl font-bold text-gray-900">{{ $title }}</h1>
            @if(isset($subtitle) && $subtitle)
                <p class="mt-2 text-sm text-gray-600">{{ $subtitle }}</p>
            @endif
            @if($showDate ?? false)
                <p class="mt-1 text-sm text-gray-500">{{ now()->format('l, F j, Y') }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 mt-2 sm:mt-0">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
