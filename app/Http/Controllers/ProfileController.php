<?php

namespace Magnus\Http\Controllers;

use Magnus\Opus;
use Magnus\User;
use Magnus\Profile;
use Magnus\Gallery;
use Magnus\Favorite;
use Magnus\Http\Requests;
use Magnus\Helpers\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{


    public function __construct()
    {
        $this->middleware(
            'auth',
            [
                'only' => ['create','store','edit','update','destroy']
            ]
        );
    }

    /**
     * Display the auth'd user's profile
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $profile = $user->profile;
        $galleries = $user->galleries()->limit(4)->get();
        $opera = $user->opera()->orderBy('created_at', 'desc')->limit(6)->get();

        return view('profile.show', compact('profile', 'user', 'galleries', 'opera'));
    }

    /**
     * Return a list of users that watch $user
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function watchers(User $user)
    {
        $query = User::query();
        $query->join('user_watch', 'users.id', '=', 'user_watch.watcher_user_id');
        $query->where('user_watch.watched_user_id', $user->id);
        $query->orderBy('user_watch.created_at', 'desc');
        $query->select('users.name', 'users.username', 'users.id', 'users.slug', 'user_watch.created_at');
        $watchers = $query->paginate(50);

        if ($user->name != null) {
            return view('profile.watchers', compact('user', 'watchers'));
        } else {
            return abort(404);
        }
    }

    public function watching(User $user)
    {
        $query = User::query();
        $query->join('user_watch', 'users.id', '=', 'user_watch.watched_user_id');
        $query->where('user_watch.watcher_user_id', $user->id);
        $query->orderBy('user_watch.created_at', 'desc');
        $query->select('users.name', 'users.username', 'users.id', 'users.slug', 'user_watch.created_at');
        $watchers = $query->paginate(50);
        if ($user->name != null) {
            return view('profile.watching', compact('user', 'watchers'));
        } else {
            return abort(404);
        }
    }

    /**
     * Return all the galleries of a user
     *
     * @param User $user
     */
    public function galleries(User $user)
    {
        $galleries = $user->galleries()->orderBy('created_at', 'desc')->paginate(Helpers::perPage());
        return view('profile.gallery', compact('galleries', 'user'));
    }

    /**
     * return all the opus of a user
     *
     * @param User $user
     */
    public function opera(User $user)
    {
        $profile = $user->profile;
        $opera = $user->opera()->orderBy('created_at', 'desc')->paginate(Helpers::perPage());
        return view('profile.opera', compact('user', 'profile', 'opera'));
    }

    public function favorites(User $user)
    {
        $profile = $user->profile;
        $favorites = $user->favorites()->orderBy('favorite_user.created_at', 'desc')->paginate(Helpers::perPage());

        return view('profile.favorites', compact('profile', 'favorites', 'user'));
        //dd($favorites);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     *  Display the specified user's profile
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(User $user)
    {
        $profile = $user->profile;
        $opera = $user->opera()->limit(6)->orderBy('created_at', 'desc')->get();
        $favorites = $user->favorites()->limit(6)->orderBy('favorite_user.created_at', 'desc')->get();
        $journal = $user->journals()->orderBy('created_at', 'desc')->first();

        if ($user->name != null) {
            return view('profile.show', compact('profile', 'user', 'galleries', 'opera', 'favorites', 'journal'));
        } else {
            return abort(404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
