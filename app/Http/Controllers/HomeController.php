<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Answer;
use App\Models\Discussion;
use App\Models\User;
class HomeController extends Controller
{
    public function index()
    {
        return view('pages.Home', [
            'answerCount' => Answer::count(),
            'discussionCount' => Discussion::count(),
            'userCount' => User::count(),
            'latestDiscussions' => Discussion::with('category', 'user')
                ->orderBy('created_at', 'desc')->limit(3)->get(),
        ]);
    }
}
