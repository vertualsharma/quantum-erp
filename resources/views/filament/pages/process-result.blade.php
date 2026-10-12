<x-filament-panels::page>
    <div>
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button wire:click="processResult" icon="heroicon-o-arrow-path">
                Process Result
            </x-filament::button>
        </div>
    </div>

    <div class="mt-6">
        {{ $this->table }}
    </div>
</x-filament-panels::page>