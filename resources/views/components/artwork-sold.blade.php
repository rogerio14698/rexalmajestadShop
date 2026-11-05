<span {{ $attributes->class(['sold-label']) }}>{{ trim((string) $slot) === '' ? 'Vendido' : $slot }}</span>
