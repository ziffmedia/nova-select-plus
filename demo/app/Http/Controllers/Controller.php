<?php

namespace App\Http\Controllers;

use App\SimplePost;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function welcome()
    {
        // $simplePosts = SimplePost::all();

        return view('welcome');
    }

    // public function simplePost(SimplePost $simplePost)
    // {
    //     $bodyContentBlocks = new Document($simplePost->body_content_blocks);
    //
    //     return view('simple-post', compact('simplePost', 'bodyContentBlocks'));
    // }
}
