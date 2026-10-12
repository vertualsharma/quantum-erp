<x-filament-panels::page>
    <div>
        {{ $this->filterForm }}

        <div class="mt-4">
            <x-filament::button wire:click="loadAction" icon="heroicon-o-magnifying-glass">
                Load
            </x-filament::button>
        </div>
    </div>

    <div class="mt-6">
        {{ $this->table }}
    </div>
</x-filament-panels::page>