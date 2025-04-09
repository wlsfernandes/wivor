<?php

namespace App\Http\Controllers;
use App\Models\Event;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function dashboard()
    {
        $events = Event::where('published', true)
            ->orderBy('published_at', 'desc')
            ->paginate(3);

        return view('customers.dashboard', compact('events'));
    }
}
