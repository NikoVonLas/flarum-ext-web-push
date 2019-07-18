import {extend} from 'flarum/extend';
import app from 'flarum/app';
import Model from 'flarum/Model';
import User from 'flarum/models/User';
import SettingsPage from 'flarum/components/SettingsPage';
import Button from 'flarum/components/Button';
import SubscribeWebPushButton from './components/SubscribeWebPushButton';
app.initializers.add('nikovonlas-webpush', () => {
    var OneSignal = window.OneSignal || [];
    extend(SettingsPage.prototype, 'notificationsItems', function(items) {
      items.add('OneSignalSubscriptionButton');
    });

    User.prototype.onesignal_user_id = Model.attribute('onesignal_user_id');
    $(document).ready(function () {
      var appId = app.forum.attribute('nikovonlas_webpush_app_id'),
          subDomain = app.forum.attribute('nikovonlas_webpush_subdomain');

      var subscribeWebPushButton = m(Button, {
        className: 'Button Button--subscribe',
        onclick: () => subscribeWebPush()
      }, app.translator.trans('nikovonlas-webpush.forum.subscribe'));
      var unSubscribeWebPushButton = m(Button, {
        className: 'Button Button--unsubscribe',
        onclick: () => unSubscribeWebPush()
      }, app.translator.trans('nikovonlas-webpush.forum.unsubscribe'));

      function subscribeWebPush () {
        Notification.requestPermission().then(function(permission) {
          OneSignal.push(['setSubscription', true]);
          if ($('.item-OneSignalSubscriptionButton').length > 0) {
            m.render($('.item-OneSignalSubscriptionButton')[0], unSubscribeWebPushButton);
          }
        });
      }

      function unSubscribeWebPush () {
        OneSignal.push(function() {
          OneSignal.push(['setSubscription', false]);
          if ($('.item-OneSignalSubscriptionButton').length > 0) {
            m.render($('.item-OneSignalSubscriptionButton')[0], subscribeWebPushButton);
          }
        });
      }

      if (app.forum.attribute('nikovonlas_webpush_type') == 'standart') {
          var initObj = {
            appId: appId,
            autoResubscribe: true,
            autoRegister: false
          };
          if (subDomain && subDomain.length > 0)
              initObj.subdomainName = subDomain;
      } else {
        var initObj = {
            appId: appId
          };
      }
      OneSignal.push(function() {
        OneSignal.init(initObj);
      });
      if (typeof app.session.user != 'undefined') {
        OneSignal.push(function() {
          OneSignal.isPushNotificationsEnabled(function(isEnabled) {
            if (isEnabled && Notification.permission == 'granted') {
              if ($('.item-OneSignalSubscriptionButton').length > 0) {
                m.render($('.item-OneSignalSubscriptionButton')[0], unSubscribeWebPushButton);
              }
            } else {
              if ($('.item-OneSignalSubscriptionButton').length > 0) {
                m.render($('.item-OneSignalSubscriptionButton')[0], subscribeWebPushButton);
              }
            }
          });
        });
        OneSignal.push(function() {
          OneSignal.on('notificationPermissionChange', function(permissionChange) {
            var currentPermission = permissionChange.to;
            if (currentPermission == 'granted') {
              OneSignal.push(['setSubscription', true]);
            } else {
              OneSignal.push(['setSubscription', false]);
            }
          });
        });
        OneSignal.push(['getNotificationPermission', function(permission) {
          if(permission == 'default') {
            subscribeWebPush();
          }
        }]);
        OneSignal.push(function() {
          OneSignal.setExternalUserId(app.session.user.id());
        });
      }
    });
});
