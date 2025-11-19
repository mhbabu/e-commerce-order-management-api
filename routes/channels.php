<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


// Customer private channel
Broadcast::channel('order.customer.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// // Admins channel (all admins)
// Broadcast::channel('orders.admin', function ($user) {
//     return $user->role === 'admin';
// });

// // Vendor private channel
// Broadcast::channel('orders.vendor.{id}', function ($user, $id) {
//     return $user->role === 'vendor' && (int) $user->id === (int) $id;
// });

// // Optional: Low stock alerts
// Broadcast::channel('low-stock.admin', function ($user) {
//     return $user->role === 'admin';
// });

// Broadcast::channel('low-stock.vendor.{id}', function ($user, $id) {
//     return $user->role === 'vendor' && (int) $user->id === (int) $id;
// });
