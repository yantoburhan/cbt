<x-filament-panels::page>
    @if ($isSubmitted)
        <div class="mx-auto max-w-xl py-10 text-center">
            <span class="mx-auto grid size-20 place-items-center rounded-full bg-success-50 text-success-600 ring-8 ring-success-50/50 dark:bg-success-500/10 dark:text-success-400 dark:ring-success-500/5">
                <x-filament::icon icon="heroicon-o-check-badge" class="size-10" />
            </span>
            <h1 class="mt-6 text-2xl font-bold tracking-tight text-gray-950 dark:text-white">Ujian sudah dikumpulkan</h1>
            <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-gray-600 dark:text-gray-400">
                Semua jawabanmu tersimpan. Lihat nilai dan pembahasannya dari daftar sesi ujian.
            </p>
            <div class="mt-6">
                <x-filament::button tag="a" :href="\App\Filament\Test\Resources\Exams\ExamResource::getUrl('index')" icon="heroicon-o-arrow-left">
                    Kembali ke sesi ujian
                </x-filament::button>
            </div>
        </div>
    @else
        @php
            $question = $questions[$currentIndex];
            $total = count($questions);
            $completion = $total ? (int) round(($answeredCount / $total) * 100) : 0;
            $doubtCount = count(array_filter($doubtfulQuestions));
            $emptyCount = $total - $answeredCount;
            $isDoubtful = $doubtfulQuestions[$question['id']] ?? false;
            $selected = $selectedAnswers[$question['id']] ?? null;
        @endphp

        <div class="mx-auto w-full max-w-4xl space-y-5">
            {{-- Bar atas: mapel, progres, timer --}}
            <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                            <x-filament::icon icon="heroicon-o-pencil-square" class="size-6" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-base font-semibold text-gray-950 dark:text-white">{{ $question['subject'] ?? 'Sesi ujian' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Soal {{ $currentIndex + 1 }} dari {{ $total }}</p>
                        </div>
                    </div>

                    @if ($remainingSeconds > 0)
                        <div
                            wire:ignore
                            x-data="{
                                end: Date.now() + {{ $remainingSeconds }} * 1000,
                                left: {{ $remainingSeconds }},
                                init() { setInterval(() => { this.left = Math.max(0, Math.round((this.end - Date.now()) / 1000)) }, 1000) },
                                get text() {
                                    const h = Math.floor(this.left / 3600), m = Math.floor((this.left % 3600) / 60), s = this.left % 60;
                                    const p = (n) => String(n).padStart(2, '0');
                                    return (h > 0 ? p(h) + ':' : '') + p(m) + ':' + p(s);
                                },
                            }"
                            class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold tabular-nums ring-1 transition-colors"
                            :class="left === 0
                                ? 'bg-danger-50 text-danger-700 ring-danger-600/20 dark:bg-danger-500/10 dark:text-danger-400 dark:ring-danger-400/20'
                                : left <= 60
                                    ? 'animate-pulse bg-danger-50 text-danger-700 ring-danger-600/20 dark:bg-danger-500/10 dark:text-danger-400 dark:ring-danger-400/20'
                                    : left <= 300
                                        ? 'bg-warning-50 text-warning-700 ring-warning-600/20 dark:bg-warning-500/10 dark:text-warning-400 dark:ring-warning-400/20'
                                        : 'bg-gray-50 text-gray-700 ring-gray-950/10 dark:bg-white/5 dark:text-gray-200 dark:ring-white/10'"
                        >
                            <x-filament::icon icon="heroicon-o-clock" class="size-5" />
                            <span x-text="left === 0 ? 'Waktu habis' : text"></span>
                            <span class="hidden font-normal opacity-70 sm:inline" x-show="left > 0">tersisa</span>
                        </div>
                    @endif
                </div>

                <div class="mt-4">
                    <div class="mb-1.5 flex items-center justify-between text-xs">
                        <span class="text-gray-500 dark:text-gray-400">{{ $answeredCount }} dari {{ $total }} soal terjawab</span>
                        <span class="font-semibold text-primary-600 dark:text-primary-400">{{ $completion }}%</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                        <div class="h-full rounded-full bg-primary-600 transition-all duration-500" style="width: {{ $completion }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Kartu soal --}}
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="px-5 pb-2 pt-6 sm:px-8 sm:pt-8">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <span class="inline-flex items-center rounded-lg bg-primary-50 px-3 py-1 text-sm font-bold text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">
                            Soal {{ $currentIndex + 1 }}
                        </span>
                        @if ($isDoubtful)
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-warning-50 px-2.5 py-1 text-xs font-semibold text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">
                                <x-filament::icon icon="heroicon-m-flag" class="size-4" />
                                Ditandai ragu
                            </span>
                        @endif
                    </div>
                    <div class="max-w-none whitespace-pre-line text-base leading-8 text-gray-900 dark:text-gray-100 [&_img]:my-3 [&_img]:max-w-full [&_img]:rounded-lg [&_p]:my-2 [&_table]:my-3 [&_td]:border [&_td]:px-2 [&_td]:py-1 [&_th]:border [&_th]:px-2 [&_th]:py-1">{!! $question['content'] !!}</div>
                </div>

                <fieldset class="px-5 pb-6 pt-4 sm:px-8 sm:pb-8">
                    <legend class="sr-only">Pilihan jawaban</legend>
                    <div class="grid gap-3">
                        @foreach ($question['answers'] as $answer)
                            @php $isSelected = $selected === $answer['id']; @endphp
                            <label
                                wire:key="question-{{ $question['id'] }}-answer-{{ $answer['id'] }}"
                                @class([
                                    'group relative flex w-full cursor-pointer items-center gap-4 rounded-xl px-4 py-3.5 ring-1 transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-primary-500',
                                    'bg-primary-50 ring-2 ring-primary-600 dark:bg-primary-500/10 dark:ring-primary-500' => $isSelected,
                                    'bg-white ring-gray-950/10 hover:bg-gray-50 hover:ring-primary-400 dark:bg-white/5 dark:ring-white/10 dark:hover:bg-white/10 dark:hover:ring-primary-500' => ! $isSelected,
                                ])
                            >
                                <input
                                    type="radio"
                                    name="answer-{{ $question['id'] }}"
                                    value="{{ $answer['id'] }}"
                                    wire:click="chooseAnswer({{ $answer['id'] }})"
                                    @checked($isSelected)
                                    class="sr-only"
                                >
                                <span @class([
                                    'grid size-9 shrink-0 place-items-center rounded-lg text-sm font-bold transition',
                                    'bg-primary-600 text-white' => $isSelected,
                                    'bg-gray-100 text-gray-600 group-hover:bg-primary-100 group-hover:text-primary-700 dark:bg-white/10 dark:text-gray-300 dark:group-hover:bg-primary-500/20 dark:group-hover:text-primary-300' => ! $isSelected,
                                ])>{{ $answer['letter'] }}</span>
                                <span class="min-w-0 flex-1 text-sm leading-6 text-gray-900 dark:text-gray-100 sm:text-base">{{ $answer['text'] }}</span>
                                @if ($isSelected)
                                    <x-filament::icon icon="heroicon-m-check-circle" class="size-6 shrink-0 text-primary-600 dark:text-primary-400" />
                                @endif
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                @error('exam')
                    <div role="alert" class="mx-5 mb-5 flex items-start gap-3 rounded-xl bg-danger-50 px-4 py-3 text-sm text-danger-700 ring-1 ring-danger-600/20 dark:bg-danger-500/10 dark:text-danger-400 sm:mx-8">
                        <x-filament::icon icon="heroicon-m-exclamation-circle" class="mt-0.5 size-5 shrink-0" />
                        {{ $message }}
                    </div>
                @enderror

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-950/5 bg-gray-50/70 px-5 py-4 dark:border-white/10 dark:bg-white/5 sm:px-8">
                    <div class="flex flex-wrap gap-2">
                        <x-filament::button color="gray" outlined icon="heroicon-o-arrow-left" wire:click="previousQuestion" :disabled="$currentIndex === 0">
                            Sebelumnya
                        </x-filament::button>
                        <x-filament::button
                            :color="$isDoubtful ? 'warning' : 'gray'"
                            :outlined="! $isDoubtful"
                            :icon="$isDoubtful ? 'heroicon-m-flag' : 'heroicon-o-flag'"
                            wire:click="toggleDoubtful"
                        >
                            {{ $isDoubtful ? 'Batalkan ragu' : 'Tandai ragu' }}
                        </x-filament::button>
                    </div>

                    @if ($currentIndex < $total - 1)
                        <x-filament::button icon="heroicon-o-arrow-right" icon-position="after" wire:click="nextQuestion">
                            Berikutnya
                        </x-filament::button>
                    @else
                        <x-filament::button color="success" icon="heroicon-o-paper-airplane" wire:click="submitExam" wire:confirm="Yakin ingin mengumpulkan ujian? Jawaban tidak bisa diubah lagi." :disabled="$emptyCount > 0">
                            Kumpulkan ujian
                        </x-filament::button>
                    @endif
                </div>
            </div>

            {{-- Navigasi soal (di bawah) --}}
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-950/5 px-5 py-4 dark:border-white/10 sm:px-6">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-950 dark:text-white">Navigasi soal</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Klik nomor untuk berpindah soal.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-600 dark:text-gray-300">
                        <span class="inline-flex items-center gap-1.5"><i class="size-3 rounded bg-success-500"></i>Terjawab {{ $answeredCount }}</span>
                        <span class="inline-flex items-center gap-1.5"><i class="size-3 rounded bg-warning-500"></i>Ragu {{ $doubtCount }}</span>
                        <span class="inline-flex items-center gap-1.5"><i class="size-3 rounded ring-1 ring-inset ring-gray-400"></i>Kosong {{ $emptyCount }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-5 gap-2.5 p-5 sm:grid-cols-10 sm:p-6 lg:grid-cols-[repeat(15,minmax(0,1fr))]">
                    @foreach ($questions as $index => $item)
                        @php
                            $qid = $item['id'];
                            $isCurrent = $index === $currentIndex;
                            $isAnswered = ($selectedAnswers[$qid] ?? null) !== null;
                            $isDoubt = $doubtfulQuestions[$qid] ?? false;
                        @endphp
                        <button
                            type="button"
                            wire:click="goToQuestion({{ $index }})"
                            wire:key="question-nav-{{ $qid }}"
                            aria-label="Soal {{ $index + 1 }}"
                            @if ($isCurrent) aria-current="step" @endif
                            @class([
                                'relative grid aspect-square place-items-center rounded-lg text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900',
                                'bg-primary-600 text-white shadow-md shadow-primary-600/30 ring-2 ring-primary-600 ring-offset-2 dark:ring-offset-gray-900' => $isCurrent,
                                'bg-warning-100 text-warning-800 hover:bg-warning-200 dark:bg-warning-500/20 dark:text-warning-300' => ! $isCurrent && $isDoubt,
                                'bg-success-100 text-success-800 hover:bg-success-200 dark:bg-success-500/20 dark:text-success-300' => ! $isCurrent && ! $isDoubt && $isAnswered,
                                'bg-white text-gray-600 ring-1 ring-inset ring-gray-950/10 hover:bg-gray-50 dark:bg-transparent dark:text-gray-300 dark:ring-white/15 dark:hover:bg-white/5' => ! $isCurrent && ! $isDoubt && ! $isAnswered,
                            ])
                        >
                            {{ $index + 1 }}
                            @if ($isDoubt && $isAnswered && ! $isCurrent)
                                <span class="absolute -right-1 -top-1 size-2.5 rounded-full bg-success-500 ring-2 ring-white dark:ring-gray-900"></span>
                            @endif
                        </button>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-950/5 bg-gray-50/70 px-5 py-4 dark:border-white/10 dark:bg-white/5 sm:px-6">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        @if ($emptyCount > 0)
                            Masih ada <strong class="text-gray-950 dark:text-white">{{ $emptyCount }} soal</strong> yang belum dijawab.
                        @elseif ($doubtCount > 0)
                            Semua soal terjawab. Cek lagi <strong class="text-gray-950 dark:text-white">{{ $doubtCount }} soal</strong> yang kamu ragukan.
                        @else
                            Semua soal sudah terjawab. Siap dikumpulkan.
                        @endif
                    </p>
                    <x-filament::button color="success" icon="heroicon-o-paper-airplane" wire:click="submitExam" wire:confirm="Yakin ingin mengumpulkan ujian? Jawaban tidak bisa diubah lagi." :disabled="$emptyCount > 0">
                        Kumpulkan ujian
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
