<?php

namespace App\Http\Controllers;
use App\Models\Event;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $events = Event::where('published', true)
            ->orderBy('published_at', 'desc')
            ->paginate(3);

        return view('customers.home', compact('events'));
    }
}
