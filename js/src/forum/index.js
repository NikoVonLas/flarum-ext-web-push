import {extend} from 'flarum/extend';
import app from 'flarum/app';
import SettingsPage from 'flarum/components/SettingsPage';
import Switch from 'flarum/components/Switch';
import NotificationGrid from 'flarum/components/NotificationGrid';
app.initializers.add('nikovonlas-webpush', () => {
    extend(SettingsPage.prototype, 'notificationsItems', function(items) {
      items.add('OneSignalSubscriptionButton');
    });

    //extend(NotificationGrid.prototype, 'notificationMethods', function(items) {
    //  items.add('webpush', {
    //    name: 'push',
    //    icon: 'fas fa-bullhorn',
    //    label: app.translator.trans('nikovonlas-webpush.forum.webpush'),
    //  });
    //});

    extend(SettingsPage.prototype, 'notificationsItems', function(items) {
      items.add('webPushNotifications',
        Switch.component({
          children: app.translator.trans('nikovonlas-webpush.forum.subscribe'),
          state: this.user.preferences().webPushNotifications,
          onchange: (value, component) => {
            this.preferenceSaver('webPushNotifications')(value, component);
            if (this.user.preferences().webPushNotifications == true) {
              window.subscribeWebPush();
            } else {
              window.unSubscribeWebPush();
            }
          }
        })
      );
    });

    $(document).ready(function () {
      var OneSignal = window.OneSignal || [],
          appId = app.forum.attribute('nikovonlas_webpush.app_id'),
          subDomain = app.forum.attribute('nikovonlas_webpush.subdomain');

      window.subscribeWebPush = function () {
        Notification.requestPermission().then(function(permission) {
          OneSignal.push(['setSubscription', true]);
        });
      }

      window.unSubscribeWebPush = function () {
        OneSignal.push(function() {
          OneSignal.push(['setSubscription', false]);
        });
      }

      OneSignal.push(function() {
        OneSignal.init({
            appId: appId
        });
      });
      if (typeof app.session.user != 'undefined') {
        OneSignal.push(['getNotificationPermission', function(permission) {
          if(permission == 'default' && app.forum.attribute('nikovonlas_webpush.autoprompt')) {
            subscribeWebPush();
          }
        }]);
        OneSignal.push(function() {
          OneSignal.setExternalUserId(app.session.user.id());
        });
      }
    });
});
