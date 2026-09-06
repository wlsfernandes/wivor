<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Photographer workspace | WivorPhotos')</title>
    <meta name="robots" content="noindex,nofollow">
    @hasSection('meta-description')
        <meta name="description" content="@yield('meta-description')">
    @endif
    @hasSection('meta-keywords')
        <meta name="keywords" content="@yield('meta-keywords')">
    @endif

    @include('partials.head-assets')
    <style>
        :root {
            --photographer-accent: #ff6700;
            --photographer-sidebar: #172033;
            --photographer-sidebar-muted: #aeb8ca;
            --photographer-page: #f5f7fb;
            --photographer-border: #e4e8ef;
        }

        * {
            box-sizing: border-box;
        }

        body.photographer-app {
            min-width: 0;
            margin: 0;
            overflow-x: hidden;
            background: var(--photographer-page);
            color: #263043;
        }

        .photographer-shell {
            display: grid;
            grid-template-columns: 264px minmax(0, 1fr);
            min-height: 100vh;
        }

        .photographer-sidebar {
            position: sticky;
            top: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            width: 264px;
            height: 100vh;
            overflow-y: auto;
            background: var(--photographer-sidebar);
            color: #fff;
            box-shadow: 8px 0 24px rgba(20, 29, 47, 0.08);
        }

        .photographer-brand {
            display: flex;
            align-items: center;
            min-height: 76px;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            text-decoration: none;
        }

        .photographer-brand img {
            display: block;
            width: auto;
            max-width: 132px;
            max-height: 42px;
        }

        .photographer-sidebar-close {
            position: absolute;
            top: 17px;
            right: 14px;
            display: none;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 0.65rem;
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            font-size: 1.15rem;
        }

        .photographer-nav {
            flex: 1;
            padding: 1.25rem 0.875rem;
        }

        .photographer-nav-label {
            margin: 0 0.75rem 0.5rem;
            color: var(--photographer-sidebar-muted);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .photographer-nav a,
        .photographer-sidebar-footer a,
        .photographer-logout {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            margin-bottom: 0.25rem;
            padding: 0.75rem 0.875rem;
            border: 0;
            border-radius: 0.65rem;
            background: transparent;
            color: #dce2ed;
            font: inherit;
            line-height: 1.25;
            text-align: left;
            text-decoration: none;
            transition: background-color 0.18s ease, color 0.18s ease;
        }

        .photographer-nav a:hover,
        .photographer-nav a:focus-visible,
        .photographer-sidebar-footer a:hover,
        .photographer-sidebar-footer a:focus-visible,
        .photographer-logout:hover,
        .photographer-logout:focus-visible,
        .photographer-nav a.active {
            background: rgba(255, 103, 0, 0.16);
            color: #fff;
        }

        .photographer-nav a.active {
            box-shadow: inset 3px 0 0 var(--photographer-accent);
        }

        .photographer-nav i,
        .photographer-sidebar-footer i {
            width: 1.25rem;
            color: var(--photographer-sidebar-muted);
            font-size: 1.05rem;
            text-align: center;
        }

        .photographer-nav a.active i,
        .photographer-nav a:hover i,
        .photographer-sidebar-footer a:hover i,
        .photographer-logout:hover i {
            color: var(--photographer-accent);
        }

        .photographer-sidebar-footer {
            padding: 1rem 0.875rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .photographer-user {
            min-width: 0;
            margin-bottom: 0.75rem;
            padding: 0 0.875rem;
        }

        .photographer-user strong,
        .photographer-user span {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .photographer-user span {
            color: var(--photographer-sidebar-muted);
            font-size: 0.78rem;
        }

        .photographer-main {
            display: flex;
            min-width: 0;
            min-height: 100vh;
            flex-direction: column;
        }

        .photographer-topbar {
            position: sticky;
            top: 0;
            z-index: 1020;
            display: flex;
            min-height: 76px;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.875rem 2rem;
            border-bottom: 1px solid var(--photographer-border);
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
        }

        .photographer-topbar-title {
            min-width: 0;
        }

        .photographer-topbar-title strong,
        .photographer-topbar-title span {
            display: block;
        }

        .photographer-topbar-title strong {
            color: #1e293b;
            font-size: 1rem;
        }

        .photographer-topbar-title span {
            color: #6b7280;
            font-size: 0.8rem;
        }

        .photographer-menu-button {
            display: none;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--photographer-border);
            border-radius: 0.65rem;
            background: #fff;
            color: #1e293b;
            font-size: 1.25rem;
        }

        .photographer-content {
            width: 100%;
            max-width: 1600px;
            flex: 1;
            margin: 0 auto;
            padding: 2rem;
        }

        .photographer-content > .container,
        .photographer-content > .container-fluid,
        .photographer-content > section > .container,
        .photographer-content > section > .container-fluid,
        .photographer-content > section > .auto-container {
            max-width: none;
            padding-right: 0;
            padding-left: 0;
        }

        .photographer-content .table-responsive {
            max-width: 100%;
        }

        .photographer-page-footer {
            padding: 1rem 2rem 1.5rem;
            color: #7a8494;
            font-size: 0.78rem;
            text-align: center;
        }

        .photographer-sidebar-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1030;
            display: none;
            background: rgba(15, 23, 42, 0.58);
        }

        @media (max-width: 991.98px) {
            .photographer-shell {
                display: block;
            }

            .photographer-sidebar {
                position: fixed;
                left: 0;
                transform: translateX(-100%);
                transition: transform 0.22s ease;
            }

            .photographer-sidebar-open {
                overflow: hidden;
            }

            .photographer-sidebar-open .photographer-sidebar {
                transform: translateX(0);
            }

            .photographer-sidebar-open .photographer-sidebar-backdrop {
                display: block;
            }

            .photographer-menu-button {
                display: inline-flex;
            }

            .photographer-sidebar-close {
                display: inline-flex;
            }

            .photographer-brand {
                padding-right: 4.25rem;
            }

            .photographer-topbar {
                justify-content: flex-start;
                padding: 0.75rem 1.25rem;
            }

            .photographer-topbar-action {
                margin-left: auto;
            }

            .photographer-content {
                padding: 1.5rem 1.25rem;
            }
        }

        @media (max-width: 575.98px) {
            .photographer-sidebar {
                width: min(86vw, 300px);
            }

            .photographer-topbar {
                min-height: 66px;
                padding: 0.65rem 1rem;
            }

            .photographer-topbar-title span,
            .photographer-topbar-action span {
                display: none;
            }

            .photographer-content {
                padding: 1rem;
            }

            .photographer-content .card-body {
                padding: 1rem;
            }

            .photographer-page-footer {
                padding: 1rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .photographer-sidebar,
            .photographer-nav a,
            .photographer-sidebar-footer a,
            .photographer-logout {
                transition: none;
            }
        }
    </style>
    @yield('styles')
</head>

@php
    $photographerUser = auth()->user();
    $canAccessPhotographerArea = $photographerUser?->canAccessPhotographerArea() ?? false;
    $photographerDisplayName = trim(($photographerUser?->photographer?->first_name ?? '') . ' ' . ($photographerUser?->photographer?->last_name ?? ''));
@endphp

<body class="photographer-app">
    <div class="photographer-shell">
        <aside class="photographer-sidebar" id="photographer-sidebar" aria-label="Photographer navigation">
            <a class="photographer-brand" href="{{ $canAccessPhotographerArea ? route('photographer.dashboard') : url('/') }}">
                <img src="{{ asset('assets/images/logo/wivor.png') }}" alt="WivorPhotos">
            </a>
            <button class="photographer-sidebar-close" type="button" data-photographer-sidebar-close
                aria-label="Close navigation">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>

            <nav class="photographer-nav">
                <p class="photographer-nav-label">Workspace</p>

                @if ($canAccessPhotographerArea)
                    <a href="{{ route('photographer.dashboard') }}"
                        class="{{ request()->routeIs('photographer.dashboard', 'photographer.payouts.*') ? 'active' : '' }}">
                        <i class="bi bi-grid" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('events.index') }}"
                        class="{{ request()->routeIs('events.index', 'events.edit', 'photographer.myEvents', 'photographer.uploads.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                        <span>My events</span>
                    </a>
                    <a href="{{ route('events.create') }}"
                        class="{{ request()->routeIs('events.create', 'photographer.newEvent') ? 'active' : '' }}">
                        <i class="bi bi-calendar-plus" aria-hidden="true"></i>
                        <span>Create event</span>
                    </a>
                    <a href="{{ route('photographer.dashboard') }}#payout-setup">
                        <i class="bi bi-bank" aria-hidden="true"></i>
                        <span>Payout setup</span>
                    </a>

                    <p class="photographer-nav-label mt-4">WivorPhotos</p>
                    <a href="{{ route('events.listEvents') }}">
                        <i class="bi bi-images" aria-hidden="true"></i>
                        <span>Browse galleries</span>
                    </a>
                @else
                    <a href="{{ route('photographer.application-status') }}"
                        class="{{ request()->routeIs('photographer.application-status') ? 'active' : '' }}">
                        <i class="bi bi-person-check" aria-hidden="true"></i>
                        <span>Application status</span>
                    </a>
                    <a href="{{ url('/') }}">
                        <i class="bi bi-house" aria-hidden="true"></i>
                        <span>Return to website</span>
                    </a>
                @endif
            </nav>

            <div class="photographer-sidebar-footer">
                <div class="photographer-user">
                    <strong>{{ $photographerDisplayName ?: 'Photographer' }}</strong>
                    <span>{{ $photographerUser?->email }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="photographer-logout" type="submit">
                        <i class="bi bi-box-arrow-left" aria-hidden="true"></i>
                        <span>Log out</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="photographer-sidebar-backdrop" data-photographer-sidebar-close aria-hidden="true"></div>

        <div class="photographer-main">
            <header class="photographer-topbar">
                <button class="photographer-menu-button" type="button" data-photographer-sidebar-toggle
                    aria-controls="photographer-sidebar" aria-expanded="false" aria-label="Open navigation">
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>
                <div class="photographer-topbar-title">
                    <strong>@yield('workspace-title', 'Photographer workspace')</strong>
                    <span>Manage events, uploads, sales, and payouts</span>
                </div>
                <a class="btn btn-sm btn-outline-secondary photographer-topbar-action" href="{{ url('/') }}">
                    <i class="bi bi-box-arrow-up-right me-sm-1" aria-hidden="true"></i>
                    <span>View website</span>
                </a>
            </header>

            <main class="photographer-content" id="main-content">
                @yield('content')
            </main>

            <footer class="photographer-page-footer">
                &copy; {{ now()->year }} WivorPhotos
            </footer>
        </div>
    </div>

    <script>
        (() => {
            const body = document.body;
            const toggle = document.querySelector('[data-photographer-sidebar-toggle]');
            const closeTargets = document.querySelectorAll('[data-photographer-sidebar-close]');
            const mobileViewport = window.matchMedia('(max-width: 991.98px)');

            const setSidebarOpen = open => {
                body.classList.toggle('photographer-sidebar-open', open);
                toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
                toggle?.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
            };

            toggle?.addEventListener('click', () => {
                setSidebarOpen(!body.classList.contains('photographer-sidebar-open'));
            });

            closeTargets.forEach(target => target.addEventListener('click', () => setSidebarOpen(false)));

            document.querySelectorAll('.photographer-sidebar a').forEach(link => {
                link.addEventListener('click', () => {
                    if (mobileViewport.matches) setSidebarOpen(false);
                });
            });

            document.addEventListener('keydown', event => {
                if (event.key === 'Escape') setSidebarOpen(false);
            });

            mobileViewport.addEventListener?.('change', event => {
                if (!event.matches) setSidebarOpen(false);
            });
        })();
    </script>
    @yield('scripts')
</body>

</html>
