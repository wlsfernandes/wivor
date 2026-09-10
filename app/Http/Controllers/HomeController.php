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

        return view('site.welcome', compact('homepageEvents'));
    }
    /*
    public function root()
    {
        return view('layouts.site.welcome');
        //  return view('index');
    }
*/
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
        return view('about-us');
    }

    public function signUp()
    {
        return view('signup');
    }

    public function ourTeam()
    {
        return view('our-team');
    }

    public function junior()
    {
        return view('wilson-fernandes-junior');
    }

    public function victor()
    {
        return view('victor');
    }

    public function carlos()
    {
        return view('carlos');
    }

    public function photobook()
    {
        return view('photobook');
    }
}
