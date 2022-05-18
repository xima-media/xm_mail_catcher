import $ = require('jquery');

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
		$('.form-section[data-m-id="' + mId + '"][data-content-type="' + contentType + '"]').removeClass('hidden');
	}
}

export const XmMailCatcher = new MailCatcher();