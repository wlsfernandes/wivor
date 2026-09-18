<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except([
            'welcome',
            'lang',
            'aboutUs',
            'ourTeam',
            'showPost',
            'photographers',
            'signUp',
            'ourTeam',
            'junior',
            'victor',
            'carlos'
        ]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if (view()->exists($request->path())) {
            return view($request->path());
        }
        return response()->view('errors.404', [], 404); // Custom error view
    }
    /** Display the customer-focused public homepage with recently published events. */
    public function welcome(): View
    {
        $homepageEvents = Event::published()
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        return view('frontend.v1.home.index', compact('homepageEvents'));
    }
    /*Language Translation*/
    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }

    public function FormSubmit(Request $request)
    {
        return view('form-repeater');
    }

    public function aboutUs()
    {
        return view('frontend.v1.pages.about');
    }

    public function signUp()
    {
        return view('frontend.v1.pages.signup');
    }

    public function ourTeam()
    {
        return view('frontend.v1.pages.team.index');
    }

    public function junior()
    {
        return view('frontend.v1.pages.team.wilson-fernandes-junior');
    }

    public function victor()
    {
        return view('frontend.v1.pages.team.victor');
    }

    public function carlos()
    {
        return view('frontend.v1.pages.team.carlos');
    }

    public function photobook()
    {
        return view('photobook');
    }
}
