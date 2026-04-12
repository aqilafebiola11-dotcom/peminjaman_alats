@vite(['resources/css/login.css', 'resources/js/app.js'])

<div class="login-shell">
    <div class="login-orb login-orb-a"></div>
    <div class="login-orb login-orb-b"></div>

    <div class="login-card">
        <aside class="login-brand">
            <p class="login-kicker">Sistem Operasional</p>
            <h1 class="login-brand-title">Peminjaman Alat</h1>
            <p class="login-brand-copy">
                Kelola peminjaman, pengembalian, dan ketersediaan alat dari satu tempat dengan antarmuka yang lebih bersih.
            </p>

            <ul class="login-points">
                <li>Validasi data peminjaman lebih cepat</li>
                <li>Monitoring stok dan status alat real-time</li>
                <li>Riwayat aktivitas tercatat rapi</li>
            </ul>

            <p class="login-year">&copy; {{ date('Y') }} Peminjaman Alat</p>
        </aside>

        <section class="login-form-panel">
            <div class="login-form-head">
                <h2>Masuk ke Dashboard</h2>
                <p>Gunakan akun kamu untuk melanjutkan.</p>
            </div>

            @if ($errors->has('data.email') || $errors->has('data.password'))
                <div class="login-alert">Email atau password salah.</div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" class="login-form">
                @csrf
                <div class="login-field">
                    <label for="email">Email</label>
                    <div class="login-input-wrap">
                        <span class="login-input-icon">@</span>
                        <input
                            name="email"
                            id="email"
                            type="email"
                            class="login-input"
                            placeholder="Masukkan email"
                            autocomplete="email"
                            required
                            autofocus
                        />
                    </div>
                </div>

                <div class="login-field">
                    <label for="password">Password</label>
                    <div class="login-input-wrap" x-data="{ showPassword: false }">
                        <span class="login-input-icon">#</span>
                        <input
                            name="password"
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="login-input"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="login-password-toggle"
                            :title="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                        >
                            <svg x-show="!showPassword" class="login-eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg x-show="showPassword" class="login-eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="login-check-row">
                    <input name="remember" id="remember" type="checkbox" />
                    <label for="remember">Ingat saya</label>
                </div>

                <button type="submit" class="login-submit">Masuk Sekarang</button>
            </form>
        </section>
    </div>
</div>
