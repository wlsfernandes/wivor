<div class="boxed_wrapper">


    <div id="search-popup" class="search-popup">
        <div class="popup-inner">
            <div class="upper-box clearfix">
                <figure class="logo-box pull-left"><a href="{{ url('/') }}"><img
                            src="{{ asset('assets/images/logo/wivor.png') }}" alt="WivorPhotos"></a>
                </figure>
                <div class="close-search pull-right"><span class="far fa-times"></span></div>
            </div>
            <div class="overlay-layer"></div>
            <div class="auto-container">
                <div class="search-form">
                    <form method="post" action="index.php">
                        <div class="form-group">
                            <fieldset>
                                <input type="search" class="form-control" name="search-input" value=""
                                    placeholder="Type your keyword and hit" required>
                                <button type="submit"><i class="far fa-search"></i></button>
                            </fieldset>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <header class="main-header header-style-two">
        <!-- header-top -->
        <div class="header-top">
            <div class="top-inner">
                <div class="top-left">
                    <div class="social-links">
                        <ul class="clearfix">
                            <li><a href="https://www.facebook.com/p/WiVor-Photos-61573081696201/" target="blank"><span
                                        class="fab fa-facebook-square"></span></a></li>
                            <li><a href="https://www.instagram.com/wivor.photos/" target="blank"><span
                                        class="fab fa-instagram"></span></a></li>


                        </ul>
                    </div>
                    <ul class="info">

                        <li>
                            <a href="{{ route('lang.switch', ['lang' => 'en']) }}" title="English">
                                <img src="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.4.6/flags/4x3/us.svg"
                                    alt="English" style="width: 24px;">
                            </a>
                            <a href="{{ route('lang.switch', ['lang' => 'es']) }}" title="Español"
                                style="margin-left: 5px;">
                                <img src="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.4.6/flags/4x3/es.svg"
                                    alt="Español" style="width: 24px;">
                            </a>
                            <a href="{{ route('lang.switch', ['lang' => 'pt-BR']) }}" title="Português">
                                <img src="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.4.6/flags/4x3/br.svg"
                                    alt="Português" style="width: 24px;">
                            </a>
                        </li>

                    </ul>
                </div>
                <div class="top-right">
                    <ul class="info">
                        <li>
                            <a href="mailto:contact@wivorphotos.com" style="font-size: 12px;  color: #fff;">
                                contact@wivorphotos.com
                            </a>
                    </ul>
                </div>
            </div>
        </div>
        <!-- header-lower -->
        @if (session()->has('success'))
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i> <!-- Success icon -->
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                @if (is_array(session('error')))
                    {{ implode(', ', session('error')) }}
                @else
                    {{ session('error') }}
                @endif
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li><i class="fas fa-exclamation-triangle"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="header-lower">
            <div class="outer-box">
                <div class="logo-box">
                    <figure class="logo"> <a href="{{ url('/') }}"><img
                                src="{{ asset('assets/images/logo/wivor.png') }}" alt=""></a>
                    </figure>
                </div>
                <div class="menu-area clearfix">
                    <!--Mobile Navigation Toggler-->
                    <div class="mobile-nav-toggler">
                        <i class="icon-bar"></i>
                        <i class="icon-bar"></i>
                        <i class="icon-bar"></i>
                    </div>
                    <nav class="main-menu navbar-expand-md navbar-light">
                        <div class="collapse navbar-collapse show clearfix" id="navbarSupportedContent">
                            <ul class="navigation clearfix">
                                <li class="current">
                                    <a href="{{ url('/') }}">Home</a>
                                </li>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-title"
                                        style="pointer-events: none;">@lang('header.about_us')</a>
                                    <ul>
                                        <li><a href="{{ route('about_us') }}">@lang('header.about_us')</a></li>
                                        <li><a href="{{ route('our_team') }}">@lang('header.our_team')</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown">
                                    <a href="{{ route('faq') }}" class="dropdown-title">@lang('header.faq')</a>
                                    <ul>
                                        <li><a href="{{ route('faq') }}#customers">Customer questions</a></li>
                                        <li><a href="{{ route('faq') }}#orders">Payments and downloads</a></li>
                                        <li><a href="{{ route('faq') }}#photographers">Photographer questions</a></li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="{{ route('events.listEvents') }}">@lang('header.events')</a>
                                </li>
                                <li class="dropdown">
                                    <a href="{{ route('photographers') }}" class="dropdown-title">@lang('header.photographers')</a>
                                    <ul>
                                        <li><a href="{{ route('photographers') }}#how-it-works">How it works</a></li>
                                        <li><a href="{{ route('photographers') }}#register_section">Apply as a
                                                photographer</a>
                                        </li>
                                        <li><a href="{{ route('login') }}">Photographer sign in</a></li>
                                    </ul>
                                </li>

                                <li>
                                    <a href="/list-events">@lang('header.download_photos')</a>
                                </li>

                            </ul>
                        </div>
                    </nav>

                </div>
                <ul class="nav-right">
                    <li class="search-box-outer search-toggler">
                        <i class="icon-1"></i>
                    </li>
                    @php($cartCount = count((array) session('wivor_cart', [])))
                    <li class="cart-box">
                        <a href="{{ route('cart.show') }}"
                            aria-label="View cart with {{ $cartCount }} selected {{ $cartCount === 1 ? 'photo' : 'photos' }}"><i
                                class="icon-23" aria-hidden="true"></i>
                            @if ($cartCount > 0)
                                <span
                                    class="cart-count-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                    style="min-width: 18px; font-size: 10px; line-height: 1;"
                                    aria-hidden="true">{{ $cartCount }}</span>
                            @endif
                        </a>
                    </li>

                    <a href="/login" class="btn btn-primary"
                        style="
         border-color: #ff3d00;
            background: linear-gradient(to right, #ff6700, #ff3d00);
            background: -webkit-linear-gradient(to right, #ff6700, #ff3d00);
        color: #fff;
        padding: 10px 20px;
        border-radius: 8px;
        transition: 0.3s ease;
    ">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Login
                    </a>



                </ul>
            </div>
        </div>

        <!--sticky Header-->
        <div class="sticky-header">
            <div class="outer-container">
                <div class="outer-box">
                    <div class="logo-box">
                        <figure class="logo"><a href="{{ url('/') }}"><img
                                    src="{{ asset('assets/images/logo/wivor.png') }}" alt="WivorPhotos"></a>
                        </figure>
                    </div>
                    <div class="menu-area clearfix">
                        <nav class="main-menu clearfix">
                            <!--Keep This Empty / Menu will come through Javascript-->
                        </nav>
                        <ul class="nav-right">
                            <!--  <li class="search-box-outer search-toggler">
                                    <i class="icon-1"></i>
                                </li>
                                <li class="btn-box">
                                    <button class="donate-box-btn theme-btn-one"><span>Donate Now</span></button>
                                </li> -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- Mobile Menu  -->
    <div class="mobile-menu">
        <div class="menu-backdrop"></div>
        <div class="close-btn"><i class="fas fa-times"></i></div>

        <nav class="menu-box">
            <div class="nav-logo"><a href="{{ url('/') }}"><img
                        src="{{ asset('assets/images/logo/wivor.png') }}" alt="WivorPhotos" title="WivorPhotos"></a>
            </div>
            <div class="menu-outer">
                <!--Here Menu Will Come Automatically Via Javascript / Same Menu as in Header-->
            </div>
            <!--    <div class="contact-info">
                    <h4>Contact Info</h4>
                    <ul>
                        <li>Chicago 12, Melborne City, USA</li>
                        <li><a href="tel:+8801682648101">+88 01682648101</a></li>
                        <li><a href="mailto:contact@wivorphotos.com">contact@wivorphotos.com</a></li>
                    </ul>
                </div> -->
            <div class="social-links">
                <ul class="clearfix">
                    <li><a href="https://www.facebook.com/p/WiVor-Photos-61573081696201/" target="blank"><span
                                class="fab fa-facebook-square"></span></a></li>
                    <li><a href="https://www.instagram.com/wivor.photos/" target="blank"><span
                                class="fab fa-instagram"></span></a></li>
                </ul>
            </div>
        </nav>
    </div>
