<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('pages.posts.index', ['posts' => Post::published()->latest('published_at')->get()]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->published_at && $post->published_at->isPast(), 404);

        return view('pages.posts.show', [
            'post' => $post,
            'more' => Post::published()->whereKeyNot($post->id)->latest('published_at')->limit(3)->get(),
        ]);
    }
}
