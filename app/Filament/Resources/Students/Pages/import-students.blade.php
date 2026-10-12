<x-filament-panels::page>
    <form wire:submit.prevent="importAction">
        {{ $this->form }}
    </form>

    @if ($importedCount !== null)
        <div class="mt-6 space-y-2">
            <p class="text-sm font-medium">
                Imported: {{ $importedCount }} &nbsp;|&nbsp; Skipped: {{ $skippedCount }}
            </p>

            @if (! empty($importErrors))
                <div class="rounded-lg border border-danger-300 bg-danger-50 p-4 dark:border-danger-700 dark:bg-danger-900/20">
                    <p class="mb-2 text-sm font-semibold text-danger-700 dark:text-danger-300">Row errors:</p>
                    <ul class="list-inside list-disc space-y-1 text-sm text-danger-600 dark:text-danger-300">
                        @foreach ($importErrors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
</x-filament-panels::page>