<x-guest-layout>
    <style>
        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-left {
            position: absolute;
            left: 14px;
            color: #64748b;
            pointer-events: none;
            display: flex;
            align-items: center;
            z-index: 10;
        }

        .input-icon-right {
            position: absolute;
            right: 14px;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            border: none;
            background: transparent;
            padding: 4px;
            z-index: 10;
            transition: color 0.2s ease;
        }

        .input-icon-right:hover {
            color: #38bdf8;
        }

        /* Demo Cards style */
        .demo-login-section {
            margin-top: 2.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 1.75rem;
        }

        .demo-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            margin-bottom: 1rem;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .demo-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
        }

        .demo-card {
            background: rgba(30, 41, 59, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 0.75rem;
            padding: 0.85rem 0.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.6rem;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            color: #94a3b8;
            width: 100%;
        }

        .demo-card:hover {
            background: rgba(30, 41, 59, 0.85);
            border-color: rgba(56, 189, 248, 0.3);
            color: #f8fafc;
            transform: translateY(-2px);
        }

        .demo-card.active {
            background: rgba(14, 165, 233, 0.12);
            border-color: #0ea5e9;
            color: #38bdf8;
            box-shadow: 
                0 0 15px rgba(14, 165, 233, 0.15),
                inset 0 0 0 1px rgba(14, 165, 233, 0.2);
        }

        .demo-card-icon-wrapper {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 50%;
            padding: 0.4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.03);
            transition: all 0.2s ease;
        }

        .demo-card:hover .demo-card-icon-wrapper,
        .demo-card.active .demo-card-icon-wrapper {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .demo-role-name {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(14, 165, 233, 0.6);
            }
            70% {
                box-shadow: 0 0 0 8px rgba(14, 165, 233, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(14, 165, 233, 0);
            }
        }

        .pulse-animation {
            animation: pulse 0.8s ease-in-out;
        }

        /* Custom error styling for modern design */
        .alert-error-container {
            background: rgba(244, 63, 94, 0.1);
            border: 1px solid rgba(244, 63, 94, 0.2);
            border-radius: 0.75rem;
            padding: 0.85rem 1rem;
            margin-bottom: 1.5rem;
            color: #fda4af;
            font-size: 0.875rem;
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
        }
    </style>

    {{-- Session Status --}}
    @if (session('status'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem; border-radius: 0.75rem;">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert-error-container">
            <svg style="flex-shrink: 0; margin-top: 2px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" id="loginForm">
        @csrf

        <!-- Email Address -->
        <div class="input-group">
            <label class="form-label" for="email">Alamat Email</label>
            <div class="input-wrapper">
                <span class="input-icon-left">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </span>
                <input type="email" name="email" id="email" class="form-control" style="padding-left: 2.75rem !important;" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="email@nexstock.com">
            </div>
        </div>

        <!-- Password -->
        <div class="input-group" style="margin-bottom: 1.25rem;">
            <label class="form-label" for="password">Kata Sandi</label>
            <div class="input-wrapper">
                <span class="input-icon-left">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </span>
                <input type="password" name="password" id="password" class="form-control" style="padding-left: 2.75rem !important; padding-right: 2.75rem !important;" required autocomplete="current-password" placeholder="Masukkan kata sandi...">
                <button type="button" class="input-icon-right" id="passwordToggle" onclick="togglePassword()" aria-label="Toggle password visibility">
                    <!-- Eye Icon (Visible by default: means password hidden) -->
                    <svg id="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <!-- Eye Off Icon (Hidden by default) -->
                    <svg id="eye-off-icon" style="display: none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                </button>
            </div>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem;">
            <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; font-size: 0.85rem; color: #94a3b8; user-select: none;">
                <input type="checkbox" name="remember" id="remember_me" style="width: 16px; height: 16px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); accent-color: #0ea5e9;">
                Ingat saya
            </label>
        </div>

        <button type="submit" class="btn btn-primary btn-submit" style="width: 100%;">
            Masuk ke Sistem
        </button>

        <!-- Quick Login Badges -->
        <div class="demo-login-section">
            <h3 class="demo-title">Pilih Akun Demo (Quick Login)</h3>
            <div class="demo-grid">
                <!-- Owner -->
                <button type="button" onclick="selectRole('owner@nexstock.com', 'password', this)" class="demo-card">
                    <div class="demo-card-icon-wrapper">
                        <svg class="demo-card-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="7"></circle>
                            <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                        </svg>
                    </div>
                    <span class="demo-role-name">Owner</span>
                </button>

                <!-- Admin -->
                <button type="button" onclick="selectRole('admin@nexstock.com', 'password', this)" class="demo-card">
                    <div class="demo-card-icon-wrapper">
                        <svg class="demo-card-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </div>
                    <span class="demo-role-name">Admin</span>
                </button>

                <!-- Staff -->
                <button type="button" onclick="selectRole('staff@nexstock.com', 'password', this)" class="demo-card">
                    <div class="demo-card-icon-wrapper">
                        <svg class="demo-card-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                    </div>
                    <span class="demo-role-name">Staff</span>
                </button>
            </div>
        </div>
    </form>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeOffIcon = document.getElementById('eye-off-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                passwordInput.type = 'password';
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        }

        function selectRole(email, password, element) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            
            // Remove active class from all demo cards
            document.querySelectorAll('.demo-card').forEach(card => {
                card.classList.remove('active');
            });
            
            // Add active class to the clicked card
            element.classList.add('active');
            
            // Trigger visual feedback to submit button
            const btn = document.querySelector('.btn-submit');
            btn.classList.add('pulse-animation');
            setTimeout(() => {
                btn.classList.remove('pulse-animation');
            }, 800);
            
            // Focus on the password input just in case
            document.getElementById('password').focus();
        }
    </script>
</x-guest-layout>
