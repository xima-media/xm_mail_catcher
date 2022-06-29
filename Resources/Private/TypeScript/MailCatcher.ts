import $ = require('jquery');
import Modal = require('TYPO3/CMS/Backend/Modal');
import Severity = require('TYPO3/CMS/Backend/Severity');

class MailCatcher {
  constructor() {
    this.bindListener();
  }

  public bindListener() {
    $('.content-type-switches a').on('click', this.onContentTypeSwitchClick.bind(this));
    $('button[data-delete]').on('click', this.onDeleteButtonClick.bind(this));
    $('#delete-all-messages').on('click', this.onDeleteAllMessagesClick.bind(this));
    $('.panel.panel-default .form-irre-header-body').on('click', this.onPanelClick.bind(this));
  }

  protected onPanelClick(e: Event) {
    e.preventDefault();
    e.stopPropagation();
    const $body = $(e.currentTarget).closest('.panel').find('.panel-collapse');
    if ($body.hasClass('collapse')) {
      $body.removeClass('collapse');
    } else {
      $body.addClass('collapse');
    }

    // load html mail if no plain text body available
    const htmlIsLoaded = $(e.currentTarget).attr('data-html-loaded') === 'true';
    const onlyHtmlButton = $(e.currentTarget).closest('.panel').find('.content-type-switches a[data-content-type="html"]:only-child');
    if (onlyHtmlButton.length && !htmlIsLoaded) {
      const messageId = $(e.currentTarget).closest('.panel').attr('data-message-file');
      this.loadHtmlMail(messageId);
    }

  }

  protected onDeleteAllMessagesClick(e: Event) {
    e.preventDefault();
    const self = this;

    Modal.confirm('Delete Messages', 'Are you sure, you want to delete all messages?', Severity.warning, [
      {
        text: 'Yes, delete',
        btnClass: 'btn-danger',
        trigger: function () {
          self.deleteAllMessages();
          Modal.dismiss();
        }
      },
      {
        text: 'No, abort',
        btnClass: 'primary-outline',
        active: true,
        trigger: function () {
          Modal.dismiss();
        }
      }
    ]);
  }

  protected deleteAllMessages() {
    const self = this;
    const $panel = $('.panel[data-message-file]');

    $.ajax({
      url: TYPO3.settings.ajaxUrls.mailcatcher_delete_all
    }).done(function (data) {
      if (data.success) {
        $panel.remove();
        self.refreshMessageCount();
        top.TYPO3.Notification.success('Success', 'All messages have been deleted', 3);
        return;
      }
      top.TYPO3.Notification.error('Error', 'Could not delete messages', 3);
    });
  }

  protected refreshMessageCount() {
    const count = $('.panel[data-message-file]').length;
    $('*[data-message-count]').attr('data-message-count', count);
    $('.message-count').html(count.toString());
  }

  protected onDeleteButtonClick(e: Event) {
    e.preventDefault();
    const $panel = $(e.currentTarget).closest('.panel');
    const messageFile = $panel.attr('data-message-file');
    const self = this;
    const url = TYPO3.settings.ajaxUrls.mailcatcher_delete + '&' + $.param({messageFile: messageFile});

    $.ajax({
      url: url
    }).done(function (data) {
      if (data.success) {
        $panel.remove();
        self.refreshMessageCount();
        return;
      }
      top.TYPO3.Notification.error('Error', 'Could not delete message', 3);
    });
  }

  protected onContentTypeSwitchClick(e: Event) {
    e.preventDefault();

    const contentType = $(e.currentTarget).attr('data-content-type');
    const mId = $(e.currentTarget).attr('data-m-id');

    $('.content-type-switches a[data-m-id="' + mId + '"]').addClass('btn-default').removeClass('btn-primary');
    $('.content-type-switches a[data-m-id="' + mId + '"][data-content-type="' + contentType + '"]').removeClass('btn-default').addClass('btn-primary');

    $('.form-section[data-m-id="' + mId + '"]').addClass('hidden');
    const $formSection = $('.form-section[data-m-id="' + mId + '"][data-content-type="' + contentType + '"]');
    $formSection.removeClass('hidden');

    const $panel = $(e.currentTarget).closest('.panel');

    if ($panel.attr('data-html-loaded') === 'false') {
      this.loadHtmlMail($panel.attr('data-message-file'));
    }
  }

  protected loadHtmlMail(messageFile: string) {

    const url = TYPO3.settings.ajaxUrls.mailcatcher_html + '&' + $.param({messageFile: messageFile});

    $.ajax({
      url: url
    }).done(function (data) {
      const $iframe = $('<iframe />')
        .attr('frameBorder', '0')
        .attr('width', '100%')
        .attr('height', '500px')
        .attr('srcdoc', data.src);
      // @ts-ignore
      $('.panel[data-message-file="' + messageFile + '"] .form-section[data-content-type="html"]').html($iframe);
    });

  }
}

export const XmMailCatcher = new MailCatcher();
