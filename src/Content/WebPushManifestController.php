<?php

namespace NikoVonLas\WebPush\Content;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Flarum\Settings\SettingsRepositoryInterface;
use Psr\Http\Message\ResponseInterface;

class WebPushManifestController implements RequestHandlerInterface {
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings) {
        $this->settings = $settings;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface{
        $appName = $this->settings->get('forum_title');
        $json = '{
            "name": "' . $appName . '",
            "short_name": "' . $appName . '",
            "start_url": "/",
            "display": "standalone",
            "gcm_sender_id": "482941778795",
            "gcm_sender_id_comment": "Do not change the GCM Sender ID"
        }';
        return new HtmlResponse($json);
    }
}
