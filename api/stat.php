<?php
require '../lib/user_authn.php';
assert_login();
if (! isset($_GET['path']) || str_contains($_GET['path'], '..')) finish(406);

$fullpath = DIR_STORAGE . "/{$_GET['path']}";
if (! file_exists($fullpath)) finish(404);

$result = file_get_stat($fullpath);
exit_json($result);


/**
 * Get info I care of a file
 * @param string $path
 * @return array|null
 */
function file_get_stat($path) {
	$fInfo = new SplFileInfo($path);
	$result = array(
		'type' => $fInfo->getType(),
		'name' => $fInfo->getFilename(),
		'mtime' => $fInfo->getMTime()
	);

	switch ($result['type']) {
		case 'file': {
			$result['size'] = $fInfo->getSize();
			$result['mime'] = mime_content_type($path);
			$result['charset'] = finfo_file(finfo_open(FILEINFO_MIME_ENCODING), $path);
			break;
		}
		case 'dir': {
			$result['files'] = array_values(array_diff(
				scandir($path),
				array('.', '..')
			));
			break;
		}
		case 'link': {
			$result['target'] = file_get_stat($fInfo->getRealPath());
			break;
		}
		case 'unknown':
		case false: {
			return null;
		}
	}

	return $result;
}

