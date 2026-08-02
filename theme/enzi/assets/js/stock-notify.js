(function ($) {
	'use strict';

	const config = window.diakoStockNotify || {};
	const i18n = config.i18n || {};
	const PHONE_MAX_LENGTH = Number(config.phoneMaxLength) || 11;

	const digitMap = {
		'۰': '0', '۱': '1', '۲': '2', '۳': '3', '۴': '4',
		'۵': '5', '۶': '6', '۷': '7', '۸': '8', '۹': '9',
		'٠': '0', '١': '1', '٢': '2', '٣': '3', '٤': '4',
		'٥': '5', '٦': '6', '٧': '7', '٨': '8', '٩': '9',
	};

	function getModal() {
		return document.querySelector('[data-stock-notify-modal]');
	}

	function toWesternDigits(value) {
		return String(value || '').replace(/[۰-۹٠-٩]/g, (char) => digitMap[char] || char);
	}

	function extractPhoneDigits(value) {
		return toWesternDigits(value).replace(/\D+/g, '').slice(0, PHONE_MAX_LENGTH);
	}

	function normalizePhone(value) {
		let digits = extractPhoneDigits(value);

		if (digits.startsWith('98') && digits.length === 12) {
			digits = `0${digits.slice(2)}`;
		} else if (!digits.startsWith('0') && digits.startsWith('9') && digits.length === 10) {
			digits = `0${digits}`;
		}

		return digits.slice(0, PHONE_MAX_LENGTH);
	}

	function getPhoneValidationError(value) {
		const digits = extractPhoneDigits(value);

		if (!digits) {
			return i18n.phoneRequired || 'شماره موبایل را وارد کنید.';
		}

		if (digits.length > PHONE_MAX_LENGTH) {
			return i18n.phoneTooLong || `شماره موبایل نباید بیشتر از ${PHONE_MAX_LENGTH} رقم باشد.`;
		}

		const phone = normalizePhone(digits);

		if (phone.length !== PHONE_MAX_LENGTH) {
			return i18n.phoneLength || `شماره موبایل باید ${PHONE_MAX_LENGTH} رقم باشد.`;
		}

		if (!/^09\d{9}$/.test(phone)) {
			return i18n.invalidPhone || 'شماره موبایل معتبر وارد کنید. (مثال: 09123456789)';
		}

		return '';
	}

	function isValidPhone(value) {
		return getPhoneValidationError(value) === '';
	}

	function lockPhoneInput(input) {
		if (!input || input.readOnly) {
			return;
		}

		const phone = normalizePhone(input.value);

		if (!/^09\d{9}$/.test(phone)) {
			return;
		}

		input.value = phone;
		input.readOnly = true;
		input.setAttribute('aria-readonly', 'true');
		input.classList.add('diako-stock-notify__input--locked');
		input.setAttribute('data-stock-notify-phone-prefilled', '');
	}

	function bindPhoneInput(input) {
		if (!input || input.dataset.stockNotifyPhoneBound === '1') {
			return;
		}

		input.dataset.stockNotifyPhoneBound = '1';
		input.setAttribute('maxlength', String(PHONE_MAX_LENGTH));
		input.setAttribute('inputmode', 'numeric');

		if (input.hasAttribute('data-stock-notify-phone-prefilled') || input.readOnly) {
			lockPhoneInput(input);
		}

		input.addEventListener('keydown', (event) => {
			if (input.readOnly) {
				event.preventDefault();
				return;
			}

			if (event.ctrlKey || event.metaKey || event.altKey) {
				return;
			}

			const allowedKeys = [
				'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
				'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End',
			];

			if (allowedKeys.includes(event.key)) {
				return;
			}

			if (!/^[0-9۰-۹٠-٩]$/.test(event.key)) {
				event.preventDefault();
			}
		});

		input.addEventListener('input', () => {
			if (input.readOnly) {
				return;
			}

			const digits = extractPhoneDigits(input.value);

			if (input.value !== digits) {
				input.value = digits;
			}
		});

		input.addEventListener('paste', () => {
			if (input.readOnly) {
				return;
			}

			window.requestAnimationFrame(() => {
				const digits = extractPhoneDigits(input.value);

				if (input.value !== digits) {
					input.value = digits;
				}

				input.dispatchEvent(new Event('input', { bubbles: true }));
			});
		});
	}

	function setFeedback(form, message, isError) {
		const feedback = form.querySelector('[data-stock-notify-feedback]');

		if (!feedback) {
			return;
		}

		feedback.hidden = !message;
		feedback.textContent = message || '';
		feedback.classList.toggle('is-error', Boolean(isError));
		feedback.classList.toggle('is-success', Boolean(message) && !isError);
	}

	function openModal(trigger) {
		const modal = getModal();

		if (!modal) {
			return;
		}

		const form = modal.querySelector('[data-stock-notify-form]');

		if (!form) {
			return;
		}

		const productId = trigger.getAttribute('data-product-id') || '0';
		const variationId = trigger.getAttribute('data-variation-id') || '0';
		const productName = trigger.getAttribute('data-product-name') || '';

		form.querySelector('[data-stock-notify-product-id]').value = productId;
		form.querySelector('[data-stock-notify-variation-id]').value = variationId;

		const nameEl = modal.querySelector('[data-stock-notify-product-name]');

		if (nameEl) {
			nameEl.textContent = productName;
			nameEl.hidden = !productName;
		}

		setFeedback(form, '', false);

		modal.hidden = false;
		document.body.classList.add('diako-stock-notify-open');

		const phoneInput = form.querySelector('[data-stock-notify-phone]');

		if (phoneInput && !phoneInput.readOnly) {
			window.setTimeout(() => phoneInput.focus(), 0);
		}
	}

	function closeModal() {
		const modal = getModal();

		if (!modal) {
			return;
		}

		modal.hidden = true;
		document.body.classList.remove('diako-stock-notify-open');
	}

	function submitForm(form) {
		const submitBtn = form.querySelector('[data-stock-notify-submit]');
		const phoneInput = form.querySelector('[data-stock-notify-phone]');
		const phone = phoneInput ? normalizePhone(phoneInput.value) : '';
		const validationError = getPhoneValidationError(phoneInput ? phoneInput.value : '');

		if (validationError) {
			setFeedback(form, validationError, true);
			if (phoneInput && !phoneInput.readOnly) {
				phoneInput.focus();
			}
			return;
		}

		if (phoneInput) {
			phoneInput.value = phone;
		}

		const productId = form.querySelector('[data-stock-notify-product-id]')?.value || '0';
		const variationId = form.querySelector('[data-stock-notify-variation-id]')?.value || '0';

		if (submitBtn) {
			submitBtn.disabled = true;
		}

		setFeedback(form, i18n.submitting || 'در حال ثبت…', false);

		const body = new FormData();
		body.append('action', 'diako_stock_notify_subscribe');
		body.append('nonce', config.nonce || '');
		body.append('product_id', productId);
		body.append('variation_id', variationId);
		body.append('phone', phone);

		fetch(config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body,
		})
			.then((response) => response.json())
			.then((payload) => {
				if (payload && payload.success) {
					setFeedback(form, payload.data?.message || '', false);
					if (phoneInput) {
						lockPhoneInput(phoneInput);
					}
					return;
				}

				const message = payload?.data?.message || i18n.error || 'خطایی رخ داد.';
				setFeedback(form, message, true);
			})
			.catch(() => {
				setFeedback(form, i18n.error || 'خطایی رخ داد.', true);
			})
			.finally(() => {
				if (submitBtn) {
					submitBtn.disabled = false;
				}
			});
	}

	function initPhoneInputs() {
		document.querySelectorAll('[data-stock-notify-phone]').forEach((input) => {
			bindPhoneInput(input);
		});
	}

	function initModal() {
		document.addEventListener('click', (event) => {
			const trigger = event.target.closest('[data-stock-notify-trigger]');

			if (trigger) {
				event.preventDefault();
				openModal(trigger);
				return;
			}

			if (event.target.closest('[data-stock-notify-close]')) {
				event.preventDefault();
				closeModal();
			}
		});

		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && document.body.classList.contains('diako-stock-notify-open')) {
				closeModal();
			}
		});

		document.querySelectorAll('[data-stock-notify-form]').forEach((form) => {
			form.addEventListener('submit', (event) => {
				event.preventDefault();
				submitForm(form);
			});
		});
	}

	function initVariableProduct() {
		const variationWrap = document.querySelector('[data-stock-notify-variation-wrap]');
		const variationsForm = document.querySelector('form.variations_form');

		if (!variationWrap || !variationsForm) {
			return;
		}

		const inlineForm = variationWrap.querySelector('[data-stock-notify-form]');

		function setVariationNotifyActive(active) {
			variationWrap.hidden = !active;

			if (!inlineForm) {
				return;
			}

			inlineForm.querySelectorAll('input, button, select, textarea').forEach((field) => {
				field.disabled = !active;
			});
		}

		setVariationNotifyActive(false);

		$(variationsForm).on('show_variation', (event, variation) => {
			if (!variation || !inlineForm) {
				return;
			}

			if (variation.is_in_stock) {
				setVariationNotifyActive(false);
				return;
			}

			const productIdInput = inlineForm.querySelector('[data-stock-notify-product-id]');
			const variationIdInput = inlineForm.querySelector('[data-stock-notify-variation-id]');

			if (productIdInput) {
				productIdInput.value = String(variation.product_id || variationsForm.getAttribute('data-product_id') || '0');
			}

			if (variationIdInput) {
				variationIdInput.value = String(variation.variation_id || '0');
			}

			setVariationNotifyActive(true);
		});

		$(variationsForm).on('hide_variation', () => {
			setVariationNotifyActive(false);
		});
	}

	function initModalState() {
		const modal = getModal();

		if (!modal) {
			return;
		}

		modal.hidden = true;
		document.body.classList.remove('diako-stock-notify-open');
	}

	function init() {
		initModalState();
		initPhoneInputs();
		initModal();
		initVariableProduct();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(jQuery);
