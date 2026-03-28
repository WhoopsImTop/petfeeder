<?php
echo "Subscriptions count: " . App\Models\PushSubscription::count() . "\n";
echo "Feeding plans count: " . App\Models\FeedingPlan::count() . "\n";

$user = App\Models\User::first();
if ($user) {
    if (method_exists($user, 'routeNotificationForWebPush')) {
        echo "User has routeNotificationForWebPush!\n";
    } else {
        echo "User DOES NOT HAVE routeNotificationForWebPush. This might be why Push Notifications fail!\n";
    }
    $subs = $user->pushSubscriptions;
    echo "User has " . $subs->count() . " active subscriptions.\n";
    if ($subs->count() > 0) {
        echo "Endpoint: " . $subs->first()->endpoint . "\n";
    }
}
