<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Control de asistencia</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-lg bg-white p-5 shadow dark:bg-gray-800"><p class="text-sm text-gray-500">Horarios activos</p><p class="text-3xl font-semibold dark:text-white">{{ $activeSchedules }}</p></div>
            <div class="rounded-lg bg-white p-5 shadow dark:bg-gray-800"><p class="text-sm text-gray-500">Permisos pendientes</p><p class="text-3xl font-semibold dark:text-white">{{ $pendingLeaves }}</p></div>
            <div class="rounded-lg bg-white p-5 shadow dark:bg-gray-800"><p class="text-sm text-gray-500">Próximos festivos</p><p class="text-3xl font-semibold dark:text-white">{{ $upcomingHolidays->count() }}</p></div>
        </div>
        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <div class="border-b p-5 dark:border-gray-700"><h3 class="font-semibold dark:text-white">Marcaciones recientes</h3></div>
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700"><thead><tr class="text-left text-gray-500"><th class="p-4">Funcionario</th><th class="p-4">Fecha y hora</th><th class="p-4">Tipo</th><th class="p-4">Origen</th><th class="p-4">Dispositivo</th></tr></thead><tbody class="divide-y dark:divide-gray-700">
                @forelse ($recentMarks as $mark)<tr><td class="p-4 dark:text-gray-200">{{ $mark->employee->Person->name ?? ('#'.$mark->employee_id) }}</td><td class="p-4 dark:text-gray-200">{{ $mark->recorded_at->format('d/m/Y H:i:s') }}</td><td class="p-4 dark:text-gray-200">{{ $mark->type }}</td><td class="p-4 dark:text-gray-200">{{ $mark->source }}</td><td class="p-4 dark:text-gray-200">{{ $mark->device?->device_name ?? 'Manual' }}</td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-gray-500">No existen marcaciones registradas.</td></tr>@endforelse
            </tbody></table></div>
        </div>{{ $recentMarks->links() }}
    </div></div>
</x-app-layout>
