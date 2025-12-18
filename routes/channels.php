<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('hr-notifications', function ($user) {
    return $user->isHR();
});

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});