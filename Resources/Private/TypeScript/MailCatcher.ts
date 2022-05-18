import $ = require('jquery');
import AjaxRequest = require('TYPO3/CMS/Core/Ajax/AjaxRequest');

class MailCatcher {
	constructor() {
		this.bindListener();
	}

	public bindListener() {
		$('.content-type-switches a').on('click', this.onContentTypeSwitchClick.bind(this));
	}

	protected onContentTypeSwitchClick(e: Event) {
		e.preventDefault();

		const contentType = $(e.currentTarget).attr('data-content-type');
		const mId = $(e.currentTarget).attr('data-m-id');

		$('.content-type-switches a[data-m-id="' + mId + '"]').addClass('btn-outline-primary').removeClass('btn-primary');
		$('.content-type-switches a[data-m-id="' + mId + '"][data-content-type="' + contentType + '"]').removeClass('btn-outline-primary').addClass('btn-primary');

		$('.form-section[data-m-id="' + mId + '"]').addClass('hidden');
		const $formSection = $('.form-section[data-m-id="' + mId + '"][data-content-type="' + contentType + '"]');
		$formSection.removeClass('hidden');

		const $panel = $(e.currentTarget).closest('.panel');

		if ($panel.attr('data-html-loaded') === 'false') {
			this.loadHtmlMail($panel.attr('data-message-file'));
		}
	}

	protected loadHtmlMail(messageFile: string) {
		new AjaxRequest(TYPO3.settings.ajaxUrls.mailcatcher_html)
			.withQueryArguments({messageFile: messageFile})
			.get()
			.then(async function (response) {
				const resolved = await response.resolve();
				console.log(resolved);
				const $iframe = $('<iframe />')
					.attr('width', '100%')
					.attr('height', '300px')
					.attr('srcdoc', resolved.src);
				// @ts-ignore
				$('.panel[data-message-file="' + messageFile + '"] .form-section[data-content-type="html"]').html($iframe);
			});
	}
}

export const XmMailCatcher = new MailCatcher();