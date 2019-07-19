<?php

namespace NikoVonLas\WebPush;

use Flarum\Extend;
use Flarum\Frontend\Document;
use Flarum\Event\ConfigureUserPreferences;
use Illuminate\Contracts\Events\Dispatcher;

return [
    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->content(function (Document $document) {
            $document->head[] = '<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async></script>';
        }),

    function (Dispatcher $events) {
			$events->subscribe(Listener\AddWebPushAttribute::class);
			$events->subscribe(Listener\SendWebPushNotification::class);
      $events->listen(ConfigureUserPreferences::class, function (ConfigureUserPreferences $event) {
          $event->add('webPushNotifications', 'boolval', false);
      });
    },
];
