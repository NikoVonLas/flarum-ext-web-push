import Button from 'flarum/components/Button';

export default class SubscribeWebPushButton extends Button {
  init() {
    if(this.props.state == 'unsubscribe') {
      this.props.children = app.translator.trans('nikovonlas-webpush.forum.unsubscribe');
      this.props.className = 'Button Button--unsubscribe';
      this.props.onclick = function(){window.unSubscribeWebPush()};
    } else {
      this.props.children = app.translator.trans('nikovonlas-webpush.forum.subscribe')
      this.props.className = 'Button Button--subscribe';
      this.props.onclick = function(){window.subscribeWebPush()};
    }
  }
}
