<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Wivor')</title>
    <meta name="description" content="@yield('meta-description', 'Default description')">
    <meta name="keywords" content="@yield('meta-keywords', 'default, keywords')">

    @include('partials.admin-header')
    @yield('scripts')
    <style>
        .sidebar {
            min-height: 100vh;
            padding: 1rem;
            border-right: 1px solid #dee2e6;
        }

        .sidebar a {
            display: block;
            padding: 0.5rem 1rem;
            margin-bottom: 0.5rem;
            color: #ff6700;
            text-decoration: none;
            border-radius: 0.375rem;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #ff6700;
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 bg-light vh-100 sidebar p-3">
                <a href="{{ route('photographer.dashboard') }}" class="d-block mb-2 {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="bi bi-house-door-fill me-1"></i> @lang('messages.home')
                </a>

                <div class="dropdown mb-2">
                    <a class="dropdown-toggle d-block {{ request()->routeIs('events.*') ? 'active' : '' }}" href="#"
                        id="eventsMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-calendar-event me-1"></i> @lang('messages.events')
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="eventsMenu">
                        <li>
                            <a class="dropdown-item" href="{{route('photographer.allEvents')}}">
                                @lang('messages.all_events')
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="">
                                @lang('messages.my_events')
                            </a>
                        </li>
                        {{-- ✅ New Submenu Items --}}
                        <li>
                            <a class="dropdown-item ps-4" href="{{route('photographer.newEvent')}}">
                                <i class="bi bi-plus-circle me-1"></i> Create Event 
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item ps-4" href="{{route('photographer.myEvents')}}">
                                <i class="bi bi-card-list me-1"></i> List My Events
                            </a>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 py-4">
                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.footer')
</body>

</html>