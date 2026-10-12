<x-filament-panels::page>
    <form wire:submit.prevent="preview">
        {{ $this->form }}

        <div class="mt-4 flex flex-wrap gap-3">
            <x-filament::button type="submit" icon="heroicon-o-eye">
                Preview Grade Sheet
            </x-filament::button>

            <x-filament::button type="button" wire:click="printDirect" icon="heroicon-o-printer" color="success">
                Print Grade Sheet (New Tab)
            </x-filament::button>

            <x-filament::button type="button" wire:click="downloadPdf" icon="heroicon-o-arrow-down-tray" color="gray">
                Download PDF
            </x-filament::button>

            <x-filament::button type="button" wire:click="openClassSummary" color="info" icon="heroicon-o-document-chart-bar">
                Print Class Summary
            </x-filament::button>

            <x-filament::button type="button" wire:click="printClassSummary" color="gray" icon="heroicon-o-arrow-down-tray">
                Download Class Summary
            </x-filament::button>
        </div>
    </form>

    @if($previewHtml)
        <div class="mt-6 border rounded-lg overflow-hidden shadow-sm bg-white" style="max-width: 210mm; margin: 1.5rem auto;">
            {!! $previewHtml !!}
        </div>
    @endif
</x-filament-panels::page>