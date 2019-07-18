import SettingsModal from 'flarum/components/SettingsModal';

export default class WebPushSettingsModal extends SettingsModal {
    className() {
        return 'WebPushSettingsModal Modal--small';
    }

    title() {
        return app.translator.trans('nikovonlas-webpush.admin.web-push-settings.title');
    }

    form() {
        return [
            <div className="form-group">
                <label>{app.translator.trans('nikovonlas-webpush.admin.web-push-settings.app_id')}</label>
                <input className="FormControl" bidi={this.setting('nikovonlas-webpush.onesignal_app_id')}/>
            </div>,
            <div className="form-group">
                <label>{app.translator.trans('nikovonlas-webpush.admin.web-push-settings.api_key')}</label>
                <input className="FormControl" bidi={this.setting('nikovonlas-webpush.onesignal_api_key')}/>
            </div>,
            <div className="form-group">
                <label>{app.translator.trans('nikovonlas-webpush.admin.web-push-settings.user_key')}</label>
                <input className="FormControl" bidi={this.setting('nikovonlas-webpush.onesignal_user_key')}/>
            </div>,
            <div className="form-group">
                <label>{app.translator.trans('nikovonlas-webpush.admin.web-push-settings.subdomain')}</label>
                <input className="FormControl" bidi={this.setting('nikovonlas-webpush.onesignal_subdomain')}/>
            </div>,
            <div className="form-group">
                <label>{app.translator.trans('nikovonlas-webpush.admin.web-push-settings.excerpt')}</label>
                <input className="FormControl" bidi={this.setting('nikovonlas-webpush.excerpt_length')}/>
            </div>
        ];
    }
}
