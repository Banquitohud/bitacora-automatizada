@extends('layouts.app')

@section('page-title', 'Calendario')
@section('title', 'Calendario')

@section('content')
<div class="rounded-xl border border-gray-200 bg-white shadow-sm" x-data="calendarApp()">
    <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-lg font-bold text-gray-900" x-text="currentMonthName"></h2>
        <div class="flex items-center gap-2">
            <button @click="move(-1)" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50">&larr; Anterior</button>
            <button @click="move(0)" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50">Hoy</button>
            <button @click="move(1)" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50">Siguiente &rarr;</button>
        </div>
    </div>

    <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50 text-center text-xs font-semibold uppercase text-gray-500">
        <div class="px-2 py-3">Lun</div>
        <div class="px-2 py-3">Mar</div>
        <div class="px-2 py-3">Mié</div>
        <div class="px-2 py-3">Jue</div>
        <div class="px-2 py-3">Vie</div>
        <div class="px-2 py-3">Sáb</div>
        <div class="px-2 py-3">Dom</div>
    </div>

    <div class="grid grid-cols-7">
        <template x-for="(cell, i) in cells" :key="i">
            <div class="min-h-24 border-b border-r border-gray-100 p-1.5"
                 :class="cell.isCurrentMonth ? 'bg-white' : 'bg-gray-50'">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium" :class="cell.isCurrentMonth ? 'text-gray-700' : 'text-gray-300'" x-text="cell.day"></span>
                    <span x-show="cell.isToday" class="rounded-full bg-brand-600 px-1.5 py-0.5 text-[10px] font-bold text-white">Hoy</span>
                </div>
                <div class="mt-1 space-y-0.5">
                    <template x-for="ev in cell.events" :key="ev.id">
                        <a :href="ev.url"
                           class="block truncate rounded px-1 py-0.5 text-[10px] font-medium text-white"
                           :style="'background-color: ' + (ev.color || '#2563eb')"
                           :title="ev.title"
                           x-text="ev.title"></a>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <div class="flex flex-wrap gap-4 border-t border-gray-100 p-4 text-xs text-gray-500">
        <span><span class="inline-block h-2.5 w-2.5 rounded-full bg-[#ef4444]"></span> Vencido</span>
        <span><span class="inline-block h-2.5 w-2.5 rounded-full bg-[#f59e0b]"></span> Próximo a vencer</span>
        <span><span class="inline-block h-2.5 w-2.5 rounded-full bg-[#10b981]"></span> En tiempo / completado</span>
        <span><span class="inline-block h-2.5 w-2.5 rounded-full bg-[#2563eb]"></span> Proyecto</span>
        <span><span class="inline-block h-2.5 w-2.5 rounded-full bg-[#8b5cf6]"></span> Tarea</span>
    </div>
</div>

@push('scripts')
<script>
function calendarApp() {
    return {
        year: new Date().getFullYear(),
        month: new Date().getMonth(),
        events: [],
        cells: [],
        eventsByDate: {},

        get currentMonthName() {
            const d = new Date(this.year, this.month, 1);
            return d.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });
        },

        async init() {
            await this.load();
            this.render();
        },

        async load() {
            const start = new Date(this.year, this.month, 1);
            const end = new Date(this.year, this.month + 1, 0);
            const url = "{{ route('calendar.events') }}" +
                '?start=' + start.toISOString().slice(0, 10) +
                '&end=' + end.toISOString().slice(0, 10);
            const res = await fetch(url);
            this.events = await res.json();

            this.eventsByDate = {};
            this.events.forEach(ev => {
                const key = ev.start.slice(0, 10);
                if (!this.eventsByDate[key]) this.eventsByDate[key] = [];
                this.eventsByDate[key].push(ev);
            });
        },

        render() {
            const first = new Date(this.year, this.month, 1);
            const offset = (first.getDay() + 6) % 7;
            const daysInMonth = new Date(this.year, this.month + 1, 0).getDate();
            const prevDays = new Date(this.year, this.month, 0).getDate();
            const today = new Date();
            const key = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');

            this.cells = [];

            for (let i = offset - 1; i >= 0; i--) {
                const d = new Date(this.year, this.month - 1, prevDays - i);
                this.cells.push({ day: d.getDate(), date: key(d), isCurrentMonth: false, isToday: false, events: this.eventsByDate[key(d)] || [] });
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const d = new Date(this.year, this.month, day);
                const isToday = d.getDate() === today.getDate() && d.getMonth() === today.getMonth() && d.getFullYear() === today.getFullYear();
                this.cells.push({ day, date: key(d), isCurrentMonth: true, isToday, events: this.eventsByDate[key(d)] || [] });
            }

            let next = 1;
            while (this.cells.length % 7 !== 0) {
                const d = new Date(this.year, this.month + 1, next);
                this.cells.push({ day: d.getDate(), date: key(d), isCurrentMonth: false, isToday: false, events: this.eventsByDate[key(d)] || [] });
                next++;
            }
        },

        async move(dir) {
            if (dir === 0) {
                this.year = new Date().getFullYear();
                this.month = new Date().getMonth();
            } else {
                this.month += dir;
                if (this.month < 0) { this.month = 11; this.year--; }
                if (this.month > 11) { this.month = 0; this.year++; }
            }
            await this.load();
            this.render();
        },
    };
}
</script>
@endpush
@endsection