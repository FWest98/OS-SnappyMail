
(function () {

	'use strict';

	var
		_ = require('_'),
		ko = require('ko'),

		Enums = require('Common/Enums'),
		Utils = require('Common/Utils'),
		Consts = require('Common/Consts'),
		Translator = require('Common/Translator'),

		Settings = require('Storage/Settings'),
		Remote = require('Storage/Admin/Remote'),

		AppStore = require('Stores/Admin/App'),
		LicenseStore = require('Stores/Admin/License'),

		kn = require('Knoin/Knoin'),
		AbstractView = require('Knoin/AbstractView')
	;

	/**
	 * @constructor
	 * @extends AbstractView
	 */
	function ActivatePopupView()
	{
		AbstractView.call(this, 'Popups', 'PopupsActivate');

		var self = this;

		this.domain = ko.observable('');
		this.key = ko.observable('');
		this.key.focus = ko.observable(false);
		this.activationSuccessed = ko.observable(false);

		this.licenseTrigger = LicenseStore.licenseTrigger;

		this.activateProcess = ko.observable(false);
		this.activateText = ko.observable('');
		this.activateText.isError = ko.observable(false);

		this.key.subscribe(function () {
			this.activateText('');
			this.activateText.isError(false);
		}, this);

		this.activationSuccessed.subscribe(function (bValue) {
			if (bValue)
			{
				this.licenseTrigger(!this.licenseTrigger());
			}
		}, this);

		this.activateCommand = Utils.createCommand(this, function () {

			this.activateProcess(true);
			if (this.validateSubscriptionKey())
			{
				Remote.licensingActivate(function (sResult, oData) {

					self.activateProcess(false);
					if (Enums.StorageResultType.Success === sResult && oData.Result)
					{
						if (true === oData.Result)
						{
							self.activationSuccessed(true);
							self.activateText('Subscription Key Activated Successfully');
							self.activateText.isError(false);
						}
						else
						{
							self.activateText(oData.Result);
							self.activateText.isError(true);
							self.key.focus(true);
						}
					}
					else if (oData.ErrorCode)
					{
						self.activateText(Translator.getNotification(oData.ErrorCode));
						self.activateText.isError(true);
						self.key.focus(true);
					}
					else
					{
						self.activateText(Translator.getNotification(Enums.Notification.UnknownError));
						self.activateText.isError(true);
						self.key.focus(true);
					}

				}, this.domain(), this.key());
			}
			else
			{
				this.activateProcess(false);
				this.activateText('Invalid Subscription Key');
				this.activateText.isError(true);
				this.key.focus(true);
			}

		}, function () {
			return !this.activateProcess() && '' !== this.domain() && '' !== this.key() && !this.activationSuccessed();
		});

		kn.constructorEnd(this);
	}

	kn.extendAsViewModel(['View/Popup/Activate', 'PopupsActivateViewModel'], ActivatePopupView);
	_.extend(ActivatePopupView.prototype, AbstractView.prototype);

	ActivatePopupView.prototype.onShow = function (bTrial)
	{
		this.domain(Settings.settingsGet('AdminDomain'));
		if (!this.activateProcess())
		{
			bTrial = Utils.isUnd(bTrial) ? false : !!bTrial;

			this.key(bTrial ? Consts.Values.RainLoopTrialKey : '');
			this.activateText('');
			this.activateText.isError(false);
			this.activationSuccessed(false);
		}
	};

	ActivatePopupView.prototype.onFocus = function ()
	{
		if (!this.activateProcess())
		{
			this.key.focus(true);
		}
	};

	/**
	 * @returns {boolean}
	 */
	ActivatePopupView.prototype.validateSubscriptionKey = function ()
	{
		var sValue = this.key();
		return '' === sValue || Consts.Values.RainLoopTrialKey === sValue ||
			!!/^RL[\d]+-[A-Z0-9\-]+Z$/.test(Utils.trim(sValue));
	};

	module.exports = ActivatePopupView;

}());