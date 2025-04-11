<?php
	require_once 'lib/html-prepare.php';
	$script_src_beforeend[] = 'assets/browse.js';
	require 'lib/html-header.php';
?>

<nav aria-label="breadcrumb"
	class="d-flex column-gap-2"
	style="--bs-breadcrumb-divider: '';"
>
	現在位置
	<ol class="breadcrumb"></ol>
</nav>

<fieldset>
	<legend>操作</legend>
	<div id="operation-dir">
		<button type="button"
			class="btn btn-info me-2"
			data-bs-toggle="collapse"
			data-bs-target="#mkdir"
			aria-expanded="false"
			aria-controls="mkdir"
		>
			<i class="bi bi-folder-plus pe-2"></i>
			新增資料夾
		</button>
		<label class="btn btn-info">
			<i class="bi bi-cloud-upload pe-2"></i>
			上傳檔案
			<input type="file"
				class="d-none"
				multiple
			>
		</label>
		<form id="mkdir"
			class="collapse input-group form-floating"
		>
			<input type="text"
				id="mkdir-input"
				class="form-control"
				required
				name="dump"
				pattern="[^\\\/\:\*\?'&quot;<>\|]+"
				placeholder="新資料夾名稱"
			>
			<label for="mkdir-input">新資料夾名稱</label>
			<button type="submit"
				class="btn btn-primary"
			>建立資料夾</button>
		</form>
		<fieldset id="upload-progress-uploader"
			class="alert alert-primary" role="alert"
		>
			<legend>上傳進度</legend>
			<div id="upload-progress"></div>
		</fieldset>
		<!-- 下載整個資料夾（包含子資料夾）
		更名
		移動
		刪除 -->
	</div>
	<div id="operation-file">
		<a  id="btn-download"
			class="btn btn-primary me-2"
		>
			<i class="bi bi-cloud-download pe-2"></i>
			下載檔案
		</a>
		<!-- 更名
		移動
		刪除 -->
	</div>
</fieldset>

<div id="content"></div>
<ul id="files"
	class="d-md-table"
	style="border-collapse: separate; border-spacing: .5rem;"
></ul>

<fieldset>
	<legend>屬性</legend>
	<div id="stat"
		class="d-table"
		style="border-collapse: separate; border-spacing: .5rem;"
	></div>
</fieldset>

<?php require 'lib/html-footer.php'; ?>
