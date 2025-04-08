/**
 * image upload interface which also could be used as an extra plugin for *CKEditor 5*.
 * * Each instance cannot handle mulitple files at the same time.
 * * Images are scaled down if either width or height is larger than the specified size.
 * @see [CKEditor 5: Custom upload adapter]{@link https://ckeditor.com/docs/ckeditor5/latest/framework/guides/deep-dive/upload-adapter.html}
 * @see [CKEditor 5: Interface UploadAdapter]{@link https://ckeditor.com/docs/ckeditor5/latest/api/module_upload_filerepository-UploadAdapter.html}
 */
class UploadAdapter {
	/**
	 * @param {ckeditor5/FileLoader} [loader] - required only for *CKEditor 5*. https://ckeditor.com/docs/ckeditor5/latest/api/module_upload_filerepository-FileLoader.html
	 */
	constructor (loader = null) {
	    this.loader = loader;
	    this.abortController = new AbortController();
	}

	/**
	 * Implements `abort()` for `UploadAdapter` of *CKEditor 5*.
	 * @see [MDN: AbortSignal]{@link https://developer.mozilla.org/en-US/docs/Web/API/AbortSignal}
	 */
	abort() {
	    this.abortController.abort();
	}

	/**
	 * Implements `upload()` for `UploadAdapter` of *CKEditor 5*.
	 * Also could be used with assigned `file` without *CKEditor*.
	 * @param {File} [file] - required if `loader` is not assigned during construction.
	 * @param {string} [path] - relative path to save the file.
	 * @returns {Promise.<Object>}
	 */
	async upload(file, path) {
	    const chunkSize = 2 * 1024 * 1024; // shall be smaller than `upload_max_filesize` in `php.ini`
	    if (! file) file = await this.loader.file;

	    let blob = file;
	    if (file.type.startsWith('image/')) {
	        blob = await resizeImage(file, {
	            width: 2048,
	            height: 2048,
	            fit: 'scaleDown',
	            format: 'image/jpeg',
	            quality: 0.8,
	            returnType: 'blob'
	        });
	    }

	    let result;
	    const body = new FormData();
	    const part_count = Math.ceil(blob.size / chunkSize);
	    for (let i = 0; i < part_count; ++i) {
	        const start = chunkSize * i;
	        const end = Math.min(chunkSize * (i + 1), blob.size);
	        const chunk = blob.slice(start, end, file.type);

	        body.set('file', chunk, file.name);
	        if (result) path = result.url;
	        if (path) body.set('path', path);

	        const options = {
	            method: 'POST',
	            body,
	            signal: this.abortController.signal
	        };
	        result = await fetchJSON('admin/upload.php', options);
	    }

	    return {default: result.url};
	}
}
