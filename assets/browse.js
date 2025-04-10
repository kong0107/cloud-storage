if (! location.pathname.includes('/browse/')) {
	history.replaceState(null, '', 'browse/');
}

let path = location.pathname.slice(location.pathname.indexOf('/browse/') + 8);
console.debug(`Required path: ${decodeURIComponent(path)}`);

fetchJSON(`api/stat.php?path=${path}`)
.then(stat => {
	console.debug(path, Object.keys(stat));
	if (stat.type === 'dir') {
		if (! path.endsWith('/')) path += '/';
	}

	const segArr = path.split('/').filter(x => x);
	segArr.unshift('');
	$('#breadcrumbs').replaceChildren(
		...segArr.map((segment, i) => {
			if (i === segArr.length - 1)
				return ['li', {class: 'list-inline-item'}, segment];
			const href = 'browse' + segArr.slice(0, i + 1).join('/');
			return ['li', {class: 'list-inline-item'},
				['a', {href, class: 'text-decoration-none'}, `${segment}/`]
			];
		})
	);

	if (stat.type !== 'dir') {
		$('#stat').replaceChildren(
			...Object.keys(stat).map(key =>
				['li', {}, `${key}: ${stat[key]}`]
			)
		);
	}
	else $('#stat').replaceChildren();

	let contents = [];
	switch (stat.type) {
		case 'dir': {
			if (! stat.files.length) contents.push('本目錄無內容');
			else contents.push(['ol',
				...stat.files.map(file =>
					['li',
						['a', {href: `browse/${path}${file}`}, file]
					]
				)
			]);
			break;
		}
	}
	console.debug(contents);
	$('#content').replaceChildren(...contents);
});
