if (! location.pathname.includes('/browse/')) {
	history.replaceState(null, '', 'browse/');
}

let path = location.pathname.slice(location.pathname.indexOf('/browse/') + 8);
console.debug(`Required path: ${decodeURIComponent(path)}`);

/** function */
const numFormat = new Intl.NumberFormat(undefined, {minimumFractionDigits: 1, maximumFractionDigits:1, roundingMode: 'ceil'}).format;

fetchJSON(`api/stat.php?path=${path}`)
.then(stat => {
	console.debug(path, stat);
	if (stat.type === 'dir') {
		if (path && ! path.endsWith('/')) path += '/';
	}

	const segArr = path.split('/').filter(x => x);
	segArr.unshift('');
	$('#breadcrumbs').replaceChildren(
		...segArr.map((segment, i) => {
			if (i === segArr.length - 1)
				return ['li', {class: 'list-inline-item'}, segment + (stat.type === 'dir' ? '/' : '')];
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

	const contents = [];
	switch (stat.type) {
		case 'dir': {
			/// @todo mkdir
			if (! stat.files.length) contents.push('本目錄無內容');
			else {
				stat.files.sort((a, b) => {
					if (a.type !== b.type) return (a.type > b.type) ? 1 : -1;
					return (a.name > b.name) ? 1 : -1;
				});
				contents.push(
					['ul', {class: 'd-md-table', style: 'border-collapse: separate; border-spacing: .5rem;'},
						...stat.files.map(file => {
							let icon;
							if (file.type === 'file') icon = 'file-earmark-richtext';
							else if (file.type === 'dir') icon = 'folder';
							else if (file.target.type === 'dir') icon = 'folder-symlink';
							else icon = 'patch-question';
							return ['li', {class: 'd-md-table-row'},
								['span', {class: 'd-inline d-md-table-cell'}, ['i', {class: `bi bi-${icon}`, title: file.type}]],
								['a', {class: 'd-inline d-md-table-cell', href: `browse/${path}${file.name}`}, file.name],
								['div', {class: 'd-block d-md-table-cell'},
									['div', {class: 'd-flex'},
										['span', {class: 'text-end flex-grow-1 px-2'},
											(file.type === 'file') ? (numFormat(file.size / 1048576) + ' MB') : ''
										],
										['time', {}, dateFormat('y-m-d H:i', file.mtime * 1000)]
									]
								]
							]
						})
					]
				);
				contents.push(
					['fieldset'
						['legned', '新建資料夾']
					]
				);
				contents.push(
					['fieldset',
						['legend', '上傳'],
						['input', {
							type: 'file',
							multiple: true,
							onchange: async function () {
								if (! this.files.length) return;
								console.debug(this.files);

								let qCheckProgress, qUpload;
								qCheckProgress = qUpload = Promise.resolve();

								let sizeTotal = 0;
								for (let index = 0; index < this.files.length; ++index) {
									const file = this.files[index];
									sizeTotal += file.size;
									qCheckProgress = qCheckProgress.then(async () => {
										console.debug(`CheckProgress #${index} start`);
										let time = Date.now();
										const blobBuffer = await file.arrayBuffer();
										const hashBuffer = await crypto.subtle.digest('SHA-256', blobBuffer);
										const hash = Array.from(
											new Uint8Array(hashBuffer),
											byte => byte.toString(16).padStart(2, '0')
										).join('');
										console.debug(hash);
										console.debug(
											'calculated hash in '
											+ (Date.now() - time)
											+ ' ms for a file with size '
											+ numFormat(file.size / 1048576)
											+ ' MB.'
										);
										// console.debug('base64: ' + btoa(hashBuffer.toString()));

										time = Date.now();
										let {size: progress} = await fetchJSON(`api/progress.php?hash=${hash}`);
										console.debug(`checked in ${Date.now() - time} ms for the file has been uploaded for ${progress} bytes.`);
										const progressMB = progress / 1048576;
										$(`tr[data-index="${index}"] progress`).value = progressMB;
										$('progress#total').value += progressMB;

										qUpload = qUpload.then(async () => {
											console.debug(`Upload #${index} start`);
											const chunkSize = 1048576;
											const fd = new FormData();
											fd.set('path', path + file.name);
											fd.set('size', file.size);

											$(`tr[data-index="${index}"] .status`).textContent = '上傳中';
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
											$(`tr[data-index="${index}"] .status`).textContent = '上傳完成';
											console.debug(`Upload #${index} end`);
										}); /* qUpload */
										console.debug(`CheckProgress #${index} end`);
									}); /* qCheckProgress */
								} /* for this.files */

								const sizeTotalMB = sizeTotal / 1048576;
								this.closest('fieldset').append(
									['progress', {id: 'total', class: 'w-100', max: sizeTotalMB, value: 0}],
									['div', `${this.files.length} 個檔案共 ` + numFormat(sizeTotalMB) + ' MB'],
									['table', {class: 'd-block d-md-table'},
										['thead', {},
											['tr', {class: 'd-none d-md-table-row'}
												['th', '檔名'],
												['th', '尺寸'],
												['th', '上傳狀態']
											]
										],
										['tbody', {},
											...[...this.files].map((file, index) => {
												const sizeMB = file.size / 1048576;
												return ['tr', {class: 'd-block d-md-table-row', data: {index}},
													['td', {class: 'd-block d-md-table-cell'},
														file.name,
														['div', {class: 'status small text-secondary'}, '等待中']
													],
													['td', {class: 'd-block d-md-table-cell'}, numFormat(sizeMB) + ' MB'],
													['td', {class: 'd-block d-md-table-cell'},
														['progress', {max: sizeMB}]
													]
												]; /* <tr> */
											})
										] /* <tbody> */
									] /* <table> */
								); /* <fieldset>.append */
							} /* <input onchange> */
						}], /* <input type="file"> */
					] /* <fieldset> */
				); /* contents.push */
			}
			break;
		}
		case 'file': {
			contents.push(
				['a', {
					href: `dl/${path}`,
					download: stat.name
				}, '下載']
			);
			break;
		}
	}
	$('#content').replaceChildren(...contents);
})
.catch(() => {
	$('main').setText('請先登入');
});
