import SettingsModal from 'flarum/components/SettingsModal';
import Switch from 'flarum/components/Switch';

const settingsPrefix = 'nikovonlas-webpush.';
const translationPrefix = 'nikovonlas-webpush.admin.settings.';

export default class WebPushSettingsModal extends SettingsModal {
    className() {
        return 'WebPushSettingsModal Modal--small';
    }

    title() {
        return app.translator.trans(translationPrefix + 'title');
    }

    form() {
        const switchSetting = (settingSuffix, labelSuffix, defaultValue, help = false) => {
            return m('.Form-group', [
                Switch.component({
                    state: this.setting(settingsPrefix + settingSuffix, defaultValue ? '1' : '0')() === '1',
                    onchange: value => {
                        this.setting(settingsPrefix + settingSuffix)(value ? '1' : '0');
                    },
                    children: app.translator.trans(translationPrefix + labelSuffix),
                }),
                help ? m('.helpText', app.translator.trans(translationPrefix + labelSuffix + 'Help')) : null,
            ]);
        };
        const inputSetting = (settingSuffix, labelSuffix, placeholder = false, help = false) => {
          return m('.Form-group', [
            m('label', app.translator.trans(translationPrefix + labelSuffix)),
            m('input.FormControl', {
              bidi: this.setting(settingsPrefix + settingSuffix),
              placeholder: placeholder ? app.translator.trans(translationPrefix + labelSuffix + 'Placeholder') : '',
            }),
            help ? m('.helpText', app.translator.trans(translationPrefix + labelSuffix + 'Help')) : null,
          ]);
        }
        return [
            inputSetting('app_id', 'app_id'),
            inputSetting('api_key', 'api_key'),
            inputSetting('user_key', 'user_key'),
            inputSetting('subdomain', 'subdomain'),
            inputSetting('excerpt_length', 'excerpt'),
            switchSetting('autoprompt', 'autoprompt')
        ];
    }
}
