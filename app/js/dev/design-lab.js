(function () {
	'use strict'

	var htmlEditor = document.querySelector('[data-html-editor]')
	var cssEditor = document.querySelector('[data-css-editor]')
	var preview = document.querySelector('[data-preview]')
	var stage = document.querySelector('[data-stage]')
	var saveState = document.querySelector('[data-save-state]')
	var storageKey = document.body.getAttribute('data-draft-key') || 'vn-design-lab-draft-v2'
	var saveTimer = null
	var migratedDraft = false
	var defaultHtml = htmlEditor.value
	var defaultCss = cssEditor.value

	function renderPreview() {
		var source = '<!doctype html><html lang="ru"><head><meta charset="utf-8">'
			+ '<meta name="viewport" content="width=device-width, initial-scale=1">'
			+ '<base href="/">'
			+ '<style>' + cssEditor.value + '</style></head><body>'
			+ htmlEditor.value + '</body></html>'
		preview.srcdoc = source
	}

	function persistDraft() {
		window.clearTimeout(saveTimer)
		saveTimer = window.setTimeout(function () {
			try {
				window.localStorage.setItem(storageKey, JSON.stringify({
					html: htmlEditor.value,
					css: cssEditor.value,
					savedAt: Date.now(),
				}))
				if (saveState) saveState.textContent = 'Черновик сохранён в этом браузере'
			} catch (error) {
				if (saveState) saveState.textContent = 'Не удалось сохранить черновик'
			}
		}, 250)
	}

	function update() {
		renderPreview()
		persistDraft()
	}

	try {
		var savedDraft = JSON.parse(window.localStorage.getItem(storageKey) || 'null')
		if (savedDraft && typeof savedDraft.html === 'string' && typeof savedDraft.css === 'string') {
			var oldShippingHtml = savedDraft.html
			savedDraft.html = savedDraft.html.replace(/\s*<a href="\/ru\/delivery">Подробнее<\/a>/g, '')
			if (savedDraft.html !== oldShippingHtml) migratedDraft = true
			var oldShippingCss = '.vn-shipping { min-height: 31px; display: flex; justify-content: center; align-items: center; gap: 5px; background: #f5f5f5; color: #555; font-size: 10px; letter-spacing: .025em; }'
			var newShippingCss = '.vn-shipping { min-height: 31px; display: flex; justify-content: center; align-items: center; gap: 5px; background: #111; color: #fff; font-size: 10px; letter-spacing: .025em; }'
			if (savedDraft.css.indexOf(oldShippingCss) !== -1) {
				savedDraft.css = savedDraft.css.replace(oldShippingCss, newShippingCss)
				savedDraft.css = savedDraft.css.replace('.vn-shipping span { color: #111; font-weight: 600; }', '.vn-shipping span { color: #fff; font-weight: 600; }')
				savedDraft.css = savedDraft.css.replace('.vn-shipping a { margin-left: 6px; color: #777; text-decoration: underline; text-underline-offset: 2px; }', '')
				migratedDraft = true
			}
			var brandStart = defaultHtml.indexOf('<section class="vn-brand-feature"')
			var categoryMarker = '  <section class="vn-categories"'
			var brandClose = defaultHtml.indexOf('</section>', brandStart)
			var brandEnd = brandClose === -1 ? -1 : brandClose + 10
			var draftCategory = savedDraft.html.indexOf(categoryMarker)

			if (brandStart !== -1 && brandEnd !== -1 && draftCategory !== -1 && savedDraft.html.indexOf('vn-brand-feature') === -1) {
				migratedDraft = true
				var brandMarkup = defaultHtml.slice(brandStart, brandEnd)
				var draftCategoryEnd = savedDraft.html.indexOf('</section>', draftCategory)
				if (draftCategoryEnd !== -1) {
					savedDraft.html = savedDraft.html.slice(0, draftCategoryEnd + 10) + '\n  ' + brandMarkup + savedDraft.html.slice(draftCategoryEnd + 10)
				}

				var brandScriptStart = defaultHtml.indexOf('<script>(function(){document.querySelectorAll(".vn-brand-feature")')
				var brandScriptEnd = brandScriptStart === -1 ? -1 : defaultHtml.indexOf('</script>', brandScriptStart) + 9
				var draftMainEnd = savedDraft.html.lastIndexOf('</main>')
				if (brandScriptStart !== -1 && brandScriptEnd > brandScriptStart && draftMainEnd !== -1) {
					savedDraft.html = savedDraft.html.slice(0, draftMainEnd) + defaultHtml.slice(brandScriptStart, brandScriptEnd) + savedDraft.html.slice(draftMainEnd)
				}

				if (savedDraft.css.indexOf('.vn-brand-feature {') === -1) {
					var baseStyleStart = defaultCss.indexOf('.vn-brand-feature {')
					var baseStyleEnd = defaultCss.indexOf('.vn-categories {', baseStyleStart)
					var mobileStyleStart = defaultCss.indexOf('  .vn-brand-feature {', baseStyleEnd)
					var mobileStyleEnd = defaultCss.indexOf('  .vn-categories {', mobileStyleStart)
					if (baseStyleStart !== -1 && baseStyleEnd !== -1) {
						savedDraft.css += '\n' + defaultCss.slice(baseStyleStart, baseStyleEnd)
						if (mobileStyleStart !== -1 && mobileStyleEnd !== -1) {
							savedDraft.css += '\n@media (max-width: 700px) {\n' + defaultCss.slice(mobileStyleStart, mobileStyleEnd) + '\n}\n'
						}
					}
				}

				if (saveState) saveState.textContent = 'В черновик добавлена бренд-секция; ваши изменения сохранены'
			}

			if (savedDraft.html.indexOf('vn-brand-feature') !== -1) {
				var brandMarkupBeforeCleanup = savedDraft.html
				savedDraft.html = savedDraft.html.replace(/(<section class="vn-brand-feature"[\s\S]*?<\/section>)/, function (sectionMarkup) {
					return sectionMarkup
						.replace(/<span class="vn-product__badge">BRAND<\/span>/g, '')
						.replace(/href="https?:\/\/(?:www\.)?vizaje-nica\.com([^"]*)"/g, 'href="$1"')
				})
				if (savedDraft.html !== brandMarkupBeforeCleanup) migratedDraft = true

				var brandMatch = savedDraft.html.match(/<section class="vn-brand-feature"[\s\S]*?<\/section>/)
				var categoriesStart = savedDraft.html.indexOf(categoryMarker)
				if (brandMatch && categoriesStart !== -1) {
					var brandIndex = savedDraft.html.indexOf(brandMatch[0])
					var categoriesEnd = savedDraft.html.indexOf('</section>', categoriesStart)
					if (brandIndex !== -1 && categoriesEnd !== -1 && brandIndex < categoriesStart) {
						savedDraft.html = savedDraft.html.slice(0, brandIndex) + savedDraft.html.slice(brandIndex + brandMatch[0].length)
						categoriesEnd = savedDraft.html.indexOf('</section>', categoriesStart)
						savedDraft.html = savedDraft.html.slice(0, categoriesEnd + 10) + '\n  ' + brandMatch[0] + savedDraft.html.slice(categoriesEnd + 10)
						migratedDraft = true
					}
				}
			}

			var oldDesktopCopy = '.vn-brand-feature__copy { position: absolute; z-index: 1; top: 20px; left: 20px; display: flex; flex-direction: column; align-items: flex-start; gap: 9px; }'
			var newDesktopCopy = '.vn-brand-feature__copy { position: absolute; z-index: 1; top: 20px; right: 9%; display: flex; flex-direction: column; align-items: flex-end; gap: 9px; text-align: right; }'
			var oldMobileCopy = '.vn-brand-feature__copy { top: 14px; left: 15px; gap: 7px; }'
			var newMobileCopy = '.vn-brand-feature__copy { top: 14px; right: 15px; gap: 7px; }'
			if (savedDraft.css.indexOf(oldDesktopCopy) !== -1) {
				savedDraft.css = savedDraft.css.replace(oldDesktopCopy, newDesktopCopy)
				migratedDraft = true
			}
			if (savedDraft.css.indexOf(oldMobileCopy) !== -1) {
				savedDraft.css = savedDraft.css.replace(oldMobileCopy, newMobileCopy)
				migratedDraft = true
			}

			var giftStart = defaultHtml.indexOf('<section class="vn-gift-certificate"')
			var giftClose = defaultHtml.indexOf('</section>', giftStart)
			var giftEnd = giftClose === -1 ? -1 : giftClose + 10
			if (giftStart !== -1 && giftEnd !== -1) {
				var giftMarkup = defaultHtml.slice(giftStart, giftEnd)
				var existingGift = savedDraft.html.match(/<section class="vn-gift-certificate"[\s\S]*?<\/section>/)
				if (existingGift) {
					if (existingGift[0] !== giftMarkup) {
						savedDraft.html = savedDraft.html.replace(existingGift[0], giftMarkup)
						migratedDraft = true
					}
				} else {
				var insertAfter = savedDraft.html.indexOf('<section class="vn-brand-feature"')
				if (insertAfter !== -1) {
					insertAfter = savedDraft.html.indexOf('</section>', insertAfter) + 10
				} else {
					insertAfter = savedDraft.html.indexOf(categoryMarker)
					insertAfter = insertAfter === -1 ? -1 : savedDraft.html.indexOf('</section>', insertAfter) + 10
				}
				if (insertAfter !== -1) {
					savedDraft.html = savedDraft.html.slice(0, insertAfter) + '\n  ' + giftMarkup + savedDraft.html.slice(insertAfter)
					migratedDraft = true
				}
				}
			}
			var storeStart = defaultHtml.indexOf('<section class="vn-store-feature"')
			var storeClose = defaultHtml.indexOf('</section>', storeStart)
			var storeEnd = storeClose === -1 ? -1 : storeClose + 10
			if (storeStart !== -1 && storeEnd !== -1) {
				var storeMarkup = defaultHtml.slice(storeStart, storeEnd)
				var existingStore = savedDraft.html.match(/<section class="vn-store-feature"[\s\S]*?<\/section>/)
				if (existingStore) {
					if (existingStore[0] !== storeMarkup) {
						savedDraft.html = savedDraft.html.replace(existingStore[0], storeMarkup)
						migratedDraft = true
					}
				} else {
					var insertStoreAfter = savedDraft.html.indexOf('<section class="vn-gift-certificate"')
					if (insertStoreAfter !== -1) {
						insertStoreAfter = savedDraft.html.indexOf('</section>', insertStoreAfter) + 10
					} else {
						insertStoreAfter = savedDraft.html.indexOf('<section class="vn-brand-feature"')
						if (insertStoreAfter !== -1) {
							insertStoreAfter = savedDraft.html.indexOf('</section>', insertStoreAfter) + 10
						} else {
							insertStoreAfter = savedDraft.html.indexOf(categoryMarker)
							insertStoreAfter = insertStoreAfter === -1 ? -1 : savedDraft.html.indexOf('</section>', insertStoreAfter) + 10
						}
					}
					if (insertStoreAfter > 9) {
						savedDraft.html = savedDraft.html.slice(0, insertStoreAfter) + '\n  ' + storeMarkup + savedDraft.html.slice(insertStoreAfter)
						migratedDraft = true
					}
				}
			}
			if (savedDraft.css.indexOf('.vn-gift-certificate__title { position: absolute;') === -1) {
				var giftCssStart = defaultCss.indexOf('.vn-gift-certificate {')
				var giftCssEnd = defaultCss.indexOf('.vn-categories {', giftCssStart)
				var mobileGiftCssStart = defaultCss.indexOf('  .vn-gift-certificate {', giftCssEnd)
				var mobileGiftCssEnd = defaultCss.indexOf('  .vn-categories {', mobileGiftCssStart)
				if (giftCssStart !== -1 && giftCssEnd !== -1) {
					savedDraft.css += '\n' + defaultCss.slice(giftCssStart, giftCssEnd)
					if (mobileGiftCssStart !== -1 && mobileGiftCssEnd !== -1) {
						savedDraft.css += '\n@media (max-width: 700px) {\n' + defaultCss.slice(mobileGiftCssStart, mobileGiftCssEnd) + '\n}\n'
					}
					migratedDraft = true
				}
			}
			if (savedDraft.css.indexOf('.vn-store-feature__title { position: absolute;') === -1) {
				var storeCssStart = defaultCss.indexOf('.vn-store-feature {')
				var storeCssEnd = defaultCss.indexOf('.vn-categories {', storeCssStart)
				var mobileStoreCssStart = defaultCss.indexOf('  .vn-store-feature {', storeCssEnd)
				var mobileStoreCssEnd = defaultCss.indexOf('  .vn-categories {', mobileStoreCssStart)
				if (storeCssStart !== -1 && storeCssEnd !== -1) {
					savedDraft.css += '\n' + defaultCss.slice(storeCssStart, storeCssEnd)
					if (mobileStoreCssStart !== -1 && mobileStoreCssEnd !== -1) {
						savedDraft.css += '\n@media (max-width: 700px) {\n' + defaultCss.slice(mobileStoreCssStart, mobileStoreCssEnd) + '\n}\n'
					}
					migratedDraft = true
				}
			}
			if (savedDraft.css.indexOf('.vn-brand-feature__rail .vn-product h3 { font-size: 13px;') === -1) {
				savedDraft.css += '\n.vn-brand-feature__rail .vn-product h3 { display: -webkit-box; overflow: hidden; font-size: 13px; line-height: 1.4; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }\n@media (max-width: 700px) { .vn-brand-feature__rail .vn-product h3 { min-height: 0; font-size: 10px; line-height: 1.4; } }\n'
				migratedDraft = true
			}
			if (savedDraft.css.indexOf('.vn-brand-feature__rail .vn-product { margin-bottom: 12px; }') === -1) {
				savedDraft.css += '\n.vn-brand-feature__rail .vn-product { margin-bottom: 12px; }\n'
				migratedDraft = true
			}
			if (storageKey !== 'vn-design-lab-catalog-draft-v1') {
				var showcaseBeforeUpdate = savedDraft.html
				savedDraft.html = savedDraft.html.replace(/(<section class="vn-section vn-showcase"[\s\S]*?<\/section>)/, function (sectionMarkup) {
					return sectionMarkup.replace(/<article class="vn-product">[\s\S]*?<\/article>/g, function (cardMarkup) {
						if (cardMarkup.indexOf('vn-product__favorite') !== -1) return cardMarkup
						return cardMarkup.replace(/(<a class="vn-product__visual"[^>]*>)([\s\S]*?)(<\/a>)(\s*)(<a class="vn-product__info")/, function (match, openLink, visualContent, closeLink, spacing, infoLink) {
							var flags = []
							visualContent = visualContent.replace(/<span class="vn-product__badge">([^<]*)<\/span>/g, function (badgeMarkup, label) {
								var kind = label.trim() === 'SALE' ? 'sale' : (label.trim() === 'NEW' ? 'new' : 'hit')
								flags.push('<span class="vn-product__badge vn-product__badge--' + kind + '">' + label + '</span>')
								return ''
							})
							visualContent = visualContent.replace(/<span class="vn-product__sale">[\s\S]*?<\/span>/g, function (saleMarkup) {
								flags.push(saleMarkup)
								return ''
							})
							if (flags.length) visualContent = visualContent.replace(/(<span class="vn-product__bag")/, '<span class="vn-product__flags">' + flags.join('') + '</span>$1')
							var favorite = '<button class="vn-product__favorite" type="button" aria-label="Добавить в избранное" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 8.8c0 5.3-8.8 11-8.8 11S3.2 14.1 3.2 8.8A4.7 4.7 0 0 1 12 6.3a4.7 4.7 0 0 1 8.8 2.5Z"/></svg></button>'
							return '<div class="vn-product__media">' + openLink + visualContent + closeLink + favorite + '</div>' + spacing + infoLink
						})
					})
				})
				if (savedDraft.html !== showcaseBeforeUpdate) migratedDraft = true
				if (savedDraft.html.indexOf('vn-favorite-toggle') === -1) {
					savedDraft.html = savedDraft.html.replace('</main>', '<script>/* vn-favorite-toggle */(function(){var section=document.getElementById("vn-showcase");if(!section)return;section.addEventListener("click",function(event){var button=event.target.closest(".vn-product__favorite");if(!button)return;var active=button.getAttribute("aria-pressed")!=="true";button.setAttribute("aria-pressed",active?"true":"false");button.setAttribute("aria-label",active?"Убрать из избранного":"Добавить в избранное")})})()<\/script>\n</main>')
					migratedDraft = true
				}
				var cardExperimentMarker = '/* Design Lab slider card experiment */'
				if (savedDraft.css.indexOf(cardExperimentMarker) === -1) {
					var cardExperimentStart = defaultCss.indexOf(cardExperimentMarker)
					if (cardExperimentStart !== -1) {
						savedDraft.css += '\n' + defaultCss.slice(cardExperimentStart)
						migratedDraft = true
					}
				}
			}
			if (storageKey === 'vn-design-lab-catalog-draft-v1') {
				var catalogHtmlBeforeUpdate = savedDraft.html
				var catalogCssBeforeUpdate = savedDraft.css
				var starterCatalogHeader = defaultHtml.match(/<header class="vc-topbar">[\s\S]*?<\/header>/)
				if (starterCatalogHeader) savedDraft.html = savedDraft.html.replace(/<header class="vc-topbar">[\s\S]*?<\/header>/, starterCatalogHeader[0])
				savedDraft.html = savedDraft.html.replace(/\s*<section class="vc-hero"[\s\S]*?<\/section>/, '')
				savedDraft.html = savedDraft.html.replace(/<button class="vc-product__favorite"[\s\S]*?<\/button>/g, '')
				savedDraft.html = savedDraft.html.replace(/<p class="vc-product__rating"[\s\S]*?<\/p>/g, '')
				savedDraft.html = savedDraft.html.replace(/<span class="vc-product__(?:badge|dots)"[\s\S]*?<\/span>/g, '')
				savedDraft.html = savedDraft.html.replace(/<span class="vc-product__promo"[\s\S]*?<\/span>/g, '')
				var catalogPromo = savedDraft.html.match(/<div class="vc-promo-strip">[\s\S]*?<\/div>/)
				if (catalogPromo) {
					savedDraft.html = savedDraft.html.replace(catalogPromo[0], '')
					savedDraft.html = savedDraft.html.replace(/(<header class="vc-topbar">[\s\S]*?<\/header>)/, '$1\n  ' + catalogPromo[0])
				}
				if (savedDraft.html.indexOf('catalog-language-toggle') === -1) {
					savedDraft.html = savedDraft.html.replace('</main>', '<script>/* catalog-language-toggle */document.querySelectorAll("[data-lang-option]").forEach(function(button){button.addEventListener("click",function(){document.querySelectorAll("[data-lang-option]").forEach(function(option){option.setAttribute("aria-pressed",option===button?"true":"false")})})})<\/script>\n</main>')
					migratedDraft = true
				}
				if (savedDraft.html !== catalogHtmlBeforeUpdate) migratedDraft = true
				if (savedDraft.css.indexOf('/* catalog design refresh */') === -1) {
					savedDraft.css += '\n/* catalog design refresh */\n.vc-catalog { --vc-accent: #111; padding-bottom: calc(70px + env(safe-area-inset-bottom)); }\n.vc-hero { display: none !important; }\n.vc-product__favorite, .vc-product__rating { display: none !important; }\n.vc-product__badge, .vc-product__promo { background: #111; color: #fff; }\n.vc-promo-strip { position: static; z-index: auto; background: #111; color: #fff; }\n'
					migratedDraft = true
				}
				if (savedDraft.css.indexOf('/* catalog header update */') === -1) {
					savedDraft.css += '\n/* catalog header update */\n.vc-topbar { height: 55px; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; }\n.vc-topbar__back, .vc-topbar__actions { display: none !important; }\n.vc-topbar__brand { justify-self: auto; font-size: 15px; }\n.vc-lang-switcher { width: 88px; height: 32px; display: grid; grid-template-columns: 1fr 1fr; align-items: center; padding: 2px; border: 1px solid #e5e5e5; border-radius: 999px; background: #fff; }\n.vc-lang-switcher button { min-width: 0; height: 26px; padding: 0; border: 0; border-radius: 999px; background: transparent; color: #999; font-size: 11px; font-weight: 500; cursor: pointer; }\n.vc-lang-switcher button[aria-pressed="true"] { background: #111; color: #fff; }\n@media (min-width: 700px) { .vc-topbar { height: 64px; padding: 0 5vw; } .vc-topbar__brand { font-size: 18px; } }\n'
					migratedDraft = true
				}
				if (savedDraft.css.indexOf('/* catalog language segmented */') === -1) {
					savedDraft.css += '\n/* catalog language segmented */\n.vc-lang-switcher { width: 88px; height: 32px; display: grid; grid-template-columns: 1fr 1fr; align-items: center; padding: 2px; border: 1px solid #e5e5e5; border-radius: 999px; background: #fff; }\n.vc-lang-switcher button { min-width: 0; height: 26px; padding: 0; border: 0; border-radius: 999px; background: transparent; color: #999; font-size: 11px; font-weight: 500; cursor: pointer; }\n.vc-lang-switcher button[aria-pressed="true"] { background: #111; color: #fff; }\n'
					migratedDraft = true
				}
				if (savedDraft.css.indexOf('/* catalog home product cards */') === -1) {
					savedDraft.css += '\n/* catalog home product cards */\n.vc-products .vc-product__media { aspect-ratio: 1 / 1; background: #f2f2f2; }\n.vc-products .vc-product__media img { position: absolute; top: 50%; left: 50%; width: 88%; height: 88%; object-fit: contain; mix-blend-mode: multiply; transform: translate(-50%, -50%); }\n.vc-products .vc-product__badge, .vc-products .vc-product__dots, .vc-products .vc-product__promo { display: none !important; }\n.vc-products .vc-product__bag { right: 8px; bottom: -17px; background: #2f2f2f; }\n.vc-products .vc-product__details { min-height: 138px; display: flex; flex-direction: column; padding: 14px 16px 16px; }\n.vc-products .vc-product__brand { margin: 0 0 5px; overflow: hidden; color: #6f6f6f; font-size: 9px; font-weight: 500; line-height: 1.2; letter-spacing: .08em; text-overflow: ellipsis; text-transform: uppercase; white-space: nowrap; }\n.vc-products .vc-product__name { min-height: 2.6em; font-size: 13px; font-weight: 500; line-height: 1.3; }\n.vc-products .vc-product__price { display: flex; align-items: baseline; flex-wrap: wrap; gap: 7px; margin: auto 0 0; padding-top: 10px; font-size: 16px; font-weight: 600; line-height: 1.15; }\n.vc-products .vc-product__price del { color: #999; font-size: 11px; font-weight: 400; }\n@media (max-width: 767px) { .vc-products .vc-product__details { min-height: 130px; padding: 17px 0 0; } .vc-products .vc-product__brand { margin-bottom: 7px; color: #111; font-size: 10px; font-weight: 600; } .vc-products .vc-product__name { min-height: 2.7em; font-size: 15px; font-weight: 400; line-height: 1.35; } }\n'
					migratedDraft = true
				}
				if (savedDraft.css !== catalogCssBeforeUpdate) migratedDraft = true
			}

			htmlEditor.value = savedDraft.html
			cssEditor.value = savedDraft.css
			if (saveState && saveState.textContent.indexOf('добавлена бренд-секция') === -1) saveState.textContent = 'Восстановлен локальный черновик'
		}
	} catch (error) {
		if (saveState) saveState.textContent = 'Стартовый макет'
	}

	renderPreview()
	if (migratedDraft) persistDraft()
	htmlEditor.addEventListener('input', update)
	cssEditor.addEventListener('input', update)

	var initialViewport = stage.getAttribute('data-viewport') || 'desktop'
	document.querySelectorAll('[data-viewport]').forEach(function (item) {
		item.setAttribute('aria-pressed', item.getAttribute('data-viewport') === initialViewport ? 'true' : 'false')
	})
	document.querySelectorAll('[data-viewport]').forEach(function (button) {
		button.addEventListener('click', function () {
			var viewport = button.getAttribute('data-viewport')
			stage.setAttribute('data-viewport', viewport)
			document.querySelectorAll('[data-viewport]').forEach(function (item) {
				item.setAttribute('aria-pressed', item === button ? 'true' : 'false')
			})
		})
	})

	var fullscreenButton = document.querySelector('[data-fullscreen]')
	var fullscreenLabel = document.querySelector('[data-fullscreen-label]')
	function updateFullscreenButton() {
		var active = document.fullscreenElement === stage || stage.classList.contains('is-fullscreen')
		if (fullscreenButton) fullscreenButton.setAttribute('aria-pressed', active ? 'true' : 'false')
		if (fullscreenLabel) fullscreenLabel.textContent = active ? 'Свернуть' : 'На весь экран'
	}
	function useFullscreenFallback() {
		stage.classList.toggle('is-fullscreen')
		updateFullscreenButton()
	}
	if (fullscreenButton) {
		fullscreenButton.addEventListener('click', function () {
			if (stage.classList.contains('is-fullscreen')) {
				stage.classList.remove('is-fullscreen')
				updateFullscreenButton()
				return
			}
			if (document.fullscreenElement === stage) {
				if (document.exitFullscreen) document.exitFullscreen()
				else useFullscreenFallback()
				return
			}
			if (!stage.requestFullscreen) {
				useFullscreenFallback()
				return
			}
			var request = stage.requestFullscreen()
			if (request && typeof request.catch === 'function') {
				request.catch(function () {
					stage.classList.add('is-fullscreen')
					updateFullscreenButton()
				})
			}
		})
		document.addEventListener('fullscreenchange', updateFullscreenButton)
		document.addEventListener('fullscreenerror', function () {
			stage.classList.add('is-fullscreen')
			updateFullscreenButton()
		})
		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && stage.classList.contains('is-fullscreen')) {
				stage.classList.remove('is-fullscreen')
				updateFullscreenButton()
			}
		})
	}

	document.querySelector('[data-restore]').addEventListener('click', function () {
		htmlEditor.value = defaultHtml
		cssEditor.value = defaultCss
		if (saveState) saveState.textContent = 'Стартовый макет восстановлен'
		update()
	})

	document.querySelector('[data-reset]').addEventListener('click', function () {
		try {
			window.localStorage.removeItem(storageKey)
		} catch (error) {
			// The preview still resets if local storage is unavailable.
		}
		htmlEditor.value = defaultHtml
		cssEditor.value = defaultCss
		var defaultViewport = stage.getAttribute('data-default-viewport') || 'desktop'
		stage.setAttribute('data-viewport', defaultViewport)
		document.querySelectorAll('[data-viewport]').forEach(function (item) {
			item.setAttribute('aria-pressed', item.getAttribute('data-viewport') === defaultViewport ? 'true' : 'false')
		})
		if (saveState) saveState.textContent = 'Черновик сброшен'
		renderPreview()
	})
})()
