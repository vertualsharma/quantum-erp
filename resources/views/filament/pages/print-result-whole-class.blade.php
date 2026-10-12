<x-filament-panels::page>
    <form wire:submit.prevent="print">
        {{ $this->form }}

        <div class="mt-4 flex flex-wrap gap-3">
            <x-filament::button type="button" wire:click="openPrint" icon="heroicon-o-printer" color="primary">
                Print Result (New Tab)
            </x-filament::button>

            <x-filament::button type="submit" icon="heroicon-o-arrow-down-tray" color="gray">
                Download PDF
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>