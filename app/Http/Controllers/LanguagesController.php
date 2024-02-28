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
        $id = auth()->id();
        $user = User::find($id)->with(['info', 'info.allLanguage'])->get();
        return $user->info->allLanguage;
        return new UserResource($user);
    }

    public function switchLanguage(Language $lang)
    {
        abort_unless(auth()->user()->info, 403);
        $info = auth()->user()->info;
        abort_unless($info->hasLanguage($lang), 404);

        $info->update(['current_language_id' => $lang->id]);

        return response('Done', 200);
    }

    public function assignLanguage(Language $lang)
    {
        abort_unless(auth()->user()->info, 403);
        $info = auth()->user()->info;
        abort_unless(!$info->hasLanguage($lang), 404);

        $lang = modelHasLanguage::create([
            'language_id' => $lang->id,
            'languageable_id' => $info->id,
            'languageable_type' => 'App\Models\UserInfo',
        ]);

        return response('Done', 200);
    }
}
