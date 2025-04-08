<?php
/**
 * Resize an image and then output.
 * https://www.php.net/manual/en/function.imagecopyresampled.php
 * https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Disposition
 */

$filename = $_GET['src'];
$width = intval($_GET['width'] ?? 128);
$height = intval($_GET['height'] ?? 128);
$quality = intval($_GET['quality'] ?? -1);

if (! file_exists($filename) || (strpos($filename, '..') !== false)) {
	http_response_code(403);
	header('Content-Type: image/svg+xml');
	readfile('assets/gpp_bad_FILL0_wght400_GRAD0_opsz48.svg');
	exit;
}

list($width_orig, $height_orig, $type) = getimagesize($filename);
$ratio = $width_orig / $height_orig;

if (isset($_GET['width']) xor isset($_GET['height'])) {
	if (isset($_GET['height'])) $width = round($height * $ratio);
	if (isset($_GET['width'])) $height = round($width / $ratio);
}
else {
	if ($width / $height > $ratio) $width = round($height * $ratio);
	else $height = round($width / $ratio);
}

$image_p = imagecreatetruecolor($width, $height);
$image = imagecreatefromtype($type, $filename);
imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

header('Content-Type: ' . image_type_to_mime_type($type));
header(sprintf('Content-Disposition: inline; filename="%s"', basename($filename)));
imageoutput($type, $image_p, null, $quality);


function imagecreatefromtype(
	int $type,
	string $filename
)/* : GdImage */{
	switch ($type) {
	    case IMAGETYPE_BMP: return imagecreatefrombmp($filename);
	    case IMAGETYPE_GIF: return imagecreatefromgif($filename);
	    case IMAGETYPE_PNG: return imagecreatefrompng($filename);
	    case IMAGETYPE_WBMP: return imagecreatefromwbmp($filename);
	    case IMAGETYPE_WEBP: return imagecreatefromwebp($filename);
	    case IMAGETYPE_XBM: return imagecreatefromxbm($filename);

	    case IMAGETYPE_JPEG:
	    case IMAGETYPE_JPEG2000:
	        return imagecreatefromjpeg($filename);

	    case IMAGETYPE_SWF:
	    case IMAGETYPE_PSD:
	    case IMAGETYPE_TIFF_II:
	    case IMAGETYPE_TIFF_MM:
	    case IMAGETYPE_IFF:
	    case IMAGETYPE_JB2:
	    case IMAGETYPE_JPC:
	    case IMAGETYPE_JP2:
	    case IMAGETYPE_JPX:
	    case IMAGETYPE_SWC:
	    case IMAGETYPE_ICO:
	        return false;

	    default: throw new Exception('unknown type');
	}
}

function imageoutput(
	int $type,
	/*GdImage*/ $image,
	/*mixed*/ $file = null,
	int $quality = -1
) : bool {
	switch ($type) {
	    case IMAGETYPE_BMP: return imagebmp($image, $file, !!$quality); // true|false
	    case IMAGETYPE_GIF: return imagegif($image, $file);
	    case IMAGETYPE_PNG: return imagepng($image, $file, $quality); // 0~9
	    case IMAGETYPE_WBMP: return imagewbmp($image, $file);
	    case IMAGETYPE_WEBP: return imagewebp($image, $file, $quality); // 0~100
	    case IMAGETYPE_XBM: return imagexbm($image, $file);

	    case IMAGETYPE_JPEG:
	    case IMAGETYPE_JPEG2000:
	        return imagejpeg($image, $file, $quality); // 0~100

	    case IMAGETYPE_SWF:
	    case IMAGETYPE_PSD:
	    case IMAGETYPE_TIFF_II:
	    case IMAGETYPE_TIFF_MM:
	    case IMAGETYPE_IFF:
	    case IMAGETYPE_JB2:
	    case IMAGETYPE_JPC:
	    case IMAGETYPE_JP2:
	    case IMAGETYPE_JPX:
	    case IMAGETYPE_SWC:
	    case IMAGETYPE_ICO:
	        return false;

	    default: throw new Exception('unknown type');
	}
}
