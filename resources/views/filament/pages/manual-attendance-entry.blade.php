<x-filament-panels::page>
    <div>
        {{ $this->filterForm }}

        <div class="mt-4">
            <x-filament::button wire:click="loadAction" icon="heroicon-o-arrow-down-tray">
                Load
            </x-filament::button>
        </div>
    </div>

    @if ($loaded && $selectedExamClassSetupId)
        <div class="mt-6 flex items-end gap-3">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium">Total Attendance for class</span>
                <input
                    type="number"
                    wire:model="totalAttendanceData.total_attendance_days"
                    class="fi-input block w-24 rounded-lg border-gray-300 text-sm"
                />
                <span class="text-sm">days</span>
            </div>
            <x-filament::button wire:click="updateTotalAttendance" icon="heroicon-o-check" size="sm">
                Update
            </x-filament::button>
        </div>

        @if (empty($totalAttendanceData['total_attendance_days']))
            <div class="mt-6">
                <h2 class="text-2xl text-gray-500">Setup Total attendance first.</h2>
            </div>
        @else
            <div class="mt-6">
                {{ $this->table }}
            </div>
        @endif
    @endif
</x-filament-panels::page>