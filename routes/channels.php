<?php


// Broadcast::channel('chat', function ($user) {
//     return true;  // หรือสามารถตรวจสอบสิทธิ์ เช่น $user->id === 1
// });


Broadcast::channel('chat.{user1}.{user2}', function ($user, $user1, $user2) {
    return in_array($user->id, [(int) $user1, (int) $user2]);
});