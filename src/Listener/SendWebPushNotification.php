<?php
namespace NikoVonLas\WebPush\Listener;

use Flarum\Notification\Event\Sending;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Flarum\Foundation\Application;
use Flarum\Locale\Translator;
use Flarum\Http\UrlGenerator;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Http\Client\Common\HttpMethodsClient as HttpClient;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use OneSignal\Config;
use OneSignal\OneSignal;
use OneSignal\Exception\OneSignalException;

class SendWebPushNotification
{
	protected $oneSignalAPI;
	protected $settings;
	protected $applicationBaseURL;

	public function __construct(SettingsRepositoryInterface $settings, Application $application, UrlGenerator $url)
	{
		$this->url = $url;
		$this->settings = $settings;
		$this->applicationBaseURL = $application->config('url');

		$config = new Config();
		$config->setApplicationId($this->settings->get('nikovonlas-webpush.app_id'));
		$config->setApplicationAuthKey($this->settings->get('nikovonlas-webpush.api_key'));
		$config->setUserAuthKey($this->settings->get('nikovonlas-webpush.user_key'));

		$guzzle = new GuzzleClient();
		$client = new HttpClient(new GuzzleAdapter($guzzle), new GuzzleMessageFactory());
		$this->oneSignalAPI = new OneSignal($config, $client);
	}

	public function subscribe(Dispatcher $events)
	{
		$events->listen(Sending::class, [$this, 'sendWebPushNotification']);
	}

	public function sendWebPushNotification(Sending $event)
	{
		$locale = $this->settings->get('default_locale');

		$translator = app(Translator::class);

		$subject = $event->blueprint->getSubject();
		if($subject == null) {
			return;
		}

		$receiverUsers = $event->users;
		if(empty($receiverUsers)) {
			return;
		}
		$users = array();
		foreach ($receiverUsers as $receiverUser) {
			array_push($users, $receiverUser->id);
		}

		if (empty($users)) {
			return;
		}

		$senderUser = $event->blueprint->getFromUser();
		switch ($event->blueprint->getSubjectModel()) {
			case 'Flarum\User\User':
				$attrs = [
					'{from}' => $senderUser->getDisplayNameAttribute(),
					'{user}' => $subject->getDisplayNameAttribute()
				];
				$link = $this->url->to('forum')->route('user', ['id' =>  $subject->username]);
				break;
			case 'Flarum\Discussion\Discussion':
				$attrs = [
					'{from}' =>  $senderUser->getDisplayNameAttribute(),
					'{title}' => $this->excerpt($subject->title)
				];
				$link = $this->url->to('forum')->route('discussion', ['id' => $subject->id]);
				break;
			case 'Flarum\Post\Post':
				$attrs = [
					'{from}' =>  $senderUser->getDisplayNameAttribute(),
					'{post}' => $this->excerpt($subject->content)
				];
				$link = $this->url->to('forum')->route('discussion', ['id' => $subject->discussion_id]);
				break;
			default:
				return;
				break;
		}

		switch ($event->blueprint->getType()){
			case 'postLiked':
				$attrs = [
					'{from}' =>  $senderUser->getDisplayNameAttribute(),
					'{post}' => $this->excerpt($subject->content)
				];
				$link = $this->url->to('forum')->route('discussion', ['id' => $subject->discussion_id]);
				$heading = $translator->trans('nikovonlas-webpush.notify.like.title');
				$message = $translator->trans('nikovonlas-webpush.notify.like.message', $attrs);
				if ($locale != 'en') {
					$translator->setLocale('en');
					$heading_en = $translator->trans('nikovonlas-webpush.notify.like.title');
					$message_en = $translator->trans('nikovonlas-webpush.notify.like.message', $attrs);
				}
				break;
			case 'postMentioned':
				$attrs = [
					'{from}' =>  $senderUser->getDisplayNameAttribute(),
					'{post}' => $this->excerpt($subject->content)
				];
				$headingAttrs = [
					'{title}' => $this->excerpt($subject->discussion->title)
				];
				$link = $this->url->to('forum')->route('discussion', ['id' => $subject->discussion_id]);
			  $heading = $translator->trans('nikovonlas-webpush.notify.mention-post.title', $headingAttrs);
				$message = $translator->trans('nikovonlas-webpush.notify.mention-post.message', $attrs);
				if ($locale != 'en') {
					$translator->setLocale('en');
					$heading_en = $translator->trans('nikovonlas-webpush.notify.mention-post.title');
					$message_en = $translator->trans('nikovonlas-webpush.notify.mention-post.title', $attrs);
				}
				break;
			case 'userMentioned':
				$attrs = [
					'{from}' =>  $senderUser->getDisplayNameAttribute(),
					'{post}' => $this->excerpt($subject->content)
				];
				$headingAttrs = [
					'{title}' => $this->excerpt($subject->discussion->title)
				];
				$link = $this->url->to('forum')->route('discussion', ['id' => $subject->discussion_id]);
			  $heading = $translator->trans('nikovonlas-webpush.notify.mention.title', $headingAttrs);
				$message = $translator->trans('nikovonlas-webpush.notify.mention.message', $attrs);
				if ($locale != 'en') {
					$translator->setLocale('en');
					$heading_en = $translator->trans('nikovonlas-webpush.notify.mention.title');
					$message_en = $translator->trans('nikovonlas-webpush.notify.mention.message', $attrs);
				}
				break;
			case 'newPost':
				$attrs = [
					'{from}' =>  $senderUser->getDisplayNameAttribute(),
					'{post}' => $this->excerpt($event->blueprint->post->content)
				];
				$headingAttrs = [
					'{title}' => $this->excerpt($subject->title)
				];
				$link = $this->url->to('forum')->route('discussion', ['id' => $subject->id]);
				$heading = $translator->trans('nikovonlas-webpush.notify.post.title', $headingAttrs);
				$message = $translator->trans('nikovonlas-webpush.notify.post.message', $attrs);
				if ($locale != 'en') {
					$translator->setLocale('en');
					$heading_en = $translator->trans('nikovonlas-webpush.notify.post.title');
					$message_en = $translator->trans('nikovonlas-webpush.notify.post.message', $attrs);
				}
				break;
			case 'discussionRenamed':
				$attrs = [
					'{from}' =>  $senderUser->getDisplayNameAttribute(),
					'{title}' => $this->excerpt($subject->title)
				];
				$link = $this->url->to('forum')->route('discussion', ['id' => $subject->id]);
				$heading = $translator->trans('nikovonlas-webpush.notify.rename.title');
				$message = $translator->trans('nikovonlas-webpush.notify.rename.message', $attrs);
				if ($locale != 'en') {
					$translator->setLocale('en');
					$heading_en = $translator->trans('nikovonlas-webpush.notify.rename.title');
					$message_en = $translator->trans('nikovonlas-webpush.notify.rename.message', $attrs);
				}
				break;
			case 'discussionLocked':
				$attrs = [
					'{from}' =>  $senderUser->getDisplayNameAttribute(),
					'{title}' => $this->excerpt($subject->title)
				];
				$link = $this->url->to('forum')->route('discussion', ['id' => $subject->id]);
				$heading = $translator->trans('nikovonlas-webpush.notify.lock.title');
				$message = $translator->trans('nikovonlas-webpush.notify.lock.message', $attrs);
				if ($locale != 'en') {
					$translator->setLocale('en');
					$heading_en = $translator->trans('nikovonlas-webpush.notify.lock.title');
					$message_en = $translator->trans('nikovonlas-webpush.notify.lock.message', $attrs);
				}
				break;
			case 'discussionDeleted':
				// NOT IMPLEMENT!
				$attrs = [
					'{from}' =>  $senderUser->getDisplayNameAttribute(),
					'{title}' => $this->excerpt($subject->title)
				];
				$link = $this->url->to('forum')->route('discussion', ['id' => $subject->id]);
				$heading = $translator->trans('nikovonlas-webpush.notify.delete.title');
				$message = $translator->trans('nikovonlas-webpush.notify.delete.message', $attrs);
				if ($locale != 'en') {
					$translator->setLocale('en');
					$heading_en = $translator->trans('nikovonlas-webpush.notify.delete.title');
					$message_en = $translator->trans('nikovonlas-webpush.notify.delete.message', $attrs);
				}
				break;
			case 'userSuspended':
				$attrs = [
					'{from}' => $senderUser->getDisplayNameAttribute(),
					'{user}' => $subject->getDisplayNameAttribute()
				];
				$link = $this->url->to('forum')->route('user', ['id' =>  $subject->username]);
				$heading = $translator->trans('nikovonlas-webpush.notify.suspend.title');
				$message = $translator->trans('nikovonlas-webpush.notify.suspend.message', $attrs);
				if ($locale != 'en') {
					$translator->setLocale('en');
					$heading_en = $translator->trans('nikovonlas-webpush.notify.suspend.title');
					$message_en = $translator->trans('nikovonlas-webpush.notify.suspend.message', $attrs);
				}
				break;
			case 'userUnsuspended':
				$attrs = [
					'{from}' => $senderUser->getDisplayNameAttribute(),
					'{user}' => $subject->getDisplayNameAttribute()
				];
				$link = $this->url->to('forum')->route('user', ['id' =>  $subject->username]);
				$heading = $translator->trans('nikovonlas-webpush.notify.unsuspend.title');
				$message = $translator->trans('nikovonlas-webpush.notify.unsuspend.message', $attrs);
				if ($locale != 'en') {
					$translator->setLocale('en');
					$heading_en = $translator->trans('nikovonlas-webpush.notify.unsuspend.title');
					$message_en = $translator->trans('nikovonlas-webpush.notify.unsuspend.message', $attrs);
				}
				break;
			case 'newDiscussionInTag':
				$attrs = [
					'{from}' =>  $senderUser->getDisplayNameAttribute(),
					'{title}' => $this->excerpt($subject->title)
				];
				$link = $this->url->to('forum')->route('discussion', ['id' => $subject->id]);
				$heading = $translator->trans('nikovonlas-webpush.notify.tag.discussion.title');
				$message = $translator->trans('nikovonlas-webpush.notify.tag.discussion.message', $attrs);
				if ($locale != 'en') {
					$translator->setLocale('en');
					$heading_en = $translator->trans('nikovonlas-webpush.notify.tag.discussion.title');
					$message_en = $translator->trans('nikovonlas-webpush.notify.tag.discussion.message', $attrs);
				}
				break;
			case 'newPostInTag':
				$attrs = [
					'{from}' =>  $senderUser->getDisplayNameAttribute(),
					'{post}' => $this->excerpt($event->blueprint->post->content)
				];
				$headingAttrs = [
					'{title}' => $this->excerpt($subject->title)
				];
				$link = $this->url->to('forum')->route('discussion', ['id' => $subject->id]);
				$heading = $translator->trans('nikovonlas-webpush.notify.tag.post.title', $headingAttrs);
				$message = $translator->trans('nikovonlas-webpush.notify.tag.post.message', $attrs);
				if ($locale != 'en') {
					$translator->setLocale('en');
					$heading_en = $translator->trans('nikovonlas-webpush.notify.tag.post.title');
					$message_en = $translator->trans('nikovonlas-webpush.notify.tag.post.message', $attrs);
				}
				break;
			default:
				return;
				break;
		}
		try {
			$heading = $this->clearStr($heading);
			$message = $this->clearStr($message);
			if ($locale != 'en') {
				$translator->setLocale($locale);
				$heading_en = $this->clearStr($heading);
				$message_en = $this->clearStr($message);
				$notification = [
					'headings' => [
						'en' => $heading_en,
						$locale => $heading
					],
					'contents' => [
						'en' => $message_en,
			      $locale => $message
			    ],
			    'include_external_user_ids' => $users,
					'url' => $link
				];
			} else {
				$notification = [
					'headings' => [
						'en' => $heading
					],
					'contents' => [
						'en' => $message
			    ],
			    'include_external_user_ids' => $users,
					'url' => $link
				];
			}
			$this->oneSignalAPI->notifications->add($notification);
		} catch (OneSignalException $e) {
			return;
		}
	}

	private function excerpt($str) {
		$length = $this->settings->get('nikovonlas-webpush.excerpt_length');
		if (mb_strlen($str) > $length) {
				$str = mb_substr(strip_tags($str), 0, $length);
				$str .= '...';
		}
		return $str;
	}

	private function clearStr($str) {
		$str = preg_replace('/@[^#]+#[0-9]+ /', '', $str);
		return $str;
	}
}
