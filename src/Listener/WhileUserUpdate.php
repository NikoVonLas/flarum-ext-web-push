<?php

namespace NikoVonLas\WebPush\Listener;

use Illuminate\Contracts\Events\Dispatcher;
use Flarum\User\Event\Saving;

class WhileUserUpdate {
	public function subscribe(Dispatcher $events) {
		$events->listen(Saving::class, [$this, 'updateOneSignalId']);
	}
	public function updateOneSignalId(Saving $event) {
		$attributes = array_get($event->data, 'attributes', [ ]);
		if (array_key_exists('onesignal_user_id', $attributes)) {
			$user = $event->user;
			$actor = $event->actor;
			if ($actor->id !== $user->id) {
				$this->assertPermission($this->elementsOnlyRemoved($user->onesignal_user_id, $attributes['onesignal_user_id']));
				$this->assertCan($actor, 'edit', $user);
			}
			$user->onesignal_user_id = $attributes['onesignal_user_id'];
		}
	}
}
