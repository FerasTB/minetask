<?php

use App\Models\HasRole;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('App.Models.Doctor.{id}', function ($user, $id) {
    return (int) $user->doctor->id === (int) $id;
});

Broadcast::channel('App.Models.DentalLab.{id}', function ($user, $id) {
    $role = HasRole::where(['roleable_id' => $id, 'roleable_type' => 'App\Models\DentalLab', 'user_id' => $user->id])->first();
    return $role != null;
});
