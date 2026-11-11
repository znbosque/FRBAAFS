@foreach ($navItems as $item)
    <a href="{{ route($item['route']) }}"
       class="flex items-center gap-3 rounded-lg px-3 py-2 transition
              {{ request()->routeIs($item['active'])
                  ? 'bg-white/10 font-medium text-white shadow-inner'
                  : 'text-maroon-200/80 hover:bg-white/5 hover:text-white' }}">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
        </svg>
        {{ $item['label'] }}
    </a>
@endforeach
