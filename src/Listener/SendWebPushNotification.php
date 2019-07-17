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
		$config->setApplicationId($this->settings->get('nikovonlas-webpush.onesignal_app_id'));
		$config->setApplicationAuthKey($this->settings->get('nikovonlas-webpush.onesignal_api_key'));
		$config->setUserAuthKey($this->settings->get('nikovonlas-webpush.onesignal_user_key'));

		$guzzle = new GuzzleClient;
		$client = new HttpClient(new GuzzleAdapter($guzzle), new GuzzleMessageFactory());
		$this->oneSignalAPI = new OneSignal($config, $client);
	}

	public function subscribe(Dispatcher $events)
	{
		$events->listen(Sending::class, [$this, 'sendWebPushNotification']);
	}

	public function sendWebPushNotification(Sending $event)
	{
		$translator = app(Translator::class);

		$subject = $event->blueprint->getSubject();
		if($subject == null) {
			throw new \Exception('Empty subject');
		}

		$receiverUsers = $event->users;
		if(empty($receiverUsers)) {
			throw new \Exception('Empty reciver users');
		}
		$users = array();
		foreach ($receiverUsers as $receiverUser) {
			if($receiverUser->onesignal_user_id != null) {
				array_push($users, $receiverUser->onesignal_user_id);
			}
		}

		$senderUser = $event->blueprint->getFromUser();
		switch ($subject->getSubjectModel()) {
			case 'Flarum\User\User':
				$attrs = [
					'from' => $senderUser->getDisplayNameAttribute(),
					'user' => $subject->getDisplayNameAttribute()
				];
				$link = $this->url->to('forum')->route('users.show', ['id' =>  $subject->username]);
				break;
			case 'Flarum\Discussion\Discussion':
				$attrs = [
					'from' =>  $senderUser->getDisplayNameAttribute(),
					'title' => $this->excerpt($subject->title)
				];
				$link = $this->url->to('forum')->route('discussions.show', ['id' => $subject->id]);
				break;
			case 'Flarum\Post\Post':
				$attrs = [
					'from' =>  $senderUser->getDisplayNameAttribute(),
					'post' => $this->excerpt($subject->content)
				];
				$link = $this->url->to('forum')->route('posts.show', ['id' => $subject->id]);
				break;
			default:
				return;
				break;
		}

		$notificationType = $event->blueprint->getType();
		switch ($notificationType){
			case 'postLiked':
				$heading = $translator->trans('nikovonlas-webpush.notify.like.title');
				$message = $translator->trans('nikovonlas-webpush.notify.like.message', $attrs);
				break;
			case 'postMentioned':
			  $heading = $translator->trans('nikovonlas-webpush.notify.mention-post.title');
				$message = $translator->trans('nikovonlas-webpush.notify.mention-post.message', $attrs);
				break;
			case 'userMentioned':
			  $heading = $translator->trans('nikovonlas-webpush.notify.mention.title');
				$message = $translator->trans('nikovonlas-webpush.notify.mention.message', $attrs);
				break;
			case 'newPost':
				$heading = $translator->trans('nikovonlas-webpush.notify.post.title');
				$message = $translator->trans('nikovonlas-webpush.notify.post.message', $attrs);
				break;
			case 'discussionRenamed':
				$heading = $translator->trans('nikovonlas-webpush.notify.rename.title');
				$message = $translator->trans('nikovonlas-webpush.notify.rename.message', $attrs);
				break;
			case 'discussionLocked':
				$heading = $translator->trans('nikovonlas-webpush.notify.lock.title');
				$message = $translator->trans('nikovonlas-webpush.notify.lock.message', $attrs);
				break;
			case 'discussionDeleted':
				$heading = $translator->trans('nikovonlas-webpush.notify.delete.title');
				$message = $translator->trans('nikovonlas-webpush.notify.delete.message', $attrs);
				break;
			case 'userSuspended':
				$heading = $translator->trans('nikovonlas-webpush.notify.suspend.title');
				$message = $translator->trans('nikovonlas-webpush.notify.suspend.message', $attrs);
				break;
			case 'userUnsuspended':
				$heading = $translator->trans('nikovonlas-webpush.notify.unsuspend.title');
				$message = $translator->trans('nikovonlas-webpush.notify.unsuspend.message', $attrs);
				break;
			case 'newDiscussionInTag':
				$heading = $translator->trans('nikovonlas-webpush.notify.tag.discussion.title');
				$message = $translator->trans('nikovonlas-webpush.notify.tag.discussion.message', $attrs);
				break;
			case 'newPostInTag':
				$heading = $translator->trans('nikovonlas-webpush.notify.tag.post.title');
				$message = $translator->trans('nikovonlas-webpush.notify.tag.post.message', $attrs);
				break;
			default:
				return;
				break;
		}

		$api->notifications->add([
			'headings' => [
				'ru' => $heading
			],
			'contents' => [
	        'ru' => $message
	    ],
	    'include_external_user_ids' => $users,
			'url' => $link
		]);
	}

	private function excerpt($str) {
		$length = $this->settings->get('nikovonlas-webpush.excerpt_length');
		if (mb_strlen($str) > $length) {
				$str = mb_substr(strip_tags($str), 0, $length);
				$str .= '...';
		}
		return $str;
	}
}
