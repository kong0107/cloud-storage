/// 網址標準化
((c, l) => {
	if (c && c !== l.origin + l.pathname + l.search)
		history.replaceState({}, '', c + l.hash);
})($('link[rel="canonical"]')?.href, location);

/// 缺失屬性補齊： [itemtype]
$$('[itemtype]').forEach(item => {
	item.setAttribute('itemscope', '');
	const type = item.getAttribute('itemtype');
	if (! type.includes('/')) item.setAttribute('itemtype', `https://schema.org/${type}`);
});

/// 缺失屬性補齊： [alt][aria-label][aria-hidden]
$$('img').forEach(img => {
	if (img.alt) img.ariaLabel = img.alt;
	else {
		img.alt = '';
		img.ariaHidden = 'true';
	}
});

/// 修改預設行為： img 和 iframe 預設為 lazy loading
$$('img, iframe').forEach(elem => elem.loading = 'lazy');

/// 修改預設結構：對 iconfont 加上 [aria-hidden] ，並在後面加一個 span.visually-hidden 。
$$('i.bi').forEach(icon => {
	icon.ariaHidden = 'true';
	if (icon.title) {
		icon.after(createElementFromJsonML(
			['span', {class: 'visually-hidden'}, icon.title]
		));
		icon.ariaLabel = icon.title;
	}
});

/// 修改預設行為：為有 [title] 或 [placeholder] 的加上 [aria-label]
$$('[title]:not(i):not([aria-label])').forEach(elem => elem.ariaLabel = elem.title);
$$('[placeholder]:not([aria-label])').forEach(input => input.ariaLabel = input.placeholder);

/**
 * 擴充 aria-label ，並原則上加上 bootstrap 的 tooltip
 * @see https://lepture.com/zh/2015/fe-aria-label
 */
$$('[aria-label]:not([data-bs-placement="none"])').forEach(elem => {
	if ($('[aria-label]:not([data-bs-placement="none"])', elem)) return; // 若有後代有 aira-label ，就不要顯示。
	new bootstrap.Tooltip(elem, {
		placement: elem.dataset.bsPlacement ?? 'top',
		title: elem.ariaLabel
	});
});
