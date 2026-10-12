<x-filament-panels::page>
    <form wire:submit.prevent="export">
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button type="submit" icon="heroicon-o-arrow-down-tray">
                Export
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>