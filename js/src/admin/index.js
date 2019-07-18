import WebPushSettingsModal from './components/WebPushSettingsModal';

app.initializers.add('nikovonlas-web-push', () => {
  app.extensionSettings['nikovonlas-web-push'] = () => app.modal.show(new WebPushSettingsModal());
});
