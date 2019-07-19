<?php

namespace NikoVonLas\WebPush\Listener;

use Flarum\Api\Serializer\UserSerializer;
use Flarum\Api\Event\Serializing;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;

class AddWebPushAttribute {
	protected $settings;

	public function __construct(SettingsRepositoryInterface $settings) {
		$this->settings = $settings;
	}

	public function subscribe(Dispatcher $events) {
		$events->listen(Serializing::class, [$this, 'addAttributes']);
	}

	public function addAttributes(Serializing $event) {
		$event->attributes['nikovonlas_webpush.app_id'] = $this->settings->get('nikovonlas-webpush.app_id');
		$event->attributes['nikovonlas_webpush.subdomain'] = $this->settings->get('nikovonlas-webpush.subdomain');
	}
}
