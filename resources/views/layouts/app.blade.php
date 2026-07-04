<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>NEXSTOCK - Inventory & Logistics Management</title>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Dark theme overrides for Select2 to match NexStock */
        .select2-container--default .select2-selection--single {
            background-color: #1a2332;
            border: 1px solid #2d3748;
            border-radius: 8px;
            height: 42px;
            color: #e2e8f0;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #e2e8f0;
            line-height: 40px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
        .select2-dropdown {
            background-color: #1a2332;
            border: 1px solid #2d3748;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #0f172a;
            border: 1px solid #2d3748;
            color: #e2e8f0;
        }
        .select2-container--default .select2-results__option--selected {
            background-color: #2d3748;
        }
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: #38bdf8;
            color: white;
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('dashboard') }}" class="logo-brand">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="url(#sidebarLogoGrad)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                        <defs>
                            <linearGradient id="sidebarLogoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#38bdf8" />
                                <stop offset="100%" stop-color="#10b981" />
                            </linearGradient>
                        </defs>
                        <!-- Box Outer Outline -->
                        <path d="M12 2L21 7L12 12L3 7Z" />
                        <path d="M3 7v10l9 5V12" />
                        <path d="M21 7v10l-9 5" />
                        
                        <!-- Packing Tape -->
                        <path d="M7.5 4.5L16.5 9.5" />
                        <path d="M16.5 9.5v3" />

                        <!-- Dotted patterns -->
                        <circle cx="7" cy="15.5" r="0.5" fill="url(#sidebarLogoGrad)" stroke="none" />
                        <circle cx="7" cy="17" r="0.5" fill="url(#sidebarLogoGrad)" stroke="none" />
                        <circle cx="7" cy="18.5" r="0.5" fill="url(#sidebarLogoGrad)" stroke="none" />
                        <circle cx="8.5" cy="16.25" r="0.5" fill="url(#sidebarLogoGrad)" stroke="none" />
                        <circle cx="8.5" cy="17.75" r="0.5" fill="url(#sidebarLogoGrad)" stroke="none" />
                        <circle cx="10" cy="17" r="0.5" fill="url(#sidebarLogoGrad)" stroke="none" />

                        <circle cx="17" cy="15.5" r="0.5" fill="url(#sidebarLogoGrad)" stroke="none" />
                        <circle cx="17" cy="17" r="0.5" fill="url(#sidebarLogoGrad)" stroke="none" />
                        <circle cx="17" cy="18.5" r="0.5" fill="url(#sidebarLogoGrad)" stroke="none" />
                        <circle cx="15.5" cy="16.25" r="0.5" fill="url(#sidebarLogoGrad)" stroke="none" />
                        <circle cx="15.5" cy="17.75" r="0.5" fill="url(#sidebarLogoGrad)" stroke="none" />
                        <circle cx="14" cy="17" r="0.5" fill="url(#sidebarLogoGrad)" stroke="none" />
                    </svg>
                    NEXSTOCK
                </a>
                <button class="sidebar-close-btn" id="sidebarClose" aria-label="Close sidebar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="sidebar-user-panel">
                <div class="user-avatar-wrapper">
                    <div class="user-avatar-initial">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                </div>
                <div class="user-profile-details">
                    <div class="profile-name">{{ auth()->user()->name }}</div>
                    <span class="profile-role-badge">{{ strtoupper(str_replace('_', ' ', auth()->user()->role)) }}</span>
                </div>
            </div>

            <nav class="sidebar-nav-menu">
                <div class="nav-section-title">Menu Utama</div>
                
                <a href="{{ route('dashboard') }}" class="sidebar-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="9"></rect>
                        <rect x="14" y="3" width="7" height="5"></rect>
                        <rect x="14" y="12" width="7" height="9"></rect>
                        <rect x="3" y="16" width="7" height="5"></rect>
                    </svg>
                    <span>Dashboard</span>
                </a>

                @if(auth()->user()->role !== 'owner')
                <div class="nav-section-title" style="margin-top: 1.25rem;">Transaksi</div>

                <a href="{{ route('inbound.index') }}" class="sidebar-nav-link {{ request()->routeIs('inbound.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                    <span>Barang Masuk</span>
                </a>

                <a href="{{ route('outbound.index') }}" class="sidebar-nav-link {{ request()->routeIs('outbound.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 22H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5"></path>
                        <polyline points="17 16 21 12 17 8"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Barang Keluar</span>
                </a>

                <a href="{{ route('damaged.index') }}" class="sidebar-nav-link {{ request()->routeIs('damaged.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <span>Karantina (Rusak)</span>
                </a>

                <a href="{{ route('opname.index') }}" class="sidebar-nav-link {{ request()->routeIs('opname.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span>Stock Opname</span>
                </a>
                @endif

                <!-- Admin & Owner Master Data / Procurement -->
                @if(auth()->user()->role === 'admin_gudang' || auth()->user()->role === 'owner')
                    <div class="nav-section-title" style="margin-top: 1.25rem;">{{ auth()->user()->role === 'owner' ? 'Monitoring' : 'Administrasi & Data' }}</div>

                    @if(auth()->user()->role === 'admin_gudang')
                    <a href="{{ route('po.index') }}" class="sidebar-nav-link {{ request()->routeIs('po.*') ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                        <span>Daftar PO</span>
                    </a>
                    @endif

                    <a href="{{ route('product.index') }}" class="sidebar-nav-link {{ request()->routeIs('product.*') ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                        <span>Master Produk</span>
                    </a>

                    @if(auth()->user()->role === 'admin_gudang')
                    <a href="{{ route('supplier.index') }}" class="sidebar-nav-link {{ request()->routeIs('supplier.*') ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Master Supplier</span>
                    </a>

                    <a href="{{ route('rack.index') }}" class="sidebar-nav-link {{ request()->routeIs('rack.*') ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="3" y1="9" x2="21" y2="9"></line>
                            <line x1="3" y1="15" x2="21" y2="15"></line>
                            <line x1="12" y1="9" x2="12" y2="21"></line>
                        </svg>
                        <span>Master Rak</span>
                    </a>
                    @endif
                @endif

                <!-- Admin Only User Management -->
                @if(auth()->user()->role === 'admin_gudang')
                    <div class="nav-section-title" style="margin-top: 1.25rem;">Sistem</div>

                    <a href="{{ route('user.index') }}" class="sidebar-nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        <span>Kelola Operator</span>
                    </a>
                @endif
            </nav>

            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}" style="width: 100%;">
                    @csrf
                    <button type="submit" class="btn btn-secondary sidebar-logout-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Workspace Area -->
        <div class="main-workspace">
            <header class="top-bar">
                <button class="mobile-nav-toggle" id="navToggle" aria-label="Toggle menu">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <div class="top-bar-title">NEXSTOCK LOGISTICS</div>
                <div class="top-bar-user-badge">
                    <span class="user-role-dot"></span>
                    <span>{{ auth()->user()->name }}</span>
                </div>
            </header>

            <main class="main-content">
                <!-- Alert Notifications -->
                @if(session('success'))
                    <div class="alert alert-success">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <span>{{ session('warning') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <div style="display: flex; flex-direction: column;">
                            @foreach ($errors->all() as $error)
                                <span>{{ $error }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Script for mobile menu toggle -->
    <script>
        const navToggle = document.getElementById('navToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarClose = document.getElementById('sidebarClose');

        if (navToggle && sidebar) {
            navToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                sidebar.classList.toggle('show');
            });
        }

        if (sidebarClose && sidebar) {
            sidebarClose.addEventListener('click', function() {
                sidebar.classList.remove('show');
            });
        }

        // Close sidebar if clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth < 1024 && sidebar) {
                const isClickInside = sidebar.contains(event.target) || (navToggle && navToggle.contains(event.target));
                if (!isClickInside) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>
    
    <!-- jQuery and Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%',
            });
        });
    </script>
</body>
</html>
