<x-filament-panels::page>
    @php
        $total = max(1, $history['total']);
        $pct = (int) round(($history['correct'] / $total) * 100);
        $tone = $pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
        $circumference = 2 * M_PI * 52;
        $offset = $circumference * (1 - $pct / 100);
    @endphp

    <div class="mx-auto w-full max-w-4xl space-y-6">
        {{-- Ringkasan --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex flex-wrap items-center gap-6 px-6 py-7 sm:px-8">
                <div class="relative size-32 shrink-0">
                    <svg viewBox="0 0 120 120" class="size-full -rotate-90">
                        <circle cx="60" cy="60" r="52" fill="none" stroke-width="10" class="stroke-gray-100 dark:stroke-white/10" />
                        <circle
                            cx="60" cy="60" r="52" fill="none" stroke-width="10" stroke-linecap="round"
                            stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"
                            @class([
                                'stroke-success-500' => $tone === 'success',
                                'stroke-warning-500' => $tone === 'warning',
                                'stroke-danger-500' => $tone === 'danger',
                            ])
                        />
                    </svg>
                    <div class="absolute inset-0 grid place-items-center text-center">
                        <div>
                            <p class="text-3xl font-bold tabular-nums text-gray-950 dark:text-white">{{ $pct }}%</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">benar</p>
                        </div>
                    </div>
                </div>

                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $history['exam'] }}</h1>
                    <p class="mt-1 inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                        <x-filament::icon icon="heroicon-o-calendar-days" class="size-4" />
                        Dikumpulkan {{ $history['submitted_at'] }}
                    </p>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">Skor total</p>
                    <p class="text-4xl font-bold tabular-nums text-gray-950 dark:text-white">
                        {{ number_format($history['score'], 2) }}
                        <span class="text-base font-medium text-gray-500 dark:text-gray-400">poin</span>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-3 divide-x divide-gray-950/5 border-t border-gray-950/5 bg-gray-50/70 dark:divide-white/10 dark:border-white/10 dark:bg-white/5">
                <div class="px-4 py-4 text-center sm:px-8">
                    <p class="text-xl font-bold tabular-nums text-gray-950 dark:text-white">{{ $history['total'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total soal</p>
                </div>
                <div class="px-4 py-4 text-center sm:px-8">
                    <p class="text-xl font-bold tabular-nums text-success-600 dark:text-success-400">{{ $history['correct'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Benar</p>
                </div>
                <div class="px-4 py-4 text-center sm:px-8">
                    <p class="text-xl font-bold tabular-nums text-danger-600 dark:text-danger-400">{{ $history['wrong'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Salah</p>
                </div>
            </div>
        </div>

        {{-- Pembahasan per mapel --}}
        @foreach ($history['subjects'] as $subject)
            @php $subjectPct = $subject['total'] ? (int) round(($subject['correct'] / $subject['total']) * 100) : 0; @endphp
            <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <header class="flex flex-wrap items-center justify-between gap-4 px-5 py-5 sm:px-7">
                    <div class="flex items-center gap-3">
                        <span class="grid size-11 place-items-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                            <x-filament::icon icon="heroicon-o-book-open" class="size-6" />
                        </span>
                        <div>
                            <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ $subject['name'] }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $subject['correct'] }} benar dari {{ $subject['total'] }} soal</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="hidden w-32 sm:block">
                            <div class="h-2 overflow-hidden rounded-full bg-danger-100 dark:bg-danger-500/20">
                                <div class="h-full rounded-full bg-success-500" style="width: {{ $subjectPct }}%"></div>
                            </div>
                        </div>
                        <p class="text-lg font-bold tabular-nums text-gray-950 dark:text-white">
                            {{ number_format($subject['score'], 2) }}
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">poin</span>
                        </p>
                    </div>
                </header>

                <ol class="divide-y divide-gray-950/5 border-t border-gray-950/5 dark:divide-white/10 dark:border-white/10">
                    @foreach ($subject['questions'] as $index => $question)
                        <li class="px-5 py-5 sm:px-7">
                            <div class="flex items-start gap-4">
                                <span @class([
                                    'grid size-9 shrink-0 place-items-center rounded-lg text-sm font-bold tabular-nums',
                                    'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-300' => $question['is_correct'],
                                    'bg-danger-100 text-danger-700 dark:bg-danger-500/20 dark:text-danger-300' => ! $question['is_correct'],
                                ])>{{ $index + 1 }}</span>

                                <div class="min-w-0 flex-1 space-y-4">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="min-w-0 max-w-none whitespace-pre-line text-sm leading-7 text-gray-900 dark:text-gray-100 [&_img]:my-3 [&_img]:max-w-full [&_img]:rounded-lg [&_p]:my-1">{!! $question['content'] !!}</div>
                                        <span @class([
                                            'inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold',
                                            'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' => $question['is_correct'],
                                            'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-400' => ! $question['is_correct'],
                                        ])>
                                            <x-filament::icon :icon="$question['is_correct'] ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'" class="size-4" />
                                            {{ $question['is_correct'] ? 'Benar' : 'Salah' }}
                                        </span>
                                    </div>

                                    <div @class(['grid gap-3', 'sm:grid-cols-2' => ! $question['is_correct']])>
                                        <div @class([
                                            'rounded-xl px-4 py-3 ring-1',
                                            'bg-success-50 ring-success-600/20 dark:bg-success-500/10 dark:ring-success-400/20' => $question['is_correct'],
                                            'bg-danger-50 ring-danger-600/20 dark:bg-danger-500/10 dark:ring-danger-400/20' => ! $question['is_correct'],
                                        ])>
                                            <p @class([
                                                'text-xs font-semibold',
                                                'text-success-700 dark:text-success-400' => $question['is_correct'],
                                                'text-danger-700 dark:text-danger-400' => ! $question['is_correct'],
                                            ])>Jawabanmu</p>
                                            <p class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-100">{{ $question['answer'] ?? 'Tidak dijawab' }}</p>
                                        </div>

                                        @unless ($question['is_correct'])
                                            <div class="rounded-xl bg-success-50 px-4 py-3 ring-1 ring-success-600/20 dark:bg-success-500/10 dark:ring-success-400/20">
                                                <p class="text-xs font-semibold text-success-700 dark:text-success-400">Kunci jawaban</p>
                                                <p class="mt-1 text-sm leading-6 text-gray-900 dark:text-gray-100">{{ $question['correct_answer'] ?? 'Tidak tersedia' }}</p>
                                            </div>
                                        @endunless
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endforeach
    </div>
</x-filament-panels::page>
