<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Language;
use App\Models\modelHasLanguage;
use App\Models\User;
use Illuminate\Http\Request;

class LanguagesController extends Controller
{
    public function index()
    {
        $user = User::find(auth()->id());
        $user->load('info.allLanguage');
        return new UserResource($user);
    }

    public function switchLanguage(Language $lang)
    {
        abort_unless(auth()->user()->info && auth()->user()->info()->hasLanguage($lang), 404);

        auth()->user()->info()->update(['current_language_id' => $lang->id]);

        return response('Done', 200);
    }

    public function assignLanguage(Language $lang)
    {
        abort_unless(auth()->user()->info && !auth()->user()->info()->hasLanguage($lang), 404);

        $lang = modelHasLanguage::create([
            'language_id' => $lang->id,
            'languageable_id' => auth()->user()->info()->id,
            'languageable_type' => 'App\Models\UserInfo',
        ]);

        return response('Done', 200);
    }
}
