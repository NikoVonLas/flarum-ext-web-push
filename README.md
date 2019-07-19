# Web push notifications with OneSignal for Flarum

### installation

Install manually:

```bash
composer require nikovonlas/flarum-ext-web-push
```

### configuration

Upload files from /vendor/nikovonlas/flarum-ext-web-push/upload/ to you public folder.
Activate the extension in the admin panel of your Flarum.
Register in onesignal.com and create a new app an configure notifications as typical site, and use the Settings dialog in your website to configure the extension.

### updating

```bash
composer update nikovonlas/flarum-ext-web-push
php flarum cache:clear
```

### todo
1. Subscribation variations to settings page
2. Provide prompts from Onesignal
3. Custom code site type

### links
[Flarum disscussion page](https://discuss.flarum.org/d/20784-onesignal-web-push-notifications)

[Packagist](https://packagist.org/packages/nikovonlas/flarum-ext-web-push)
