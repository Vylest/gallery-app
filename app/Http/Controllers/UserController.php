<?php

namespace Magnus\Http\Controllers;

use Magnus\User;
use Magnus\Role;
use Magnus\Watch;
use Magnus\Gallery;
use Magnus\Profile;
use Magnus\Preference;
use Magnus\Permission;
use Magnus\Notification;
use Magnus\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        //$users = User::paginate(10);

        $input = $request->all();

        if (!\Request::wantsJson()) {
            return view('user.index');
        }

        if (isset($input['sorting'])) {
            $orderParam = $input['sorting'];
            $orderBy = key($orderParam);
            $direction = $input['sorting'][key($orderParam)];
        } else {
            $orderBy = 'id';
            $direction = 'asc';
        }

        $query = User::query()->orderBy($orderBy, $direction);

        if (isset($input['filter'])) {
            $filterParam = $input['filter'];

            foreach ($filterParam as $key => $value) {
                $filterValue = '%' . $value . '%';
                $column = $key;
                $query = $query->where($column, 'like', $filterValue);
            }
        }


        $users = $query->paginate();

        return response()->json($users);

//        foreach ($users as &$user) {
//            $user->permission_id = Permission::where('id', $user->permission_id)->value('schema_name');
//        }

    }

    public function show(User $user)
    {
        //$user = User::where('id', $id)->first();
        $permissions = Permission::lists('schema_name', 'id');

        return view('user.edit', compact('user', 'permissions'));
    }

    public function create()
    {
        $roles = Role::lists('role_name', 'id');
        
        return view('user.create', compact('roles'));
    }

    public function store(Requests\UserCreateRequest $request)
    {
        //dd($request->all());
//        $user = new User;

//        $user->username = $request->username;
//        $user->name = $request->name;
//        $user->slug = str_slug($request->username);
//        $user->email = $request->email;
//        $user->password = bcrypt($request->password);
//
//        $user = $user->create();
        $user = User::create([
            'username' => $request->username,
            'name'      => $request->name,
            'slug'      => str_slug($request->username),
            'email'     => $request->email,
            'timezone'  => $request->timezone,
            'password'  =>bcrypt($request->password)
        ]);

        $user->roles()->attach($request->input('role_id'));
        $user->profile()->save(new Profile(['biography'=>'Not filled out yet']));
        $user->preferences()->save(new Preference(['sex' => '', 'show_sex' => 0, 'date_of_birth' => '0000-00-00', 'show_dob' => 'none', 'per_page' => 24]));
        Gallery::makeDirectories($user);
        
        return redirect()->route('admin.users.index')->with('success', 'New user '. $user->username .' was created.');
    }

    public function edit(User $user)
    {
        $roles = Role::lists('role_name', 'id');
        return view('user.edit', compact('user', 'roles'));
    }

    public function update(User $user, Requests\UserEditRequest $request)
    {
        if ($request->password == $request->password_confirmation) {
            $user->name = $request->name;
            $user->username = $request->username;
            $user->email = $request->email;
            $user->slug = str_slug($request->username, '-');
            $user->timezone = $request->input('timezone');
            if ($request->password != "" and $request->password != null) {
                $user->password = bcrypt($request->password);
            }
            $user->roles()->detach();

            $user->roles()->attach($request->input('role_id'));

            $user->save();

            return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
        } else {
            //return redirect()->back()->withErrors('Password does not match the confirmation');
        }
    }

    public function destroy(User $user)
    {
        //$user = User::findOrFail($id);

        if ($user->delete()) {
            return redirect()->route('admin.users.index')->with('success', 'User Deleted!');
        } else {
            return redirect()->back()->withErrors('Account Deletion Failed!');
        }
    }

    public function login()
    {
        return view('auth.login');
    }

    /**
     *  Add the selected user to the Auth'd user's watch list
     * @param Request $request
     * @param User $user
     * @return $this|\Illuminate\Http\RedirectResponse\
     */
    public function watchUser(Request $request, User $user)
    {
        foreach ($request->user()->watchedUsers as $watched) {
            if ($user->id == $watched->user_id) {
                return redirect()->to(app('url')->previous())->withErrors('You are already watching this user!');
            }
        }
        if (Auth::user()-> id != $user->id) {
            Watch::watchUser(Auth::user(), $user, $request);
            Notification::notifyUserNewWatch($user, $request->user());
            return redirect()->to(app('url')->previous())->with('success', 'You have added ' . $user->name . ' to your watch list!');
        } else {
            return redirect()->to(app('url')->previous())->withErrors('You can\'t watch yourself!');
        }
    }

    /**
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unwatchUser(Request $request, User $user)
    {
        Watch::unwatchUser(Auth::user(), $user);
        return redirect()->to(app('url')->previous())->with('success', 'You have unwatched '.$user->name);
    }
}
