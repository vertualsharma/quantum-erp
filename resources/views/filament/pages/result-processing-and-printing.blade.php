<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white p-6 shadow-sm dark:bg-gray-900 mb-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold">Process Result</h2>
                <p class="text-sm text-gray-500">Choose the year, class, and exam to calculate the result.</p>
            </div>
        </div>
        <form wire:submit.prevent="processResult">
            {{ $this->processForm }}
            <div class="mt-4 flex justify-end">
                <x-filament::button type="submit" icon="heroicon-o-arrow-path">
                    Process Result
                </x-filament::button>
            </div>
        </form>
    </div>

    <div class="fi-section rounded-xl bg-white p-6 shadow-sm dark:bg-gray-900 mb-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold">Print Result</h2>
                <p class="text-sm text-gray-500">Print the whole class result with the selected template.</p>
            </div>
        </div>
        <form wire:submit.prevent="printWholeClass">
            {{ $this->printWholeClassForm }}
            <div class="mt-4 flex flex-wrap justify-end gap-3">
                <x-filament::button type="button" wire:click="openPrintWholeClass" icon="heroicon-o-printer" color="primary">
                    Print Result (New Tab)
                </x-filament::button>
                <x-filament::button type="submit" icon="heroicon-o-arrow-down-tray" color="gray">
                    Download PDF
                </x-filament::button>
            </div>
        </form>
    </div>

    <div class="fi-section rounded-xl bg-white p-6 shadow-sm dark:bg-gray-900">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold">Print Result (Student-wise)</h2>
                <p class="text-sm text-gray-500">Print an individual student grade sheet with the selected template.</p>
            </div>
        </div>
        <form wire:submit.prevent="printStudentWise">
            {{ $this->printStudentWiseForm }}
            <div class="mt-4 flex flex-wrap justify-end gap-3">
                <x-filament::button type="button" wire:click="openPrintStudentWise" icon="heroicon-o-printer" color="primary">
                    Print Result (New Tab)
                </x-filament::button>
                <x-filament::button type="submit" icon="heroicon-o-arrow-down-tray" color="gray">
                    Download PDF
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>