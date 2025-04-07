<?php

namespace App\Http\Controllers;
use App\Models\Post;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $posts = Post::where('published', true)
            ->whereHas('postType', function ($query) {
                $query->where('name', 'Event');
            })
            ->orderBy('published_at', 'desc')
            ->paginate(3);

        return view('customers.home', compact('posts'));
    }
}
