<?php

namespace NikoVonLas\WebPush\Content;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Flarum\Settings\SettingsRepositoryInterface;
use Psr\Http\Message\ResponseInterface;

class WebPushWorkerController implements RequestHandlerInterface {
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings) {
        $this->settings = $settings;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface{
        $appName = $this->settings->get('forum_title');
        $str = "importScripts('https://cdn.onesignal.com/sdks/OneSignalSDKWorker.js');";
        return new HtmlResponse($str);
    }
}
