import WebPushSettingsModal from './components/WebPushSettingsModal';

app.initializers.add('nikovonlas-webpush', () => {
  app.extensionSettings['nikovonlas-webpush'] = () => app.modal.show(new WebPushSettingsModal());
});
