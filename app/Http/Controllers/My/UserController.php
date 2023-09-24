<?php

namespace App\Http\Controllers\My;

use App\Http\Controllers\Controller;
use App\Models\Discussion;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Answer;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\User\UpdateRequest;

class UserController extends Controller
{
    public function show($username){
        $user = User::where('username',$username)->first();
        if(!$user){
            return abort(404);
        }

        $picture = filter_var($user->picture,FILTER_VALIDATE_URL) ? $user->picture : Storage::url($user->picture);
        $perPage = 5;
        $column =['*'];
        $discussionsPageName = 'discussions';
        $answerPageName = 'answers';

        return view('pages.users.show',[
            'user' => $user,
            'picture' =>$picture,
            'discussions' => Discussion::where('user_id',$user->id)->orderBy('created_at','desc')->paginate($perPage, $column, $discussionsPageName),
            'answers' => Answer::where('user_id', $user->id)->orderBy('created_at','desc')->paginate($perPage, $column, $answerPageName)
        ]);
    }

    public function edit($username){
        $user = User::where('username', $username)->first();

        if(!$user || $user->id !== auth()->id()){
            return abort(404);
        }

        $picture = filter_var($user->picture,FILTER_VALIDATE_URL) ? $user->picture : Storage::url($user->picture);
        

        return view('pages.users.form',[
            'user' => $user,
            'picture' => $picture
        ]);
    }

    public function update(UpdateRequest $request, $username ){
        $user = User::where('username', $username)->first();
        if (!$user || $user->id !== auth()->id()) {
            return abort(404);
        }

        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }
        else {
            unset($validated['password']);
        }

        if ($request->hasFile('picture')) {
            if (filter_var($user->picture, FILTER_VALIDATE_URL) === false) {
                Storage::disk('public')->delete($user->picture);
            }

            $filePath = Storage::disk('public')->put('images/users/picture', request()->file('picture'));
            $validated['picture'] = $filePath;
        }

        $update = $user->update($validated);

        if ($update) {
            session()->flash('notif.success', 'User profile updated successfully!');
            return redirect()->route('users.show', $validated['username']);
        }

        return abort(500);
    }
}
