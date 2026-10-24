<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Manajemen Bot'])
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    <main class="pl-main">
        <header class="pl-page-head">
            <div>
                <h1 class="pl-page-title">Manajemen Bot</h1>
                <p class="pl-page-sub">Kelola pesan Telegram, template, dan jadwal pengiriman.</p>
            </div>
            <span class="pl-badge pl-badge-primary">{{ $jumlahGrupAktif }} grup aktif</span>
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="pl-alert pl-alert-accent" role="alert">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
        @endif

        <section class="pl-section" aria-labelledby="judul-kirim">
            <h2 id="judul-kirim" class="pl-section__title">Kirim atau Jadwalkan Pesan</h2>
            <form method="POST" action="{{ route('bot.messages.store') }}" class="pl-panel max-w-[60rem]">
                @csrf
                <div class="pl-fields">
                    <div class="pl-field pl-field--wide">
                        <label class="pl-field-label" for="template_id">Gunakan Template</label>
                        <select class="pl-input" id="template_id" name="template_id">
                            <option value="">Tulis pesan baru</option>
                            @foreach ($templates as $item)
                                <option value="{{ $item->id }}" data-isi="{{ $item->isi }}" @selected((string) old('template_id') === (string) $item->id)>
                                    {{ $item->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pl-field pl-field--wide">
                        <label class="pl-field-label" for="message">Isi Pesan</label>
                        <textarea class="pl-input" id="message" name="message" maxlength="4096" rows="5" required>{{ old('message', $template?->isi) }}</textarea>
                        <div class="pl-field-hint">Maksimal 4.096 karakter. Pesan dikirim ke semua grup Telegram aktif.</div>
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="send_at">Waktu Pengiriman</label>
                        <input class="pl-input" id="send_at" name="send_at" type="datetime-local" value="{{ old('send_at') }}" min="{{ now()->format('Y-m-d\TH:i') }}">
                        <div class="pl-field-hint">Kosongkan untuk mengirim sekarang.</div>
                    </div>
                </div>
                <div class="pl-form-actions">
                    <button class="pl-btn pl-btn-primary" type="submit" name="aksi" value="sekarang">Kirim Sekarang</button>
                    <button class="pl-btn pl-btn-ghost" type="submit" name="aksi" value="jadwalkan">Jadwalkan</button>
                </div>
            </form>
        </section>

        <section class="pl-section" aria-labelledby="judul-jadwal">
            <h2 id="judul-jadwal" class="pl-section__title">Daftar Pesan dan Status</h2>
            <div class="pl-table-wrap">
                <table class="pl-table">
                    <thead>
                        <tr>
                            <th scope="col">Pesan</th>
                            <th scope="col">Template</th>
                            <th scope="col">Waktu</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($messages as $message)
                            <tr>
                                <td class="whitespace-normal min-w-[16rem]">
                                    <span class="line-clamp-3">{{ $message->isi }}</span>
                                    @if ($message->failure_reason)
                                        <span class="block text-sm text-muted">{{ $message->failure_reason }}</span>
                                    @endif
                                </td>
                                <td>{{ $message->template?->nama ?? '—' }}</td>
                                <td class="tabular-nums whitespace-nowrap">
                                    {{ $message->send_at->locale('id')->translatedFormat('j M Y, H:i') }}
                                    @if ($message->sent_at)
                                        <span class="block text-sm text-muted">Terkirim {{ $message->sent_at->locale('id')->translatedFormat('j M Y, H:i') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span @class([
                                        'pl-badge',
                                        'pl-badge-primary' => in_array($message->status, ['sent', 'cancelled'], true),
                                        'pl-badge-accent' => in_array($message->status, ['failed', 'sending', 'pending'], true),
                                    ])>
                                        {{ ['pending' => 'Terjadwal', 'sending' => 'Mengirim', 'sent' => 'Terkirim', 'failed' => 'Gagal', 'cancelled' => 'Dibatalkan'][$message->status] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-2 min-w-[14rem]">
                                        @if ($message->status === 'pending')
                                            <details>
                                                <summary class="pl-btn pl-btn-ghost pl-btn-sm cursor-pointer">Edit</summary>
                                                <form method="POST" action="{{ route('bot.messages.update', $message) }}" class="grid gap-2 mt-2 min-w-[18rem]">
                                                    @csrf
                                                    @method('PATCH')
                                                    <textarea class="pl-input" name="isi" maxlength="4096" required>{{ $message->isi }}</textarea>
                                                    <input class="pl-input" type="datetime-local" name="send_at" value="{{ $message->send_at->format('Y-m-d\TH:i') }}" min="{{ now()->format('Y-m-d\TH:i') }}" required>
                                                    <button type="submit" class="pl-btn pl-btn-primary pl-btn-sm">Simpan Perubahan</button>
                                                </form>
                                            </details>
                                            <form method="POST" action="{{ route('bot.messages.send-now', $message) }}">
                                                @csrf
                                                <button type="submit" class="pl-btn pl-btn-ghost pl-btn-sm">Kirim Sekarang</button>
                                            </form>
                                            <form method="POST" action="{{ route('bot.messages.cancel', $message) }}">
                                                @csrf
                                                <button type="submit" class="pl-btn pl-btn-ghost pl-btn-sm">Batalkan</button>
                                            </form>
                                        @endif
                                        @if ($message->status !== 'sending')
                                            <form method="POST" action="{{ route('bot.messages.destroy', $message) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="pl-btn pl-btn-ghost pl-btn-sm">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="c !py-8 text-muted">Belum ada pesan bot.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pl-table-foot">
                @include('partials.halaman', ['data' => $messages])
            </div>
        </section>

        <section class="pl-section" aria-labelledby="judul-template">
            <h2 id="judul-template" class="pl-section__title">Template Pesan</h2>
            <div class="pl-row">
                <form method="POST"
                    action="{{ $template ? route('bot.templates.update', $template) : route('bot.templates.store') }}"
                    class="pl-panel" style="flex: 1 1 24rem">
                    @csrf
                    @if ($template)
                        @method('PUT')
                    @endif
                    <h3 class="pl-panel__title">{{ $template ? 'Edit Template' : 'Buat Template' }}</h3>
                    <div class="pl-fields">
                        <div class="pl-field pl-field--wide">
                            <label class="pl-field-label" for="nama-template">Nama Template</label>
                            <input class="pl-input" id="nama-template" name="nama" value="{{ old('nama', $template?->nama) }}" maxlength="100" required>
                        </div>
                        <div class="pl-field pl-field--wide">
                            <label class="pl-field-label" for="isi-template">Isi Pesan</label>
                            <textarea class="pl-input" id="isi-template" name="isi" maxlength="4096" rows="5" required>{{ old('isi', $template?->isi) }}</textarea>
                        </div>
                    </div>
                    <div class="pl-form-actions">
                        @if ($template)
                            <a href="{{ route('bot.index') }}" class="pl-btn pl-btn-ghost">Batal</a>
                        @endif
                        <button type="submit" class="pl-btn pl-btn-primary">{{ $template ? 'Simpan Perubahan' : 'Simpan Template' }}</button>
                    </div>
                </form>

                <div class="pl-panel" style="flex: 2 1 30rem">
                    <h3 class="pl-panel__title">Template Tersimpan</h3>
                    <div class="grid gap-3">
                        @forelse ($templates as $item)
                            <article class="pl-panel !gap-2">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <h4 class="font-semibold">{{ $item->nama }}</h4>
                                    <div class="flex gap-2">
                                        <a class="pl-btn pl-btn-ghost pl-btn-sm" href="{{ route('bot.index', ['template' => $item->id]) }}">Edit</a>
                                        <form method="POST" action="{{ route('bot.templates.destroy', $item) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="pl-btn pl-btn-ghost pl-btn-sm" type="submit">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                                <p class="pl-panel__text whitespace-pre-line">{{ $item->isi }}</p>
                            </article>
                        @empty
                            <p class="m-0 text-muted">Belum ada template pesan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        (() => {
            const pilihTemplate = document.getElementById('template_id');
            const isiPesan = document.getElementById('message');
            const waktu = document.getElementById('send_at');

            pilihTemplate.addEventListener('change', () => {
                const isiTemplate = pilihTemplate.selectedOptions[0]?.dataset.isi;
                if (isiTemplate !== undefined) isiPesan.value = isiTemplate;
            });
            if (pilihTemplate.value && !isiPesan.value) {
                isiPesan.value = pilihTemplate.selectedOptions[0]?.dataset.isi ?? '';
            }

            document.querySelectorAll('button[name="aksi"]').forEach((button) => {
                button.addEventListener('click', () => {
                    waktu.required = button.value === 'jadwalkan';
                });
            });
        })();
    </script>
</body>
</html>
