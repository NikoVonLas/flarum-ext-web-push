<?php

namespace NikoVonLas\WebPush;

use Flarum\Extend;
use Flarum\Frontend\Document;
use Illuminate\Contracts\Events\Dispatcher;
use NikoVonLas\WebPush\WebPushManifestController;

return [
    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    (new Extend\Frontend('forum'))
        ->js('https://cdn.onesignal.com/sdks/OneSignalSDK.js')
        ->js(__DIR__.'/js/dist/forum.js')
        ->content(function (Document $document) {
            $document->head[] = '<link rel="manifest" href="/manifest.json">';
        }),

    (new Extend\Routes('forum'))
        ->get('/manifest.json', 'nikovonlas.webpush.manifest', WebPushManifestController::class)
        ->get('/OneSignalSDKWorker.js', 'nikovonlas.webpush.worker', WebPushWorkerController::class)
        ->get('/OneSignalSDKUpdaterWorker.js', 'nikovonlas.webpush.updater', WebPushUpdaterController::class),

    function (Dispatcher $events) {
			$events->subscribe(Listener\AddWebPushAttribute::class);
			$events->subscribe(Listener\WhileUserUpdate::class);
			$events->subscribe(Listener\SendWebPushNotification::class);
    },
];
