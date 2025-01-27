<?php


Broadcast::channel('chat', function ($user) {
    return true;  // หรือสามารถตรวจสอบสิทธิ์ เช่น $user->id === 1
});