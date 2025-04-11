/**
 * @todo mkdir
 * @todo skip duplicate file before upload
 * @todo rename
 * @todo delete
 * @todo base64url as hash func
 */

if (! location.pathname.includes('/browse/')) {
	history.replaceState(null, '', 'browse/');
}

// let path = location.pathname.slice(location.pathname.indexOf('/browse/') + 8);
// console.debug(`Required path: ${decodeURIComponent(path)}`);

/** function */
const numFormat = new Intl.NumberFormat(undefined, {minimumFractionDigits: 1, maximumFractionDigits:1, roundingMode: 'ceil'}).format;

route();
async function route(path) {
	if (path === undefined) {
		path = location.pathname.slice(new URL($('base').href).pathname.length + 7);
	}
	const stat = await fetchJSON(`api/stat.php?path=${path}`);
	console.debug('stat', path, stat);

	if (stat.type === 'dir') {
		if (path && ! path.endsWith('/')) path += '/';
		history.replaceState(null, '', `browse/${path}`);
	}

	$('.breadcrumb').replaceChildren(
		...['', ...path.split('/').filter(x => x)]
		.map((segment, i, array) => {
			if (i === array.length - 1) return ['li',
				{class: 'breadcrumb-item active', aria: {current: 'page'}},
				(stat.type === 'dir') ? `${segment}/` : segment
			];
			const href = 'browse' + array.slice(0, i + 1).join('/');
			return ['li', {class: 'breadcrumb-item'},
				['a', {href}, `${segment}/`]
			];
		})
	);

	if (! path) delete stat.name;
	$('#stat').replaceChildren(
		...Object.keys(stat)
		.filter(key => typeof stat[key] !== 'object')
		.map(key =>
			['dl', {class: 'd-table-row'},
				['dt', {class: 'd-table-cell'}, key],
				['dd', {class: 'd-table-cell'}, stat[key]]
			]
		)
	);

	hide('#operation-dir', '#operation-file', '#upload-progress-uploader');
	clear('#content', '#files');

	switch (stat.type) {
		case 'dir': {
			show('#operation-dir');
			if (stat.files.length) {
				stat.files.sort((a, b) => {
					if (a.type !== b.type) return (a.type > b.type) ? 1 : -1;
					return (a.name > b.name) ? 1 : -1;
				});
				$('#files').append(
					...stat.files.map(child => {
						let icon = 'patch-question';
						if (child.type === 'file') icon = 'file-earmark-richtext';
						else if (child.type === 'dir') icon = 'folder';
						else if (child.target.type === 'dir') icon = 'folder-symlink';
						return ['li', {class: 'd-md-table-row'},
							['span', {class: 'd-inline d-md-table-cell'}, ['i', {class: `bi bi-${icon}`, title: child.type}]],
							['a', {class: 'd-inline d-md-table-cell', href: `browse/${path}${child.name}`}, child.name],
							['div', {class: 'd-block d-md-table-cell'},
								['div', {class: 'd-flex'},
									['span', {class: 'text-end flex-grow-1 px-2'},
										(child.type === 'file') ? (numFormat(child.size / 1048576) + ' MB') : ''
									],
									['time', {}, dateFormat('y-m-d H:i', child.mtime * 1000)]
								]
							]
						];
					})
				);
			}
			else $('#content').append('本目錄尚無內容');
			break;
		}
		case 'file': {
			show('#operation-file');
			$('#btn-download').set({
				href: `dl/${path}`,
				download: stat.name
			});
			break;
		}
		default: {
			$('#content').append('未支援的檔案類型');
		}
	}

}

function hide(...ss) { ss.forEach(s => $(s)?.classList.add('d-none')); }
function show(...ss) { ss.forEach(s => $(s)?.classList.remove('d-none')); }
function clear(...ss) { ss.forEach(s => $(s)?.replaceChildren()); }

listen('form#mkdir', 'onsubmit', function (event) {
	event.preventDefault();
	fetchStrict('api/mkdir.php', {
		method: 'POST',
		body: {path: path + $('#mkdir-input').value}
	})
	.then(() => {
		// todo
		alert('新增資料夾成功');
		route();
	})
	.catch(alerter('failure'));
});


listen('[type=file]', 'onchange', function (event) {
	const files = event.target.files;
	if (! files.length) return;
	console.debug(files);

	let qCheckProgress, qUpload;
	qCheckProgress = qUpload = Promise.resolve();

	let sizeTotal = 0;
	for (let index = 0; index < files.length; ++index) {
		const file = files[index];
		sizeTotal += file.size;
		qCheckProgress = qCheckProgress.then(async () => {
			// console.debug(`CheckProgress #${index} start`);
			let time = Date.now();
			const blobBuffer = await file.arrayBuffer();
			const hashBuffer = await crypto.subtle.digest('SHA-256', blobBuffer);
			const hash = btoa(String.fromCharCode(...new Uint8Array(hashBuffer)))
				.replaceAll('+', '-').replaceAll('/', '_').replaceAll('=', '');
			console.debug(`Calculated hash in ${Date.now() - time} ms for a file with size ${numFormat(file.size / 1048576)} MB.`);

			let {size: progress} = await fetchJSON(`api/progress.php?hash=${hash}`);
			const progressMB = progress / 1048576;
			$(`tr[data-index="${index}"] progress`).value = progressMB;
			$('progress#total').value += progressMB;

			qUpload = qUpload.then(async () => {
				// console.debug(`Upload #${index} start`);
				const chunkSize = 1048576;
				const fd = new FormData();
				fd.set('path', path + file.name);
				fd.set('size', file.size);

				$(`tr[data-index="${index}"] .status`).textContent = '上傳中';
				time = Date.now();
				for (let offset = progress; offset < file.size; offset += chunkSize) {
					fd.set('offset', offset);
					fd.set('chunk', file.slice(offset, offset + chunkSize), hash);
					const upload = await fetchJSON('api/upload.php', {
						method: 'POST',
						body: fd
					});
					const realChunkSizeMB = (upload.progress - offset) / 1048576;
					$(`tr[data-index="${index}"] progress`).value += realChunkSizeMB;
					$('progress#total').value += realChunkSizeMB;
				}
				console.debug(`Uploaded ${file.size - progress} bytes in ${Date.now() - time} ms.`);
				$(`tr[data-index="${index}"] .status`).textContent = '上傳完成';
				// console.debug(`Upload #${index} end`);
			}); /* qUpload */
			// console.debug(`CheckProgress #${index} end`);
		}); /* qCheckProgress */
	} /* for files */

	const sizeTotalMB = sizeTotal / 1048576;
	$('#upload-progress').replaceChildren(
		['progress', {id: 'total', class: 'w-100', max: sizeTotalMB, value: 0}],
		['div', `${files.length} 個檔案共 ` + numFormat(sizeTotalMB) + ' MB'],
		['table', {class: 'd-block d-md-table w-100'},
			['thead', {},
				['tr', {class: 'd-none d-md-table-row'},
					['th', '檔名'],
					['th', '尺寸'],
					['th', '上傳狀態']
				]
			],
			['tbody', {},
				...[...files].map((file, index) => {
					const sizeMB = file.size / 1048576;
					return ['tr', {class: 'd-block d-md-table-row', data: {index}},
						['td', {class: 'd-block d-md-table-cell'}, file.name],
						['td', {class: 'd-block d-md-table-cell'}, numFormat(sizeMB) + ' MB'],
						['td', {class: 'd-block d-md-table-cell'},
							['progress', {max: sizeMB}],
							['div', {class: 'status small text-secondary'}, '等待中']
						]
					]; /* <tr> */
				})
			] /* <tbody> */
		] /* <table> */
	); /* <fieldset>.append */
}); /* listen('[type=file]', 'onchange') */
