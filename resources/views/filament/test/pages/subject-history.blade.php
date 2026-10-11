<x-filament-panels::page>
    <div class="mx-auto w-full max-w-5xl space-y-6">
        {{-- Ringkasan --}}
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <span class="grid size-10 place-items-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                    <x-filament::icon icon="heroicon-o-chart-bar" class="size-5" />
                </span>
                <p class="mt-4 text-3xl font-bold tabular-nums text-gray-950 dark:text-white">{{ number_format($summary['average_score'], 2) }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rata-rata skor per ujian</p>
            </article>
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <span class="grid size-10 place-items-center rounded-xl bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400">
                    <x-filament::icon icon="heroicon-o-check-circle" class="size-5" />
                </span>
                <p class="mt-4 text-3xl font-bold tabular-nums text-gray-950 dark:text-white">{{ $summary['answered_correct'] }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Jawaban benar</p>
            </article>
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <span class="grid size-10 place-items-center rounded-xl bg-danger-50 text-danger-600 dark:bg-danger-500/10 dark:text-danger-400">
                    <x-filament::icon icon="heroicon-o-x-circle" class="size-5" />
                </span>
                <p class="mt-4 text-3xl font-bold tabular-nums text-gray-950 dark:text-white">{{ $summary['answered_wrong'] }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Jawaban salah</p>
            </article>
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <span class="grid size-10 place-items-center rounded-xl bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400">
                    <x-filament::icon icon="heroicon-o-academic-cap" class="size-5" />
                </span>
                <p class="mt-4 text-3xl font-bold tabular-nums text-gray-950 dark:text-white">{{ $summary['exam_count'] }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ujian selesai</p>
            </article>
        </section>

        {{-- Riwayat --}}
        <section class="space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Riwayat ujian</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Klik rincian untuk melihat hasil tiap mata pelajaran.</p>
            </div>

            @forelse ($subjects as $history)
                @php $pct = $history['total'] ? (int) round(($history['correct'] / $history['total']) * 100) : 0; @endphp
                <article class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex flex-wrap items-center justify-between gap-5 px-5 py-5 sm:px-6">
                        <div class="flex min-w-0 flex-1 items-center gap-4">
                            <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                                <x-filament::icon icon="heroicon-o-document-check" class="size-6" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate text-base font-semibold text-gray-950 dark:text-white">{{ $history['exam'] }}</h3>
                                <p class="mt-0.5 inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                                    <x-filament::icon icon="heroicon-o-calendar-days" class="size-4" />
                                    {{ $history['date'] }}
                                </p>
                            </div>
                        </div>

                        <div class="text-end">
                            <p class="text-2xl font-bold tabular-nums text-gray-950 dark:text-white">
                                {{ number_format($history['score'], 2) }}
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">poin</span>
                            </p>
                        </div>

                        <div class="w-full">
                            <div class="mb-1.5 flex items-center justify-between text-xs">
                                <span class="text-gray-500 dark:text-gray-400">
                                    <span class="font-semibold text-success-600 dark:text-success-400">{{ $history['correct'] }} benar</span>
                                    dan
                                    <span class="font-semibold text-danger-600 dark:text-danger-400">{{ $history['wrong'] }} salah</span>
                                    dari {{ $history['total'] }} soal
                                </span>
                                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $pct }}%</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-danger-100 dark:bg-danger-500/20">
                                <div class="h-full rounded-full bg-success-500" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    </div>

                    <details class="group border-t border-gray-950/5 dark:border-white/10">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5 sm:px-6 [&::-webkit-details-marker]:hidden">
                            <span class="inline-flex items-center gap-2">
                                <x-filament::icon icon="heroicon-o-book-open" class="size-4 text-gray-400" />
                                Rincian {{ count($history['subjects']) }} mata pelajaran
                            </span>
                            <x-filament::icon icon="heroicon-m-chevron-down" class="size-5 text-gray-400 transition-transform duration-200 group-open:rotate-180" />
                        </summary>

                        <div class="grid gap-3 border-t border-gray-950/5 bg-gray-50/70 p-4 dark:border-white/10 dark:bg-white/5 sm:grid-cols-2 sm:p-5">
                            @foreach ($history['subjects'] as $subject)
                                @php $subjectPct = $subject['total'] ? (int) round(($subject['correct'] / $subject['total']) * 100) : 0; @endphp
                                <div class="rounded-xl bg-white p-4 ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="min-w-0 truncate text-sm font-semibold text-gray-950 dark:text-white">{{ $subject['name'] }}</p>
                                        <p class="shrink-0 text-sm font-bold tabular-nums text-gray-950 dark:text-white">{{ number_format($subject['score'], 2) }}</p>
                                    </div>
                                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-danger-100 dark:bg-danger-500/20">
                                        <div class="h-full rounded-full bg-success-500" style="width: {{ $subjectPct }}%"></div>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $subject['correct'] }} benar, {{ $subject['wrong'] }} salah, dari {{ $subject['total'] }} soal
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </details>
                </article>
            @empty
                <div class="rounded-2xl bg-white px-6 py-16 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <span class="mx-auto grid size-14 place-items-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/10">
                        <x-filament::icon icon="heroicon-o-book-open" class="size-7" />
                    </span>
                    <h3 class="mt-4 text-base font-semibold text-gray-950 dark:text-white">Belum ada ujian yang selesai</h3>
                    <p class="mx-auto mt-1 max-w-sm text-sm leading-6 text-gray-500 dark:text-gray-400">Kerjakan ujian dari menu Sesi Ujian. Hasilnya akan muncul di sini setelah dikumpulkan.</p>
                </div>
            @endforelse
        </section>
    </div>
</x-filament-panels::page>
