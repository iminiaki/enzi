(function () {
	const fetchRecaptchaToken = async (action) => {
		const config = window.diakoRecaptcha;

		if (!config?.enabled || !config.siteKey) {
			return '';
		}

		if (typeof grecaptcha === 'undefined') {
			throw new Error('recaptcha_unavailable');
		}

		await new Promise((resolve) => {
			grecaptcha.ready(resolve);
		});

		return grecaptcha.execute(config.siteKey, { action });
	};

	const toPersianDigits = (value) => {
		if (window.diakoLocale && window.diakoLocale.usePersianDigits === false) {
			return String(value);
		}

		return String(value).replace(/[0-9]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);
	};

	const applyTheme = (theme) => {
		const root = document.documentElement;
		const isDark = theme === 'dark';

		root.classList.remove('dark');
		if (isDark) {
			root.classList.add('dark');
		}

		root.style.colorScheme = theme;
		root.dataset.theme = theme;

		document.querySelectorAll('.theme-icon-sun').forEach((icon) => {
			icon.classList.toggle('hidden', !isDark);
		});

		document.querySelectorAll('.theme-icon-moon').forEach((icon) => {
			icon.classList.toggle('hidden', isDark);
		});

		try {
			localStorage.setItem('diako-theme', theme);
		} catch (e) {}

		window.dispatchEvent(new CustomEvent('diako-theme-change', { detail: { theme } }));
	};

	const initTheme = () => {
		try {
			const stored = localStorage.getItem('diako-theme');
			const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
			applyTheme(stored || (prefersDark ? 'dark' : 'light'));
		} catch (e) {}
	};

	document.addEventListener('DOMContentLoaded', () => {
		initTheme();
		initHomeHeaderScroll();

		const navToggle = document.querySelector('[data-nav-toggle]');
		const navClose = document.querySelector('[data-nav-close]');
		const mobileNav = document.querySelector('[data-mobile-nav]');
		const mobileNavBackdrop = document.querySelector('[data-mobile-nav-backdrop]');
		const navIconOpen = navToggle ? navToggle.querySelector('.diako-nav-icon-open') : null;
		const navIconClose = navToggle ? navToggle.querySelector('.diako-nav-icon-close') : null;
		const desktopNavQuery = window.matchMedia('(min-width: 1024px)');

		const setNavOpen = (isOpen) => {
			if (!mobileNav || !mobileNavBackdrop || !navToggle) {
				return;
			}

			mobileNav.classList.toggle('is-open', isOpen);
			mobileNavBackdrop.classList.toggle('is-open', isOpen);
			document.body.classList.toggle('diako-mobile-nav-open', isOpen);

			mobileNav.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
			mobileNavBackdrop.hidden = !isOpen;
			mobileNavBackdrop.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
			navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			navToggle.setAttribute(
				'aria-label',
				isOpen
					? navToggle.dataset.closeLabel || 'بستن منو'
					: navToggle.dataset.openLabel || 'باز کردن منو'
			);

			if (navIconOpen && navIconClose) {
				navIconOpen.classList.toggle('hidden', isOpen);
				navIconClose.classList.toggle('hidden', !isOpen);
			}
		};

		let blockBackdropClickUntil = 0;

		const closeNav = () => setNavOpen(false);
		const openNav = () => {
			document.dispatchEvent(new CustomEvent('diako:close-header-search'));
			blockBackdropClickUntil = Date.now() + 450;
			setNavOpen(true);
		};
		const toggleNav = () => {
			if (mobileNav && mobileNav.classList.contains('is-open')) {
				closeNav();
				return;
			}
			openNav();
		};

		if (navToggle && mobileNav && mobileNavBackdrop) {
			navToggle.dataset.openLabel = navToggle.getAttribute('aria-label') || 'باز کردن منو';
			navToggle.dataset.closeLabel = 'بستن منو';

			let suppressToggleClick = false;

			navToggle.addEventListener('pointerup', (event) => {
				if (event.pointerType === 'mouse' && event.button !== 0) {
					return;
				}

				event.preventDefault();
				suppressToggleClick = true;
				toggleNav();
				window.setTimeout(() => {
					suppressToggleClick = false;
				}, 450);
			});

			navToggle.addEventListener('click', (event) => {
				if (suppressToggleClick) {
					event.preventDefault();
					return;
				}

				toggleNav();
			});

			navClose?.addEventListener('click', closeNav);
			document.addEventListener('diako:close-mobile-nav', closeNav);
			mobileNavBackdrop.addEventListener('click', (event) => {
				if (Date.now() < blockBackdropClickUntil) {
					event.preventDefault();
					event.stopPropagation();
					return;
				}

				closeNav();
			});

			mobileNav.querySelectorAll('.menu-item-has-children').forEach((item) => {
				const link = item.querySelector(':scope > .nav-link, :scope > a');
				const submenu = item.querySelector(':scope > .sub-menu');

				if (!link || !submenu) {
					return;
				}

				link.addEventListener('click', (event) => {
					if (window.matchMedia('(min-width: 1024px)').matches) {
						return;
					}

					event.preventDefault();
					item.classList.toggle('is-open');
					link.setAttribute('aria-expanded', item.classList.contains('is-open') ? 'true' : 'false');
				});
			});

			mobileNav.querySelectorAll('a').forEach((link) => {
				link.addEventListener('click', (event) => {
					if (link.closest('.menu-item-has-children') === link.parentElement && link.parentElement.querySelector(':scope > .sub-menu')) {
						return;
					}
					closeNav();
				});
			});

			document.addEventListener('keydown', (event) => {
				if (event.key === 'Escape') {
					closeNav();
				}
			});

			desktopNavQuery.addEventListener('change', (event) => {
				if (event.matches) {
					closeNav();
				}
			});
		}

		document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
			button.addEventListener('click', () => {
				const nextTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
				applyTheme(nextTheme);
			});
		});

		window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
			if (localStorage.getItem('diako-theme')) {
				return;
			}
			applyTheme(event.matches ? 'dark' : 'light');
		});

		initProductTabs();
		initProductGallery();
		initReviewStarRating();
		initShopSidebarDrawer();
		initShopFilterToggles();
		initPriceFilterSlider();
		window.setTimeout(initPriceFilterSlider, 0);
		initShopAjaxFilters();
		initSaleCountdowns();
		initSingleProductSaleCountdown();
		initVariableProductForm();

		if (window.jQuery) {
			window.jQuery(document.body).on(
				'price_slider_create price_slider_slide price_slider_updated',
				normalizePriceFilterDigits
			);
			window.setTimeout(normalizePriceFilterDigits, 50);
		}
		initWooCommerceToasts();
		initCartAutoUpdate();
		initCheckoutCouponForm();
		initCheckoutLayout();
		initHeaderSearch();
		initFavoriteButtons();
		initCompareProducts();
		initProductCardCart();
	});

	const initFavoriteButtons = () => {
		const config = window.diakoFavorites;

		if (!config) {
			return;
		}

		const i18n = config.i18n || {};

		const setProductFavoriteState = (productId, isActive) => {
			document.querySelectorAll(`[data-favorite-toggle][data-product-id="${productId}"]`).forEach((button) => {
				button.classList.toggle('is-active', isActive);
				button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
				button.setAttribute('aria-label', isActive ? i18n.remove || 'حذف از علاقه‌مندی‌ها' : i18n.add || 'افزودن به علاقه‌مندی‌ها');
			});
		};

		document.addEventListener('click', (event) => {
			const button = event.target.closest('[data-favorite-toggle]');

			if (!button) {
				return;
			}

			event.preventDefault();
			event.stopPropagation();

			if (!config.loggedIn) {
				window.location.assign(config.loginUrl || '/my-account/');
				return;
			}

			if (button.disabled) {
				return;
			}

			const productId = button.getAttribute('data-product-id');

			if (!productId) {
				return;
			}

			button.disabled = true;

			const body = new FormData();
			body.append('action', 'diako_toggle_favorite_product');
			body.append('nonce', config.nonce || '');
			body.append('product_id', productId);

			fetch(config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body,
			})
				.then((response) => response.json())
				.then((payload) => {
					if (!payload || !payload.success) {
						const redirect = payload?.data?.redirect;

						if (redirect) {
							window.location.assign(redirect);
						}

						return;
					}

					setProductFavoriteState(productId, Boolean(payload.data?.active));
				})
				.catch(() => {})
				.finally(() => {
					button.disabled = false;
				});
		});
	};

	const showCardCartToast = (message, type = 'message', cartUrl = '') => {
		const notice = document.createElement('div');
		notice.className = type === 'error' ? 'woocommerce-error' : 'woocommerce-message';
		notice.setAttribute('role', 'status');

		const text = document.createElement('p');
		text.textContent = message;
		notice.appendChild(text);

		if (cartUrl && type !== 'error') {
			const link = document.createElement('a');
			link.className = 'button wc-forward';
			link.href = cartUrl;
			link.textContent = window.diakoCardCart?.i18n?.viewCart || 'مشاهده سبد';
			notice.appendChild(link);
		}

		document.body.appendChild(notice);
	};

	const applyCartFragments = (fragments) => {
		if (!fragments || typeof fragments !== 'object') {
			return;
		}

		Object.entries(fragments).forEach(([selector, html]) => {
			const targets = document.querySelectorAll(selector);

			if (!targets.length) {
				if (selector.includes('data-diako-cart-count')) {
					const cartLink = document.querySelector('[data-diako-cart-link]');
					if (cartLink) {
						cartLink.insertAdjacentHTML('beforeend', html);
					}
				}
				return;
			}

			targets.forEach((target) => {
				const template = document.createElement('template');
				template.innerHTML = String(html).trim();
				const next = template.content.firstElementChild;

				if (next) {
					target.replaceWith(next);
				}
			});
		});
	};

	const initProductCardCart = () => {
		const config = window.diakoCardCart;

		if (!config) {
			return;
		}

		const i18n = config.i18n || {};
		const modal = document.querySelector('[data-diako-variation-modal]');
		const modalBody = document.querySelector('[data-diako-variation-modal-body]');
		let activeRequest = null;

		const setButtonBusy = (button, busy) => {
			if (!button) {
				return;
			}

			button.disabled = busy;
			button.classList.toggle('is-loading', busy);
			button.setAttribute('aria-busy', busy ? 'true' : 'false');
		};

		const closeModal = () => {
			if (!modal) {
				return;
			}

			modal.hidden = true;
			document.body.classList.remove('diako-variation-modal-open');

			if (modalBody) {
				modalBody.innerHTML = '';
			}
		};

		const normalizeValue = (value) => {
			if (value === undefined || value === null) {
				return '';
			}

			const stringValue = String(value);

			try {
				return decodeURIComponent(stringValue.replace(/\+/g, ' '));
			} catch (error) {
				return stringValue;
			}
		};

		const bindVariationModalForm = (form) => {
			const submitButton = form.querySelector('[data-diako-variation-modal-submit]');
			const variationIdInput = form.querySelector('.variation_id');
			const priceBlock = form.querySelector('[data-diako-variation-modal-price]');
			const defaultPriceHtml = priceBlock?.dataset.diakoDefaultPriceHtml || priceBlock?.innerHTML || '';
			const feedback = form.querySelector('[data-diako-variation-modal-feedback]');

			const getVariations = () => {
				const raw = form.getAttribute('data-product_variations');

				if (!raw || raw === 'false') {
					return [];
				}

				try {
					const parsed = JSON.parse(raw);
					return Array.isArray(parsed) ? parsed : [];
				} catch (error) {
					return [];
				}
			};

			const getAttributeFields = () => [
				...form.querySelectorAll('select[name^="attribute_"], input[name^="attribute_"]:not([type="radio"])'),
			];

			const getSelectedAttributes = () => {
				const selected = {};

				getAttributeFields().forEach((field) => {
					const attributeName = field.getAttribute('data-attribute_name') || field.getAttribute('name');

					if (attributeName && field.value) {
						selected[attributeName] = field.value;
					}
				});

				return selected;
			};

			const allAttributesChosen = () => {
				const fields = getAttributeFields();
				return fields.length > 0 && fields.every((field) => field.value);
			};

			const getMatchingVariations = (variations, selected) =>
				variations.filter((variation) =>
					Object.entries(variation.attributes || {}).every(([key, value]) => {
						if (!value) {
							return true;
						}

						return !selected[key] || normalizeValue(selected[key]) === normalizeValue(value);
					})
				);

			const findMatchingVariation = () => {
				const variations = getVariations();
				const fields = getAttributeFields();

				if (!fields.length || !variations.length || !allAttributesChosen()) {
					return null;
				}

				const selected = getSelectedAttributes();
				const matches = getMatchingVariations(variations, selected);

				return matches.length === 1 ? matches[0] : null;
			};

			const setButtonNeedsSelection = (needsSelection) => {
				if (!submitButton) {
					return;
				}

				submitButton.disabled = needsSelection;
				submitButton.classList.toggle('disabled', needsSelection);
			};

			const updatePriceDisplay = (priceHtml = '') => {
				if (!priceBlock) {
					return;
				}

				priceBlock.innerHTML = priceHtml || defaultPriceHtml;
			};

			const syncVariationState = () => {
				const variation = findMatchingVariation();

				if (variation && variationIdInput) {
					variationIdInput.value = String(variation.variation_id || 0);
					const canPurchase = Boolean(variation.is_purchasable && variation.is_in_stock);
					setButtonNeedsSelection(!canPurchase);
					updatePriceDisplay(variation.price_html || '');

					if (feedback) {
						feedback.hidden = true;
						feedback.textContent = '';
					}

					return;
				}

				if (variationIdInput) {
					variationIdInput.value = '0';
				}

				setButtonNeedsSelection(true);

				const selected = getSelectedAttributes();
				const matches = getMatchingVariations(getVariations(), selected);
				const priceHtml = matches.length
					? matches.reduce((lowest, current) =>
							parseFloat(current.display_price) < parseFloat(lowest.display_price) ? current : lowest
						).price_html || ''
					: '';

				updatePriceDisplay(priceHtml);
			};

			form.querySelectorAll('[data-diako-variation-radio]').forEach((radio) => {
				radio.addEventListener('change', () => {
					if (!radio.checked) {
						return;
					}

					const selectName = radio.dataset.targetSelect;
					const select = selectName ? form.querySelector(`select[name="${selectName}"]`) : null;

					if (select) {
						select.value = radio.value;
					}

					const group = radio.closest('[data-diako-variation-radios]');
					group?.querySelectorAll('.diako-variation-option').forEach((option) => {
						option.classList.toggle('is-selected', option.contains(radio) && radio.checked);
					});

					syncVariationState();
				});
			});

			form.addEventListener('submit', (event) => {
				event.preventDefault();

				const variation = findMatchingVariation();

				if (!variation) {
					if (feedback) {
						feedback.hidden = false;
						feedback.textContent = i18n.selectVariation || 'لطفاً یک گزینه را انتخاب کنید.';
						feedback.classList.add('is-error');
					}
					return;
				}

				const productId = form.getAttribute('data-product_id') || form.querySelector('[name="product_id"]')?.value;
				const formData = new FormData();
				formData.append('action', 'diako_card_add_to_cart');
				formData.append('nonce', config.nonce || '');
				formData.append('product_id', String(productId || ''));
				formData.append('variation_id', String(variation.variation_id || 0));
				formData.append('quantity', '1');

				Object.entries(getSelectedAttributes()).forEach(([key, value]) => {
					formData.append(key, value);
				});

				setButtonBusy(submitButton, true);

				fetch(config.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					body: formData,
				})
					.then((response) => response.json())
					.then((payload) => {
						if (!payload?.success) {
							const message = payload?.data?.message || i18n.error || 'خطایی رخ داد.';
							if (feedback) {
								feedback.hidden = false;
								feedback.textContent = message;
								feedback.classList.add('is-error');
							}
							showCardCartToast(message, 'error');
							return;
						}

						applyCartFragments(payload.data?.fragments);
						showCardCartToast(
							payload.data?.message || i18n.added || 'به سبد خرید اضافه شد.',
							'message',
							payload.data?.cartUrl || config.cartUrl || ''
						);
						closeModal();
					})
					.catch(() => {
						const message = i18n.error || 'خطایی رخ داد.';
						if (feedback) {
							feedback.hidden = false;
							feedback.textContent = message;
							feedback.classList.add('is-error');
						}
						showCardCartToast(message, 'error');
					})
					.finally(() => {
						setButtonBusy(submitButton, false);
						syncVariationState();
					});
			});

			syncVariationState();
		};

		const openVariationModal = (productId) => {
			if (!modal || !modalBody || !productId) {
				return;
			}

			modal.hidden = false;
			document.body.classList.add('diako-variation-modal-open');
			modalBody.innerHTML = `<p class="diako-variation-modal__status">${i18n.loading || 'در حال بارگذاری…'}</p>`;

			const formData = new FormData();
			formData.append('action', 'diako_get_variation_modal');
			formData.append('nonce', config.nonce || '');
			formData.append('product_id', String(productId));

			if (activeRequest) {
				activeRequest.abort();
			}

			const controller = new AbortController();
			activeRequest = controller;

			fetch(config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData,
				signal: controller.signal,
			})
				.then((response) => response.json())
				.then((payload) => {
					if (!payload?.success) {
						modalBody.innerHTML = `<p class="diako-variation-modal__status is-error">${payload?.data?.message || i18n.error || 'خطایی رخ داد.'}</p>`;
						return;
					}

					modalBody.innerHTML = payload.data?.html || '';
					const form = modalBody.querySelector('[data-diako-variation-modal-form]');

					if (form) {
						bindVariationModalForm(form);
					}
				})
				.catch((error) => {
					if (error?.name === 'AbortError') {
						return;
					}

					modalBody.innerHTML = `<p class="diako-variation-modal__status is-error">${i18n.error || 'خطایی رخ داد.'}</p>`;
				})
				.finally(() => {
					if (activeRequest === controller) {
						activeRequest = null;
					}
				});
		};

		const addSimpleProduct = (button) => {
			const productId = button.getAttribute('data-product-id');

			if (!productId) {
				return;
			}

			const formData = new FormData();
			formData.append('action', 'diako_card_add_to_cart');
			formData.append('nonce', config.nonce || '');
			formData.append('product_id', productId);
			formData.append('quantity', '1');

			setButtonBusy(button, true);

			fetch(config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData,
			})
				.then((response) => response.json())
				.then((payload) => {
					if (!payload?.success) {
						showCardCartToast(payload?.data?.message || i18n.error || 'خطایی رخ داد.', 'error');
						return;
					}

					applyCartFragments(payload.data?.fragments);
					showCardCartToast(
						payload.data?.message || i18n.added || 'به سبد خرید اضافه شد.',
						'message',
						payload.data?.cartUrl || config.cartUrl || ''
					);
				})
				.catch(() => {
					showCardCartToast(i18n.error || 'خطایی رخ داد.', 'error');
				})
				.finally(() => {
					setButtonBusy(button, false);
				});
		};

		document.addEventListener('click', (event) => {
			const button = event.target.closest('[data-diako-card-atc]');

			if (button) {
				event.preventDefault();
				event.stopPropagation();

				const productType = button.getAttribute('data-product-type') || 'simple';

				if (productType === 'variable') {
					openVariationModal(button.getAttribute('data-product-id'));
				} else {
					addSimpleProduct(button);
				}

				return;
			}

			if (event.target.closest('[data-diako-variation-modal-close]')) {
				event.preventDefault();
				closeModal();
			}
		});

		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && modal && !modal.hidden) {
				closeModal();
			}
		});
	};

	const initCompareProducts = () => {
		const config = window.diakoCompare;

		if (!config) {
			return;
		}

		const i18n = config.i18n || {};
		const storageKey = 'diakoCompareProducts';
		const maxItems = Number(config.max) || 4;
		const bar = document.querySelector('[data-compare-bar]');
		const countEl = document.querySelector('[data-compare-count]');
		const modal = document.querySelector('[data-compare-modal]');
		const bodyEl = document.querySelector('[data-compare-body]');

		const readItems = () => {
			try {
				const items = JSON.parse(localStorage.getItem(storageKey) || '[]');
				return Array.isArray(items) ? items.map(String).filter(Boolean).slice(0, maxItems) : [];
			} catch (error) {
				return [];
			}
		};

		const writeItems = (items) => {
			const normalized = [...new Set(items.map(String).filter(Boolean))].slice(0, maxItems);
			localStorage.setItem(storageKey, JSON.stringify(normalized));
			return normalized;
		};

		const updateButtons = () => {
			const items = readItems();

			document.querySelectorAll('[data-compare-toggle]').forEach((button) => {
				const active = items.includes(String(button.getAttribute('data-product-id') || ''));
				button.classList.toggle('is-active', active);
				button.setAttribute('aria-pressed', active ? 'true' : 'false');
				button.setAttribute('aria-label', active ? i18n.remove || 'حذف از مقایسه' : i18n.add || 'افزودن به مقایسه');
			});

			if (bar) {
				bar.hidden = items.length === 0;
			}

			if (countEl) {
				countEl.textContent = toPersianDigits(items.length);
			}
		};

		const closeModal = () => {
			if (!modal) {
				return;
			}

			modal.hidden = true;
			document.body.classList.remove('diako-compare-open');
		};

		const openModal = () => {
			const items = readItems();

			if (!modal || !bodyEl || !items.length) {
				return;
			}

			modal.hidden = false;
			document.body.classList.add('diako-compare-open');
			bodyEl.innerHTML = `<p class="diako-compare-modal__status">${i18n.loading || 'در حال ساخت مقایسه…'}</p>`;

			const formData = new FormData();
			formData.append('action', 'diako_render_compare_products');
			formData.append('nonce', config.nonce || '');
			items.forEach((id) => formData.append('ids[]', id));

			fetch(config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData,
			})
				.then((response) => response.json())
				.then((payload) => {
					if (!payload || !payload.success) {
						bodyEl.innerHTML = `<p class="diako-compare-modal__status is-error">${payload?.data?.message || i18n.error || 'خطایی رخ داد.'}</p>`;
						return;
					}

					bodyEl.innerHTML = payload.data?.html || '';
				})
				.catch(() => {
					bodyEl.innerHTML = `<p class="diako-compare-modal__status is-error">${i18n.error || 'خطایی رخ داد.'}</p>`;
				});
		};

		document.addEventListener('click', (event) => {
			const toggle = event.target.closest('[data-compare-toggle]');

			if (toggle) {
				event.preventDefault();
				event.stopPropagation();

				const productId = String(toggle.getAttribute('data-product-id') || '');
				let items = readItems();

				if (!productId) {
					return;
				}

				if (items.includes(productId)) {
					items = items.filter((id) => id !== productId);
				} else {
					if (items.length >= maxItems) {
						items.shift();
					}
					items.push(productId);
				}

				writeItems(items);
				updateButtons();
				return;
			}

			const removeButton = event.target.closest('[data-compare-remove]');
			if (removeButton) {
				event.preventDefault();
				const productId = String(removeButton.getAttribute('data-compare-remove') || '');
				writeItems(readItems().filter((id) => id !== productId));
				updateButtons();

				if (readItems().length) {
					openModal();
				} else {
					closeModal();
				}
				return;
			}

			if (event.target.closest('[data-compare-open]')) {
				event.preventDefault();
				openModal();
				return;
			}

			if (event.target.closest('[data-compare-clear]')) {
				event.preventDefault();
				writeItems([]);
				updateButtons();
				closeModal();
				return;
			}

			if (event.target.closest('[data-compare-close]')) {
				event.preventDefault();
				closeModal();
			}
		});

		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape') {
				closeModal();
			}
		});

		updateButtons();
	};

	const initHeaderSearch = () => {
		const config = window.diakoSearch;
		const toggle = document.querySelector('[data-header-search-toggle]');
		const overlay = document.querySelector('[data-header-search-overlay]');
		const panel = document.querySelector('[data-header-search-panel]');
		const backdrop = document.querySelector('[data-header-search-backdrop]');
		const closeButtons = document.querySelectorAll('[data-header-search-close]');
		const input = document.querySelector('[data-header-search-input]');
		const results = document.querySelector('[data-header-search-results]');
		const form = document.querySelector('[data-header-search-form]');

		if (!config || !toggle || !overlay || !panel || !input || !results) {
			return;
		}

		const i18n = config.i18n || {};
		const minChars = Number(config.minChars) || 2;
		let debounceTimer = null;
		let activeRequest = null;

		const escapeHtml = (value) =>
			String(value)
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#39;');

		const setResultsContent = (html, show = true) => {
			results.innerHTML = html;
			results.hidden = !show;
		};

		const focusSearchInput = () => {
			if (overlay.hidden || !input) {
				return;
			}

			try {
				input.focus({ preventScroll: true });
			} catch (error) {
				input.focus();
			}
		};

		const setSearchOpen = (isOpen) => {
			overlay.hidden = !isOpen;

			document.body.classList.toggle('diako-header-search-open', isOpen);
			toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			toggle.setAttribute(
				'aria-label',
				isOpen ? i18n.closeLabel || 'بستن جستجو' : i18n.openLabel || 'جستجو در محصولات'
			);

			if (isOpen) {
				document.dispatchEvent(new CustomEvent('diako:close-mobile-nav'));
				return;
			}

			if (activeRequest) {
				activeRequest.abort();
				activeRequest = null;
			}

			input.value = '';
			setResultsContent('', false);
		};

		const closeSearch = () => setSearchOpen(false);

		const openSearch = () => {
			setSearchOpen(true);
			focusSearchInput();
			requestAnimationFrame(() => {
				focusSearchInput();
			});
		};

		document.addEventListener('diako:close-header-search', closeSearch);

		const renderProducts = (products, total, viewAllUrl) => {
			if (!products.length) {
				setResultsContent(
					`<p class="diako-header-search__status">${i18n.noResults || 'محصولی یافت نشد.'}</p>`
				);
				return;
			}

			const items = products
				.map((product) => {
					const image = product.image
						? `<img class="diako-header-search__thumb" src="${product.image}" alt="${escapeHtml(product.name)}" loading="lazy" decoding="async" />`
						: '<span class="diako-header-search__thumb" aria-hidden="true"></span>';

					return `<li class="diako-header-search__item">
						<a class="diako-header-search__item-link" href="${escapeHtml(product.url)}">
							${image}
							<span class="diako-header-search__meta">
								<span class="diako-header-search__name">${escapeHtml(product.name)}</span>
								<span class="diako-header-search__price">${product.price_html}</span>
							</span>
						</a>
					</li>`;
				})
				.join('');

			const viewAllLabel = i18n.viewAll || 'مشاهده همه نتایج';
			const viewAllText =
				total > 0
					? `${viewAllLabel} (${toPersianDigits(total)})`
					: viewAllLabel;
			const viewAll =
				viewAllUrl && total > products.length
					? `<div class="diako-header-search__footer">
						<a class="diako-header-search__view-all" href="${viewAllUrl}">
							${escapeHtml(viewAllText)}
						</a>
					</div>`
					: '';

			setResultsContent(
				`<ul class="diako-header-search__list" role="list">${items}</ul>${viewAll}`
			);
		};

		const fetchResults = (term) => {
			if (activeRequest) {
				activeRequest.abort();
			}

			if (term.length < minChars) {
				setResultsContent(
					`<p class="diako-header-search__status">${i18n.typeMore || 'حداقل ۲ حرف وارد کنید.'}</p>`,
					term.length > 0
				);
				return;
			}

			setResultsContent(
				`<p class="diako-header-search__status">${i18n.searching || 'در حال جستجو…'}</p>`
			);

			const url = new URL(config.ajaxUrl);
			url.searchParams.set('action', 'diako_product_search');
			url.searchParams.set('nonce', config.nonce);
			url.searchParams.set('term', term);

			activeRequest = new AbortController();

			fetch(url.toString(), {
				method: 'GET',
				credentials: 'same-origin',
				signal: activeRequest.signal,
			})
				.then((response) => response.json())
				.then((payload) => {
					activeRequest = null;

					if (!payload || !payload.success) {
						setResultsContent(
							`<p class="diako-header-search__status">${i18n.noResults || 'محصولی یافت نشد.'}</p>`
						);
						return;
					}

					const data = payload.data || {};
					renderProducts(data.products || [], Number(data.total) || 0, data.view_all_url || '');
				})
				.catch((error) => {
					if (error.name === 'AbortError') {
						return;
					}

					activeRequest = null;
					setResultsContent(
						`<p class="diako-header-search__status">${i18n.noResults || 'محصولی یافت نشد.'}</p>`
					);
				});
		};

		const queueSearch = () => {
			window.clearTimeout(debounceTimer);
			debounceTimer = window.setTimeout(() => {
				fetchResults(input.value.trim());
			}, 300);
		};

		const submitSearch = () => {
			window.clearTimeout(debounceTimer);

			const term = input.value.trim();

			if (term.length < minChars) {
				fetchResults(term);
				return;
			}

			const searchUrl = new URL(form.getAttribute('action') || '/', window.location.origin);
			searchUrl.searchParams.set('s', term);
			searchUrl.searchParams.set('post_type', 'product');
			window.location.assign(searchUrl.toString());
		};

		toggle.addEventListener('click', (event) => {
			if (overlay.hidden) {
				event.preventDefault();
				openSearch();
				return;
			}

			closeSearch();
		});

		closeButtons.forEach((button) => {
			button.addEventListener('click', closeSearch);
		});

		backdrop?.addEventListener('click', closeSearch);

		input.addEventListener('input', queueSearch);

		form?.addEventListener('submit', (event) => {
			event.preventDefault();
			submitSearch();
		});

		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && !overlay.hidden) {
				closeSearch();
			}
		});
	};

	const initHomeHeaderScroll = () => {
		const header = document.querySelector('.site-header--overlay');
		if (!header) {
			return;
		}

		const updateHeaderState = () => {
			header.classList.toggle('is-scrolled', window.scrollY > 16);
		};

		updateHeaderState();
		window.addEventListener('scroll', updateHeaderState, { passive: true });
	};

	const initShopFilterToggles = () => {
		const config = window.diakoShopFilters;
		const filterRoot = document.querySelector('.diako-shop-filters');

		if (!config || !filterRoot) {
			return;
		}

		const visibleCount = Number(config.visibleCount) > 0 ? Number(config.visibleCount) : 3;
		const lists = filterRoot.querySelectorAll(
			'.diako-shop-filter__list, .woocommerce-widget-layered-nav-list, .widget_layered_nav > ul'
		);

		const isActiveItem = (item) =>
			item.classList.contains('is-active') ||
			item.classList.contains('chosen') ||
			item.classList.contains('woocommerce-widget-layered-nav-list__item--chosen');

		lists.forEach((list) => {
			if (list.closest('.widget_layered_nav_filters') || list.dataset.filterToggleInit === 'true') {
				return;
			}

			const items = Array.from(list.children).filter((child) => child.tagName === 'LI');
			if (items.length <= visibleCount) {
				return;
			}

			list.dataset.filterToggleInit = 'true';
			list.classList.add('diako-shop-filter__list--collapsible');

			const hiddenCount = items.length - visibleCount;
			const hasActiveHidden = items.slice(visibleCount).some(isActiveItem);
			const isExpanded = hasActiveHidden;

			if (isExpanded) {
				list.classList.add('is-expanded');
			}

			items.forEach((item, index) => {
				if (index >= visibleCount) {
					item.classList.add('diako-shop-filter__item--extra');
				}
			});

			const toggle = document.createElement('button');
			toggle.type = 'button';
			toggle.className = 'diako-shop-filter__toggle';
			toggle.dataset.shopFilterToggle = '';
			toggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');

			const updateLabel = (expanded) => {
				toggle.textContent = expanded
					? config.i18n.showLess
					: config.i18n.showMore.replace('%d', toPersianDigits(hiddenCount));
			};

			updateLabel(isExpanded);

			toggle.addEventListener('click', () => {
				const expanded = toggle.getAttribute('aria-expanded') === 'true';
				const nextExpanded = !expanded;

				toggle.setAttribute('aria-expanded', nextExpanded ? 'true' : 'false');
				list.classList.toggle('is-expanded', nextExpanded);
				updateLabel(nextExpanded);
			});

			list.after(toggle);
		});
	};

	const priceSliderHandleObservers = new WeakSet();

	const normalizePriceFilterDigits = () => {
		document
			.querySelectorAll('.widget_price_filter .price_label .from, .widget_price_filter .price_label .to')
			.forEach((node) => {
				if (!node.textContent) {
					return;
				}

				node.textContent = toPersianDigits(node.textContent);
			});
	};

	const initPriceFilterSlider = () => {
		if (!window.jQuery || typeof window.woocommerce_price_slider_params === 'undefined') {
			return;
		}

		window.jQuery(document.body).trigger('init_price_filter');
		normalizePriceSliderHandles();
		normalizePriceFilterDigits();
	};

	const normalizePriceSliderHandles = () => {
		document.querySelectorAll('.widget_price_filter .ui-slider-handle').forEach((handle) => {
			const inlineLeft = (handle.style.left || '').replace(/\s/g, '');

			if (inlineLeft === '100%') {
				handle.classList.add('diako-price-slider-handle--max');
			} else {
				handle.classList.remove('diako-price-slider-handle--max');
			}

			if (priceSliderHandleObservers.has(handle)) {
				return;
			}

			priceSliderHandleObservers.add(handle);

			const observer = new MutationObserver(() => {
				const nextInlineLeft = (handle.style.left || '').replace(/\s/g, '');

				if (nextInlineLeft === '100%') {
					handle.classList.add('diako-price-slider-handle--max');
				} else {
					handle.classList.remove('diako-price-slider-handle--max');
				}
			});

			observer.observe(handle, { attributes: true, attributeFilter: ['style'] });
		});
	};

	const initShopAjaxFilters = () => {
		const config = window.diakoShopFilters;
		const shell = document.querySelector('.diako-shop-shell');

		if (!config?.ajax || !shell) {
			return;
		}

		let activeRequest = null;

		const getMain = () => document.querySelector('[data-shop-main]');
		const getFilters = () => document.querySelector('[data-shop-filters-built-in]');

		const reinitShopFilterUi = () => {
			initShopFilterToggles();
			initPriceFilterSlider();
			initSaleCountdowns();
			window.setTimeout(initPriceFilterSlider, 0);
		};

		const scrollToResults = () => {
			const main = getMain();
			const target = document.querySelector('.diako-shop-toolbar') || main;

			if (!target) {
				return;
			}

			const top = window.scrollY + target.getBoundingClientRect().top - 24;
			window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
		};

		const setLoading = (isLoading) => {
			const main = getMain();

			if (!main) {
				return;
			}

			main.classList.toggle('is-loading', isLoading);
			main.setAttribute('aria-busy', isLoading ? 'true' : 'false');
		};

		const applyShopArchiveHtml = (doc) => {
			const nextMain = doc.querySelector('[data-shop-main]');
			const nextFilters = doc.querySelector('[data-shop-filters-built-in]');
			const currentMain = getMain();
			const currentFilters = getFilters();

			if (!nextMain || !currentMain) {
				return false;
			}

			currentMain.innerHTML = nextMain.innerHTML;

			if (nextFilters && currentFilters) {
				currentFilters.replaceWith(nextFilters);
			}

			reinitShopFilterUi();
			return true;
		};

		const closeMobileDrawer = () => {
			const drawer = document.querySelector('[data-shop-drawer]');
			const backdrop = document.querySelector('[data-shop-drawer-backdrop]');

			if (!drawer || !drawer.classList.contains('is-open')) {
				return;
			}

			drawer.classList.remove('is-open');
			backdrop?.classList.remove('is-open');
			document.body.classList.remove('diako-shop-drawer-open');
			drawer.setAttribute('aria-hidden', 'true');

			if (backdrop) {
				backdrop.hidden = true;
				backdrop.setAttribute('aria-hidden', 'true');
			}

			document.querySelectorAll('[data-shop-drawer-toggle]').forEach((button) => {
				button.setAttribute('aria-expanded', 'false');
			});
		};

		const loadShopArchive = async (url, { push = true, scroll = true } = {}) => {
			if (activeRequest) {
				activeRequest.abort();
			}

			activeRequest = new AbortController();
			setLoading(true);

			try {
				const response = await fetch(url, {
					credentials: 'same-origin',
					signal: activeRequest.signal,
					headers: {
						'X-Requested-With': 'XMLHttpRequest',
					},
				});

				if (!response.ok) {
					throw new Error(`HTTP ${response.status}`);
				}

				const html = await response.text();
				const doc = new DOMParser().parseFromString(html, 'text/html');

				if (!applyShopArchiveHtml(doc)) {
					window.location.href = url;
					return;
				}

				if (push) {
					window.history.pushState({ diakoShopArchive: true }, '', url);
				}

				if (scroll) {
					scrollToResults();
				}

				closeMobileDrawer();
			} catch (error) {
				if (error.name !== 'AbortError') {
					window.location.href = url;
				}
			} finally {
				setLoading(false);
				activeRequest = null;
			}
		};

		const getFilterLink = (target) => {
			const filtersRoot = target.closest('[data-shop-filters-built-in]');

			if (!filtersRoot) {
				return null;
			}

			const anchor = target.closest('a[href]');

			if (!anchor || anchor.target === '_blank' || anchor.hasAttribute('download')) {
				return null;
			}

			try {
				const linkUrl = new URL(anchor.href, window.location.origin);

				if (linkUrl.origin !== window.location.origin) {
					return null;
				}
			} catch (e) {
				return null;
			}

			return anchor;
		};

		shell.addEventListener('click', (event) => {
			const anchor = getFilterLink(event.target);

			if (!anchor) {
				return;
			}

			event.preventDefault();
			loadShopArchive(anchor.href);
		});

		shell.addEventListener('submit', (event) => {
			const form = event.target.closest('.widget_price_filter form');

			if (!form || !form.closest('[data-shop-filters-built-in]')) {
				return;
			}

			event.preventDefault();

			const url = new URL(form.getAttribute('action') || window.location.href, window.location.origin);
			const formData = new FormData(form);

			formData.forEach((value, key) => {
				if (value === '') {
					url.searchParams.delete(key);
					return;
				}

				url.searchParams.set(key, value);
			});

			loadShopArchive(url.toString());
		});

		window.addEventListener('popstate', () => {
			loadShopArchive(window.location.href, { push: false, scroll: false });
		});
	};

	const initShopSidebarDrawer = () => {
		const drawer = document.querySelector('[data-shop-drawer]');
		const backdrop = document.querySelector('[data-shop-drawer-backdrop]');
		const shell = document.querySelector('.diako-shop-shell');
		const desktopQuery = window.matchMedia('(min-width: 1024px)');

		if (!drawer || !backdrop || !shell) {
			return;
		}

		const setDrawerOpen = (isOpen) => {
			drawer.classList.toggle('is-open', isOpen);
			backdrop.classList.toggle('is-open', isOpen);
			document.body.classList.toggle('diako-shop-drawer-open', isOpen);

			drawer.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
			backdrop.hidden = !isOpen;
			backdrop.setAttribute('aria-hidden', isOpen ? 'false' : 'true');

			shell.querySelectorAll('[data-shop-drawer-toggle]').forEach((button) => {
				button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});
		};

		const closeDrawer = () => setDrawerOpen(false);
		const openDrawer = () => setDrawerOpen(true);
		const toggleDrawer = () => {
			if (drawer.classList.contains('is-open')) {
				closeDrawer();
				return;
			}
			openDrawer();
		};

		// Delegated handlers survive AJAX toolbar/filter replacements.
		shell.addEventListener('click', (event) => {
			if (event.target.closest('[data-shop-drawer-toggle]')) {
				event.preventDefault();
				toggleDrawer();
				return;
			}

			if (event.target.closest('[data-shop-drawer-close]')) {
				event.preventDefault();
				closeDrawer();
			}
		});

		backdrop.addEventListener('click', closeDrawer);

		drawer.addEventListener('click', (event) => {
			const link = event.target.closest('a[href]');

			if (!link || link.target === '_blank' || link.hasAttribute('download')) {
				return;
			}

			closeDrawer();
		});

		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && drawer.classList.contains('is-open')) {
				closeDrawer();
			}
		});

		desktopQuery.addEventListener('change', (event) => {
			if (event.matches) {
				closeDrawer();
			}
		});
	};

	const activateProductTab = (tabsRoot, tabKey) => {
		if (!tabsRoot || !tabKey) {
			return;
		}

		const triggers = tabsRoot.querySelectorAll('.diako-product-tabs__trigger');
		const panels = tabsRoot.querySelectorAll('.diako-product-tabs__panel');
		const trigger = tabsRoot.querySelector(`[data-tab-trigger="${tabKey}"]`);
		const panel = tabsRoot.querySelector(`[data-tab-panel="${tabKey}"]`);

		if (!trigger || !panel) {
			return;
		}

		triggers.forEach((item) => {
			item.setAttribute('aria-selected', 'false');
			item.setAttribute('tabindex', '-1');
			item.closest('.diako-product-tabs__item')?.classList.remove('active');
		});

		panels.forEach((item) => {
			item.classList.remove('is-active');
			item.hidden = true;
			item.style.display = 'none';
		});

		trigger.setAttribute('aria-selected', 'true');
		trigger.setAttribute('tabindex', '0');
		trigger.closest('.diako-product-tabs__item')?.classList.add('active');
		panel.classList.add('is-active');
		panel.hidden = false;
		panel.style.removeProperty('display');
	};

	const initProductTabs = () => {
		document.querySelectorAll('.diako-product-tabs').forEach((tabsRoot) => {
			tabsRoot.addEventListener('click', (event) => {
				const trigger = event.target.closest('.diako-product-tabs__trigger');
				if (!trigger || !tabsRoot.contains(trigger)) {
					return;
				}

				event.preventDefault();
				activateProductTab(tabsRoot, trigger.dataset.tabTrigger);
			});
		});

		document.body.addEventListener('click', (event) => {
			const reviewLink = event.target.closest('a.woocommerce-review-link');
			if (!reviewLink) {
				return;
			}

			const tabsRoot = document.querySelector('.diako-product-tabs');
			if (!tabsRoot) {
				return;
			}

			event.preventDefault();
			activateProductTab(tabsRoot, 'reviews');
			document.querySelector('#tab-reviews')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
		});
	};

	const initProductGallery = () => {
		const replaceTriggerIcon = () => {
			const triggerIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>';

			document.querySelectorAll('.woocommerce-product-gallery__trigger').forEach((trigger) => {
				let icon = trigger.querySelector('.diako-gallery-trigger__icon');

				if (!icon) {
					icon = document.createElement('span');
					icon.className = 'diako-gallery-trigger__icon';
					icon.setAttribute('aria-hidden', 'true');
					trigger.textContent = '';
					trigger.appendChild(icon);
				}

				if (icon.dataset.diakoIcon !== 'true') {
					icon.dataset.diakoIcon = 'true';
					icon.innerHTML = triggerIcon;
				}
			});
		};

		replaceTriggerIcon();

		const gallery = document.querySelector('.woocommerce-product-gallery');
		const thumbsRoot = document.querySelector('[data-gallery-thumbs]');
		const slides = gallery
			? [...gallery.querySelectorAll('.woocommerce-product-gallery__wrapper > .woocommerce-product-gallery__image')]
			: [];

		if (gallery) {
			const observer = new MutationObserver(replaceTriggerIcon);
			observer.observe(gallery, { childList: true, subtree: true });
		}

		const showSlide = (index) => {
			slides.forEach((slide, slideIndex) => {
				slide.classList.toggle('is-active', slideIndex === index);
			});

			if (gallery) {
				gallery.style.opacity = '1';
			}

			if (window.jQuery && gallery) {
				window.jQuery(gallery).trigger('woocommerce_gallery_init_zoom');
			}
		};

		if (slides.length) {
			showSlide(0);
		} else if (gallery) {
			gallery.style.opacity = '1';
		}

		if (!gallery || !thumbsRoot) {
			return;
		}

		const track = thumbsRoot.querySelector('.diako-gallery-thumbs__track');
		const items = [...thumbsRoot.querySelectorAll('.diako-gallery-thumbs__item')];
		const prevButton = thumbsRoot.querySelector('.diako-gallery-thumbs__nav--prev');
		const nextButton = thumbsRoot.querySelector('.diako-gallery-thumbs__nav--next');
		const isRtl = thumbsRoot.dataset.rtl === '1';
		let activeIndex = items.findIndex((item) => item.classList.contains('is-active'));

		if (activeIndex < 0) {
			activeIndex = 0;
		}

		const setActiveThumb = (index) => {
			items.forEach((item, itemIndex) => {
				item.classList.toggle('is-active', itemIndex === index);
			});

			const activeItem = items[index];
			if (activeItem) {
				activeItem.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });
			}
		};

		const updateNavState = () => {
			if (!prevButton || !nextButton || !items.length) {
				return;
			}

			if (isRtl) {
				prevButton.disabled = activeIndex >= items.length - 1;
				nextButton.disabled = activeIndex <= 0;
				return;
			}

			prevButton.disabled = activeIndex <= 0;
			nextButton.disabled = activeIndex >= items.length - 1;
		};

		const selectSlide = (index) => {
			if (!items.length) {
				return;
			}

			const nextIndex = Math.max(0, Math.min(items.length - 1, index));
			activeIndex = nextIndex;
			showSlide(nextIndex);
			setActiveThumb(nextIndex);
			updateNavState();
		};

		items.forEach((item) => {
			item.addEventListener('click', () => {
				const slideIndex = Number(item.dataset.slide);

				if (Number.isNaN(slideIndex)) {
					return;
				}

				selectSlide(slideIndex);
			});
		});

		prevButton?.addEventListener('click', () => {
			selectSlide(isRtl ? activeIndex + 1 : activeIndex - 1);
		});

		nextButton?.addEventListener('click', () => {
			selectSlide(isRtl ? activeIndex - 1 : activeIndex + 1);
		});

		selectSlide(activeIndex);
	};

	const initWooCommerceToasts = () => {
		const toastSelector = '.woocommerce-message, .woocommerce-info, .woocommerce-error';
		const TOAST_DURATION = 6000;
		const TOAST_RESUME = 4000;
		const processed = new WeakSet();
		let stack = document.getElementById('diako-toast-stack');

		const ensureStack = () => {
			if (stack) {
				return stack;
			}

			stack = document.createElement('div');
			stack.id = 'diako-toast-stack';
			stack.className = 'diako-toast-stack';
			stack.setAttribute('aria-live', 'polite');
			stack.setAttribute('aria-atomic', 'false');
			document.body.appendChild(stack);
			return stack;
		};

		const dismissToast = (toast, delay = 0) => {
			if (!toast || toast.classList.contains('is-leaving')) {
				return;
			}

			const removeToast = () => {
				toast._diakoToastAnim?.cancel();
				toast.classList.add('is-leaving');
				toast.addEventListener(
					'animationend',
					() => {
						toast.remove();
						if (stack && !stack.children.length) {
							stack.remove();
							stack = null;
						}
					},
					{ once: true }
				);
			};

			if (delay > 0) {
				window.setTimeout(removeToast, delay);
				return;
			}

			removeToast();
		};

		const getToastIconMarkup = (notice) => {
			if (notice.classList.contains('woocommerce-error')) {
				return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>';
			}

			if (notice.classList.contains('woocommerce-info')) {
				return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="16" y2="12"/><line x1="12" x2="12.01" y1="8" y2="8"/></svg>';
			}

			return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
		};

		const structureToastLayout = (notice) => {
			if (notice.querySelector('.diako-toast__inner')) {
				return;
			}

			const actionSelector = 'a.button, a.wc-forward, .woocommerce-Button, .button';
			const actionLinks = [...notice.querySelectorAll(actionSelector)].filter(
				(link) => !link.classList.contains('diako-toast__close')
			);

			const icon = document.createElement('span');
			icon.className = 'diako-toast__icon';
			icon.setAttribute('aria-hidden', 'true');
			icon.innerHTML = getToastIconMarkup(notice);

			const inner = document.createElement('div');
			inner.className = 'diako-toast__inner';

			const message = document.createElement('div');
			message.className = 'diako-toast__message';

			const actions = document.createElement('div');
			actions.className = 'diako-toast__actions';

			[...notice.childNodes].forEach((node) => {
				if (node.nodeType !== Node.ELEMENT_NODE && node.nodeType !== Node.TEXT_NODE) {
					return;
				}

				if (node.nodeType === Node.ELEMENT_NODE) {
					if (node.classList?.contains('diako-toast__close')) {
						return;
					}

					if (node.matches?.(actionSelector)) {
						actions.appendChild(node);
						return;
					}
				}

				if (node.nodeType === Node.TEXT_NODE && !node.textContent.trim()) {
					return;
				}

				message.appendChild(node);
			});

			inner.appendChild(message);

			if (actions.children.length) {
				inner.appendChild(actions);
			}

			notice.insertBefore(icon, notice.firstChild);
			notice.insertBefore(inner, icon.nextSibling);
		};

		const enhanceToast = (notice) => {
			if (!notice || processed.has(notice) || !notice.matches?.(toastSelector)) {
				return;
			}

			processed.add(notice);
			notice.classList.add('diako-toast');
			structureToastLayout(notice);

			if (!notice.querySelector('.diako-toast__close')) {
				const closeButton = document.createElement('button');
				closeButton.type = 'button';
				closeButton.className = 'diako-toast__close';
				closeButton.setAttribute('aria-label', 'بستن');
				closeButton.innerHTML =
					'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';
				closeButton.addEventListener('click', () => {
					notice._diakoToastAnim?.cancel();
					dismissToast(notice);
				});
				notice.appendChild(closeButton);
			}

			let progressBar = notice.querySelector('.diako-toast__progress-bar');

			if (!progressBar) {
				const progressTrack = document.createElement('div');
				progressTrack.className = 'diako-toast__progress';
				progressTrack.setAttribute('aria-hidden', 'true');
				progressBar = document.createElement('span');
				progressBar.className = 'diako-toast__progress-bar';
				progressTrack.appendChild(progressBar);
				notice.appendChild(progressTrack);
			}

			let dismissTimer = null;
			let animDuration = TOAST_DURATION;

			const startProgress = (duration) => {
				animDuration = duration;
				notice._diakoToastAnim?.cancel();
				notice._diakoToastAnim = progressBar.animate(
					[{ transform: 'scaleX(1)' }, { transform: 'scaleX(0)' }],
					{ duration, easing: 'linear', fill: 'forwards' }
				);
			};

			const scheduleDismiss = (duration) => {
				window.clearTimeout(dismissTimer);
				startProgress(duration);
				dismissTimer = window.setTimeout(() => dismissToast(notice), duration);
			};

			scheduleDismiss(TOAST_DURATION);

			notice.addEventListener('mouseenter', () => {
				window.clearTimeout(dismissTimer);

				if (notice._diakoToastAnim) {
					notice._diakoRemaining = Math.max(animDuration - notice._diakoToastAnim.currentTime, 0);
					notice._diakoToastAnim.pause();
				}
			});

			notice.addEventListener('mouseleave', () => {
				const remaining = notice._diakoRemaining ?? TOAST_RESUME;
				scheduleDismiss(Math.max(remaining, 500));
			});

			ensureStack().appendChild(notice);
		};

		const processWrappers = () => {
			document.querySelectorAll('.woocommerce-notices-wrapper').forEach((wrapper) => {
				wrapper.querySelectorAll(toastSelector).forEach(enhanceToast);
			});

			document.querySelectorAll(toastSelector).forEach((notice) => {
				if (!notice.closest('.diako-toast-stack')) {
					enhanceToast(notice);
				}
			});
		};

		processWrappers();

		const observer = new MutationObserver(() => {
			processWrappers();
		});

		observer.observe(document.body, { childList: true, subtree: true });

		if (window.jQuery) {
			window.jQuery(document.body).on(
				'added_to_cart removed_from_cart updated_wc_div checkout_error',
				() => {
					window.setTimeout(processWrappers, 50);
				}
			);
		}
	};

	const initReviewStarRating = () => {
		document.querySelectorAll('[data-star-rating]').forEach((root) => {
			const field = root.closest('.diako-star-rating-field');
			const select = field?.querySelector('#rating');
			const hint = field?.querySelector('[data-rating-hint]');
			const triggers = [...root.querySelectorAll('.diako-stars__trigger')];
			let labels = {};
			const placeholder = root.dataset.ratingPlaceholder || '';

			field?.querySelectorAll('p.stars').forEach((element) => element.remove());

			try {
				labels = JSON.parse(root.dataset.ratingLabels || '{}');
			} catch (error) {
				labels = {};
			}

			const setRating = (value, persist = true) => {
				const normalizedValue = Number(value) || 0;

				triggers.forEach((button) => {
					const buttonValue = Number(button.dataset.value);
					button.classList.toggle('is-active', normalizedValue > 0 && buttonValue <= normalizedValue);
					button.setAttribute('aria-checked', buttonValue === normalizedValue ? 'true' : 'false');
				});

				if (select && persist) {
					select.value = normalizedValue > 0 ? String(normalizedValue) : '';
				}

				if (hint) {
					const label = labels[normalizedValue] || labels[String(normalizedValue)] || '';
					hint.textContent = label || placeholder;
				}
			};

			triggers.forEach((button) => {
				button.addEventListener('click', () => {
					setRating(Number(button.dataset.value));
				});

				button.addEventListener('mouseenter', () => {
					setRating(Number(button.dataset.value), false);
				});
			});

			root.addEventListener('mouseleave', () => {
				setRating(select?.value || 0);
			});

			setRating(select?.value || 0);
		});

		const reviewForm = document.querySelector('.diako-product-reviews__comment-form');
		reviewForm?.addEventListener(
			'submit',
			(event) => {
				const select = reviewForm.querySelector('#rating');
				if (!select || !select.required) {
					return;
				}

				if (select.value) {
					return;
				}

				event.preventDefault();
				reviewForm.querySelector('.diako-star-rating-field')?.scrollIntoView({
					behavior: 'smooth',
					block: 'center',
				});
			},
			true
		);
	};

	const initCartAutoUpdate = () => {
		if (!document.querySelector('.woocommerce-cart-form') || !window.jQuery) {
			return;
		}

		const $ = window.jQuery;
		let updateTimer = null;

		const updateCartHeaderCount = () => {
			const desc = document.getElementById('diako-cart-header-desc');
			if (!desc) {
				return;
			}

			const count = document.querySelectorAll('.woocommerce-cart-form__cart-item').length;
			const emptyText = desc.dataset.emptyText || '';
			const countTemplate = desc.dataset.countText || '';

			if (count > 0 && countTemplate) {
				desc.textContent = countTemplate.replace('%d', toPersianDigits(count));
				return;
			}

			desc.textContent = emptyText;
		};

		$(document.body).on('change', '.woocommerce-cart-form .qty', function () {
			window.clearTimeout(updateTimer);
			updateTimer = window.setTimeout(() => {
				$('.woocommerce-cart-form :input[name="update_cart"]').prop('disabled', false);
				$(document.body).trigger('wc_update_cart');
			}, 500);
		});

		$(document.body).on('updated_wc_div updated_cart_totals wc_cart_emptied', updateCartHeaderCount);
		updateCartHeaderCount();
	};

	const initCheckoutCouponForm = () => {
		const couponForm = document.querySelector('form.checkout_coupon');
		if (!couponForm) {
			return;
		}

		const inputWrap = couponForm.querySelector('.diako-checkout-coupon__input-wrap');
		const couponRow = couponForm.querySelector('.diako-checkout-coupon__row');
		const couponInput = couponForm.querySelector('#coupon_code');

		const clearCouponErrors = () => {
			couponForm.querySelectorAll('.coupon-error-notice').forEach((notice) => {
				notice.remove();
			});

			if (!couponInput) {
				return;
			}

			couponInput.classList.remove('has-error');
			couponInput.removeAttribute('aria-invalid');
			couponInput.removeAttribute('aria-describedby');
		};

		const normalizeCouponError = () => {
			if (!couponRow || !couponInput) {
				return;
			}

			const notices = Array.from(couponForm.querySelectorAll('.coupon-error-notice'));
			if (!notices.length) {
				return;
			}

			notices.slice(0, -1).forEach((notice) => {
				notice.remove();
			});

			const notice = couponForm.querySelector('.coupon-error-notice');
			if (!notice) {
				return;
			}

			if (notice.previousElementSibling !== couponRow) {
				couponRow.after(notice);
			}

			couponInput.classList.add('has-error');
			couponInput.setAttribute('aria-invalid', 'true');
			couponInput.setAttribute('aria-describedby', 'coupon-error-notice');
		};

		const revealCouponForm = () => {
			couponForm.style.removeProperty('display');

			if (window.jQuery) {
				window.jQuery(couponForm).stop(true, true).show();
			}

			normalizeCouponError();
		};

		revealCouponForm();

		if (couponInput) {
			couponInput.addEventListener('input', clearCouponErrors);
		}

		couponForm.addEventListener(
			'submit',
			() => {
				clearCouponErrors();
			},
			true
		);

		if (inputWrap) {
			const observer = new MutationObserver(normalizeCouponError);
			observer.observe(inputWrap, { childList: true });
			observer.observe(couponForm, { childList: true, subtree: true });
		}

		if (window.jQuery) {
			window.jQuery(document.body).on(
				'updated_checkout init_checkout applied_coupon_in_checkout',
				revealCouponForm
			);
		}
	};

	const initCheckoutLayout = () => {
		if (
			!document.body.classList.contains('diako-checkout-page')
			&& !document.body.classList.contains('diako-edit-address-page')
		) {
			return;
		}

		const fixSelect2Width = () => {
			document.querySelectorAll('.diako-checkout-page .select2-container, .diako-edit-address-page .select2-container').forEach((container) => {
				container.style.width = '100%';
				container.style.maxWidth = '100%';
			});
		};

		fixSelect2Width();

		if (window.jQuery) {
			window.jQuery(document.body).on('init_checkout updated_checkout', fixSelect2Width);
		}
	};

	let saleCountdownTimer = null;

	const formatSaleCountdownRemaining = (seconds) => {
		const safeSeconds = Math.max(0, Number(seconds) || 0);
		const days = Math.floor(safeSeconds / 86400);
		const remainder = safeSeconds % 86400;
		const hours = Math.floor(remainder / 3600);
		const minutes = Math.floor((remainder % 3600) / 60);
		const secs = remainder % 60;
		const time = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;

		if (days > 0) {
			return `${toPersianDigits(days)} روز ${toPersianDigits(time)}`;
		}

		return toPersianDigits(time);
	};

	const updateSaleCountdownElement = (element) => {
		const until = Number(element.dataset.saleCountdownUntil);
		const timeNode = element.querySelector('[data-sale-countdown-time]');

		if (!until || !timeNode) {
			element.hidden = true;
			return;
		}

		const remaining = until - Math.floor(Date.now() / 1000);

		if (remaining <= 0) {
			element.hidden = true;
			return;
		}

		element.hidden = false;
		timeNode.textContent = formatSaleCountdownRemaining(remaining);
	};

	const initSaleCountdowns = () => {
		const elements = document.querySelectorAll('[data-sale-countdown]');

		elements.forEach(updateSaleCountdownElement);

		if (!elements.length || saleCountdownTimer) {
			return;
		}

		saleCountdownTimer = window.setInterval(() => {
			document.querySelectorAll('[data-sale-countdown]').forEach(updateSaleCountdownElement);
		}, 1000);
	};

	const initVariableProductForm = () => {
		const form = document.querySelector('form.variations_form');

		if (!form || !window.jQuery) {
			return;
		}

		const $form = window.jQuery(form);
		const wrap = form.querySelector('.single_variation_wrap');
		const button = form.querySelector('.single_add_to_cart_button');

		const keepWrapVisible = () => {
			if (!wrap) {
				return;
			}

			wrap.style.display = 'block';
			wrap.classList.add('diako-variation-wrap--visible');
		};

		const syncRadioGroup = (select) => {
			const selectName = select.getAttribute('name');
			const value = select.value;

			form.querySelectorAll(`[data-diako-variation-radio][data-target-select="${selectName}"]`).forEach((radio) => {
				const isMatch = radio.value === value;
				radio.checked = isMatch;
				radio.closest('.diako-variation-option')?.classList.toggle('is-selected', isMatch);
			});
		};

		const clearRadioGroups = () => {
			form.querySelectorAll('[data-diako-variation-radio]').forEach((radio) => {
				radio.checked = false;
			});
			form.querySelectorAll('.diako-variation-option.is-selected').forEach((option) => {
				option.classList.remove('is-selected');
			});
		};

		const setButtonNeedsSelection = (needsSelection) => {
			if (!button) {
				return;
			}

			button.disabled = needsSelection;
			button.classList.toggle('disabled', needsSelection);
			button.classList.toggle('wc-variation-selection-needed', needsSelection);
		};

		const variationIsSelected = () => {
			const variationId = form.querySelector('.variation_id')?.value;
			return Boolean(variationId && variationId !== '0');
		};

		const priceBlock = form.querySelector('.diako-add-to-cart-form__price');
		const priceNode = priceBlock?.querySelector('.price');
		const defaultPriceHtml = priceBlock?.dataset.diakoDefaultPriceHtml || priceNode?.innerHTML || '';

		const getVariations = () => {
			const fromData = $form.data('product_variations');

			if (Array.isArray(fromData)) {
				return fromData;
			}

			const raw = form.getAttribute('data-product_variations');

			if (!raw || raw === 'false') {
				return [];
			}

			try {
				const parsed = JSON.parse(raw);
				return Array.isArray(parsed) ? parsed : [];
			} catch (error) {
				return [];
			}
		};

		const normalizeValue = (value) => {
			if (value === undefined || value === null) {
				return '';
			}

			const stringValue = String(value);

			try {
				return decodeURIComponent(stringValue.replace(/\+/g, ' '));
			} catch (error) {
				return stringValue;
			}
		};

		const getAttributeFields = () => [
			...form.querySelectorAll('select[name^="attribute_"], input[name^="attribute_"]'),
		];

		const allAttributesChosen = () => {
			const fields = getAttributeFields();

			return fields.length > 0 && fields.every((field) => field.value);
		};

		const getSelectedAttributes = () => {
			const selected = {};

			getAttributeFields().forEach((field) => {
				const attributeName = field.getAttribute('data-attribute_name') || field.getAttribute('name');

				if (attributeName && field.value) {
					selected[attributeName] = field.value;
				}
			});

			return selected;
		};

		const findMatchingVariation = () => {
			const variations = getVariations();
			const fields = getAttributeFields();

			if (!fields.length || !variations.length) {
				return null;
			}

			const currentAttributes = {};
			let count = 0;
			let chosenCount = 0;

			fields.forEach((field) => {
				const attributeName = field.getAttribute('data-attribute_name') || field.getAttribute('name');
				const value = field.value || '';

				if (!attributeName) {
					return;
				}

				if (value) {
					chosenCount += 1;
				}

				count += 1;
				currentAttributes[attributeName] = value;
			});

			if (chosenCount !== count) {
				return null;
			}

			for (const variation of variations) {
				const attributes = variation.attributes || {};
				let match = true;

				for (const attributeName in attributes) {
					if (!Object.prototype.hasOwnProperty.call(attributes, attributeName)) {
						continue;
					}

					const val1 = attributes[attributeName];
					const val2 = currentAttributes[attributeName];

					if (
						val1 !== undefined &&
						val1 !== null &&
						val1 !== '' &&
						val2 !== undefined &&
						val2 !== null &&
						val2 !== '' &&
						normalizeValue(val1) !== normalizeValue(val2)
					) {
						match = false;
						break;
					}
				}

				if (match) {
					return variation;
				}
			}

			if (!allAttributesChosen()) {
				return null;
			}

			const selected = getSelectedAttributes();
			const fallbackMatches = getMatchingVariations(variations, selected);

			return fallbackMatches.length === 1 ? fallbackMatches[0] : null;
		};

		const getMatchingVariations = (variations, selected) =>
			variations.filter((variation) =>
				Object.entries(variation.attributes || {}).every(([key, value]) => {
					if (!value) {
						return true;
					}

					return !selected[key] || normalizeValue(selected[key]) === normalizeValue(value);
				})
			);

		const resolvePriceHtml = (variations) => {
			if (!variations.length) {
				return '';
			}

			const minVariation = variations.reduce((lowest, current) =>
				parseFloat(current.display_price) < parseFloat(lowest.display_price) ? current : lowest
			);

			return minVariation.price_html || '';
		};

		const updateVariablePriceDisplay = (explicitPriceHtml = '') => {
			if (!priceBlock || !priceNode) {
				return;
			}

			if (explicitPriceHtml) {
				priceNode.innerHTML = explicitPriceHtml;
				priceBlock.classList.remove('diako-add-to-cart-form__price--range');
				return;
			}

			const selected = getSelectedAttributes();

			if (!Object.keys(selected).length) {
				priceNode.innerHTML = defaultPriceHtml;
				priceBlock.classList.add('diako-add-to-cart-form__price--range');
				return;
			}

			const resolvedPriceHtml = resolvePriceHtml(getMatchingVariations(getVariations(), selected));

			if (resolvedPriceHtml) {
				priceNode.innerHTML = resolvedPriceHtml;
				priceBlock.classList.remove('diako-add-to-cart-form__price--range');
				return;
			}

			priceNode.innerHTML = defaultPriceHtml;
			priceBlock.classList.add('diako-add-to-cart-form__price--range');
		};

		const variationIdInput = form.querySelector('input.variation_id');

		const triggerSelectChange = (select) => {
			select.dispatchEvent(new Event('change', { bubbles: true }));
			window.jQuery(select).trigger('change');
			$form.trigger('woocommerce_variation_select_change');
			$form.trigger('check_variations');
		};

		const syncVariationState = (explicitVariation = null) => {
			const variation = explicitVariation || findMatchingVariation();

			if (variation && variationIdInput) {
				variationIdInput.value = String(variation.variation_id || 0);
				const canPurchase = Boolean(variation.is_purchasable && variation.is_in_stock);
				setButtonNeedsSelection(!canPurchase);
				updateVariablePriceDisplay(variation.price_html || '');
				return;
			}

			updateVariablePriceDisplay();

			if (!allAttributesChosen()) {
				if (variationIdInput) {
					variationIdInput.value = '0';
				}
				setButtonNeedsSelection(true);
			}
		};

		form.querySelectorAll('[data-diako-variation-radio]').forEach((radio) => {
			radio.addEventListener('change', () => {
				if (!radio.checked) {
					return;
				}

				const selectName = radio.dataset.targetSelect;
				const select = selectName ? form.querySelector(`select[name="${selectName}"]`) : null;

				if (!select) {
					return;
				}

				select.value = radio.value;
				triggerSelectChange(select);

				const group = radio.closest('[data-diako-variation-radios]');
				group?.querySelectorAll('.diako-variation-option').forEach((option) => {
					option.classList.toggle('is-selected', option.contains(radio) && radio.checked);
				});

				syncVariationState();
			});
		});

		form.querySelectorAll('select.diako-variation-select').forEach((select) => {
			window.jQuery(select).on('change', () => {
				syncRadioGroup(select);
				syncVariationState();
			});
			syncRadioGroup(select);
		});

		keepWrapVisible();

		$form.on('reset_data', () => {
			keepWrapVisible();
			clearRadioGroups();
			if (variationIdInput) {
				variationIdInput.value = '0';
			}
			setButtonNeedsSelection(true);
			updateVariablePriceDisplay();
		});

		$form.on('hide_variation', () => {
			keepWrapVisible();
			form.querySelectorAll('select.diako-variation-select').forEach(syncRadioGroup);

			if (allAttributesChosen()) {
				syncVariationState();
			}
		});

		$form.on('found_variation', (event, variation) => {
			keepWrapVisible();
			syncVariationState(variation);
		});

		$form.on('woocommerce_variation_select_change', () => {
			keepWrapVisible();
			syncVariationState();
		});

		$form.on('show_variation', (event, variation) => {
			keepWrapVisible();
			syncVariationState(variation);
		});

		$form.on('woocommerce_variations_loaded', () => {
			syncVariationState();
		});

		button?.addEventListener('click', () => {
			syncVariationState();
		});

		const startsDisabled = button?.classList.contains('wc-variation-selection-needed');

		if (startsDisabled && !variationIsSelected()) {
			setButtonNeedsSelection(true);
		}

		syncVariationState();
	};

	const initSingleProductSaleCountdown = () => {
		const form = document.querySelector('form.variations_form');
		const countdown = document.querySelector('.diako-sale-countdown--single[data-sale-countdown]');

		if (!form || !countdown || !window.jQuery) {
			return;
		}

		const defaultUntil = countdown.dataset.saleCountdownUntil || '';
		const defaultMode = countdown.dataset.saleCountdownMode || 'ends';

		const applySchedule = (schedule) => {
			if (!schedule || !schedule.until) {
				countdown.hidden = true;
				return;
			}

			countdown.dataset.saleCountdownUntil = String(schedule.until);
			countdown.dataset.saleCountdownMode = schedule.mode || 'ends';

			const labelNode = countdown.querySelector('.diako-sale-countdown__label');
			if (labelNode) {
				labelNode.textContent =
					schedule.mode === 'starts'
						? countdown.dataset.saleCountdownLabelStarts || labelNode.textContent
						: countdown.dataset.saleCountdownLabelEnds || labelNode.textContent;
			}

			updateSaleCountdownElement(countdown);
		};

		window.jQuery(form).on('found_variation', (event, variation) => {
			applySchedule(variation?.diako_sale_countdown || null);
		});

		window.jQuery(form).on('reset_data', () => {
			countdown.dataset.saleCountdownUntil = defaultUntil;
			countdown.dataset.saleCountdownMode = defaultMode;

			const labelNode = countdown.querySelector('.diako-sale-countdown__label');
			if (labelNode) {
				labelNode.textContent =
					defaultMode === 'starts'
						? countdown.dataset.saleCountdownLabelStarts || labelNode.textContent
						: countdown.dataset.saleCountdownLabelEnds || labelNode.textContent;
			}

			updateSaleCountdownElement(countdown);
		});
	};

	const initSinglePostPage = () => {
		const tocRoot = document.querySelector('[data-post-toc]');
		const tocToggle = tocRoot ? tocRoot.querySelector('[data-toc-toggle]') : null;
		const tocLinks = tocRoot ? Array.from(tocRoot.querySelectorAll('[data-toc-link]')) : [];
		const i18n = window.diakoBlogSingle?.i18n || {};

		if (tocToggle && tocRoot) {
			const hiddenCount = parseInt(tocToggle.dataset.hiddenCount || '0', 10);
			const showMoreLabel = (i18n.tocShowMore || 'نمایش %d مورد دیگر').replace(
				'%d',
				toPersianDigits(hiddenCount)
			);
			const showLessLabel = i18n.tocShowLess || 'نمایش کمتر';

			tocToggle.addEventListener('click', () => {
				const expanded = tocRoot.classList.toggle('is-expanded');
				tocToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
				tocToggle.textContent = expanded ? showLessLabel : showMoreLabel;
			});
		}

		const headings = tocLinks
			.map((link) => {
				const id = (link.getAttribute('href') || '').replace(/^#/, '');
				return id ? document.getElementById(id) : null;
			})
			.filter(Boolean);

		const setActiveTocLink = (id) => {
			tocLinks.forEach((link) => {
				const href = (link.getAttribute('href') || '').replace(/^#/, '');
				link.classList.toggle('is-active', href === id);
			});
		};

		tocLinks.forEach((link) => {
			link.addEventListener('click', (event) => {
				const href = link.getAttribute('href') || '';
				const target = href.startsWith('#') ? document.getElementById(href.slice(1)) : null;

				if (!target) {
					return;
				}

				event.preventDefault();
				target.scrollIntoView({ behavior: 'smooth', block: 'start' });
				setActiveTocLink(href.slice(1));
			});
		});

		if (headings.length) {
			const observer = new IntersectionObserver(
				(entries) => {
					const visible = entries
						.filter((entry) => entry.isIntersecting)
						.sort((a, b) => b.intersectionRatio - a.intersectionRatio);

					if (visible[0] && visible[0].target.id) {
						setActiveTocLink(visible[0].target.id);
					}
				},
				{
					rootMargin: '-20% 0px -60% 0px',
					threshold: [0, 0.25, 0.5, 1],
				}
			);

			headings.forEach((heading) => observer.observe(heading));
		}

		document.querySelectorAll('[data-share-copy]').forEach((button) => {
			button.addEventListener('click', async () => {
				const url = button.dataset.shareUrl || window.location.href;
				const i18n = window.diakoBlogSingle?.i18n || {};
				let copied = false;

				try {
					if (navigator.clipboard && window.isSecureContext) {
						await navigator.clipboard.writeText(url);
						copied = true;
					}
				} catch (e) {}

				if (!copied) {
					const input = document.createElement('textarea');
					input.value = url;
					input.setAttribute('readonly', '');
					input.style.position = 'absolute';
					input.style.left = '-9999px';
					document.body.appendChild(input);
					input.select();
					copied = document.execCommand('copy');
					document.body.removeChild(input);
				}

				if (!copied) {
					window.alert(i18n.copyFailed || 'کپی لینک انجام نشد.');
					return;
				}

				button.classList.add('is-copied');
				window.setTimeout(() => button.classList.remove('is-copied'), 1600);
			});
		});

		const subscribeForm = document.querySelector('[data-blog-subscribe-form]');
		const subscribeNotice = document.querySelector('[data-blog-subscribe-notice]');

		if (!subscribeForm || !window.diakoBlogSingle) {
			return;
		}

		subscribeForm.addEventListener('submit', async (event) => {
			event.preventDefault();

			const emailInput = subscribeForm.querySelector('input[name="email"]');
			const email = emailInput ? emailInput.value.trim() : '';
			const submitBtn = subscribeForm.querySelector('button[type="submit"]');
			const i18n = window.diakoBlogSingle.i18n || {};

			if (!email) {
				return;
			}

			if (submitBtn) {
				submitBtn.disabled = true;
			}

			const body = new URLSearchParams();
			body.set('action', 'diako_blog_newsletter_subscribe');
			body.set('nonce', window.diakoBlogSingle.nonce || '');
			body.set('email', email);

			try {
				const recaptchaToken = await fetchRecaptchaToken('newsletter_subscribe');
				if (window.diakoRecaptcha?.enabled) {
					body.set('recaptcha_token', recaptchaToken);
				}

				const response = await fetch(window.diakoBlogSingle.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
					body: body.toString(),
				});
				const data = await response.json();

				if (subscribeNotice) {
					subscribeNotice.hidden = false;
					subscribeNotice.textContent = data?.data?.message || '';
					subscribeNotice.classList.toggle('is-error', !data?.success);
				}

				if (data?.success && emailInput) {
					emailInput.value = '';
				}
			} catch (e) {
				if (subscribeNotice) {
					subscribeNotice.hidden = false;
					subscribeNotice.textContent =
						e?.message === 'recaptcha_unavailable'
							? window.diakoRecaptcha?.i18n?.unavailable || i18n.submitError
							: i18n.submitError || 'خطا در ثبت ایمیل. دوباره تلاش کنید.';
					subscribeNotice.classList.add('is-error');
				}
			} finally {
				if (submitBtn) {
					submitBtn.disabled = false;
				}
			}
		});
	};

	initSinglePostPage();

	const initContactForm = () => {
		const form = document.querySelector('[data-contact-form]');
		const notice = document.querySelector('[data-contact-notice]');
		const submitBtn = document.querySelector('[data-contact-submit]');

		if (!form || !window.diakoContactForm) {
			return;
		}

		form.addEventListener('submit', async (event) => {
			event.preventDefault();

			const i18n = window.diakoContactForm.i18n || {};
			const submitLabel = submitBtn ? submitBtn.querySelector('span') : null;
			const defaultLabel = submitLabel ? submitLabel.textContent : '';

			if (submitBtn) {
				submitBtn.disabled = true;
				if (submitLabel) {
					submitLabel.textContent = i18n.sending || 'در حال ارسال…';
				}
			}

			const body = new URLSearchParams(new FormData(form));
			body.set('action', 'diako_contact_form_submit');
			body.set('nonce', window.diakoContactForm.nonce || '');

			try {
				const recaptchaToken = await fetchRecaptchaToken('contact_form');
				if (window.diakoRecaptcha?.enabled) {
					body.set('recaptcha_token', recaptchaToken);
				}

				const response = await fetch(window.diakoContactForm.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
					body: body.toString(),
				});
				const data = await response.json();

				if (notice) {
					notice.hidden = false;
					notice.textContent = data?.data?.message || '';
					notice.classList.toggle('is-error', !data?.success);
					notice.classList.toggle('is-success', !!data?.success);
				}

				if (data?.success) {
					form.reset();
				}
			} catch (e) {
				if (notice) {
					notice.hidden = false;
					notice.textContent =
						e?.message === 'recaptcha_unavailable'
							? window.diakoRecaptcha?.i18n?.unavailable || i18n.submitError
							: i18n.submitError || 'خطا در ارسال پیام. دوباره تلاش کنید.';
					notice.classList.add('is-error');
					notice.classList.remove('is-success');
				}
			} finally {
				if (submitBtn) {
					submitBtn.disabled = false;
					if (submitLabel) {
						submitLabel.textContent = defaultLabel || i18n.submit || 'ارسال پیام';
					}
				}
			}
		});
	};

	initContactForm();

	const initComingSoonPage = () => {
		const countdown = document.querySelector('[data-coming-soon-countdown]');

		if (countdown) {
			const target = parseInt(countdown.getAttribute('data-target'), 10) * 1000;
			const units = {
				days: countdown.querySelector('[data-unit="days"]'),
				hours: countdown.querySelector('[data-unit="hours"]'),
				minutes: countdown.querySelector('[data-unit="minutes"]'),
				seconds: countdown.querySelector('[data-unit="seconds"]'),
			};

			const tick = () => {
				const diff = Math.max(0, target - Date.now());
				const totalSeconds = Math.floor(diff / 1000);
				const days = Math.floor(totalSeconds / 86400);
				const hours = Math.floor((totalSeconds % 86400) / 3600);
				const minutes = Math.floor((totalSeconds % 3600) / 60);
				const seconds = totalSeconds % 60;

				if (units.days) units.days.textContent = toPersianDigits(days);
				if (units.hours) units.hours.textContent = toPersianDigits(String(hours).padStart(2, '0'));
				if (units.minutes) units.minutes.textContent = toPersianDigits(String(minutes).padStart(2, '0'));
				if (units.seconds) units.seconds.textContent = toPersianDigits(String(seconds).padStart(2, '0'));
			};

			tick();
			window.setInterval(tick, 1000);
		}

		const form = document.querySelector('[data-coming-soon-form]');
		const notice = document.querySelector('[data-coming-soon-notice]');

		if (!form) {
			return;
		}

		form.addEventListener('submit', async (event) => {
			event.preventDefault();

			const emailInput = form.querySelector('input[name="email"]');
			const email = emailInput ? emailInput.value.trim() : '';
			const submitBtn = form.querySelector('button[type="submit"]');
			const submitError = form.getAttribute('data-error-message') || 'خطا در ثبت ایمیل. دوباره تلاش کنید.';

			if (!email) {
				return;
			}

			if (submitBtn) {
				submitBtn.disabled = true;
			}

			const body = new URLSearchParams();
			body.set('action', 'diako_coming_soon_subscribe');
			body.set('nonce', form.getAttribute('data-nonce') || '');
			body.set('email', email);

			try {
				const recaptchaToken = await fetchRecaptchaToken('newsletter_subscribe');
				if (window.diakoRecaptcha?.enabled) {
					body.set('recaptcha_token', recaptchaToken);
				}

				const response = await fetch(form.getAttribute('data-ajax-url') || '', {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
					body: body.toString(),
				});
				const data = await response.json();

				if (notice) {
					notice.hidden = false;
					notice.textContent = data?.data?.message || '';
					notice.classList.toggle('is-error', !data?.success);
				}

				if (data?.success && emailInput) {
					emailInput.value = '';
				}
			} catch (e) {
				if (notice) {
					notice.hidden = false;
					notice.textContent =
						e?.message === 'recaptcha_unavailable'
							? window.diakoRecaptcha?.i18n?.unavailable || submitError
							: submitError;
					notice.classList.add('is-error');
				}
			} finally {
				if (submitBtn) {
					submitBtn.disabled = false;
				}
			}
		});
	};

	initComingSoonPage();
})();
