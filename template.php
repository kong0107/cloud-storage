<?php
require_once './lib/start.php';
/**
 * 於 `/lib/start.php` 宣告的變數：
 * * $db: 資料庫操作，見 `./lib/database.php` 。
 * * $Session, $Get, $Post: $_SESSION, $_GET, $_POST 的物件化版本，
 *   用法參見 `./lib/associative.php` 。建議就不要再直接操作 $_SESSION 、 $_GET 和 $_POST 。
 */

/**
 * 範例：若存取 template.php?messages[]=xxx&HAHAHA=YOYOYO&messages[]=yyy&foo=false
 * 可見輸出的 HTML 中， og:url 和 canonical 後段為 template.php?foo=0&messages%5B0%5D=xxx&messages%5B1%5D=yyy
 * 參數順序會與這裡宣告的格式為準，而不是網址列中的順序。
 */
$Get->convert(['foo' => 'bool', 'messages' => 'array']);

/**
 * 擷取與處理資料
 */



// 盡量將資料操作的程式碼和頁面呈現的程式碼切開，準備好資料後，才開始輸出 HTML 。




/**** 若沒有要輸出 HTML ，那就到此為止。 ****/

/**
 * 設定檔頭需要的資料：
 * * title: 不用寫網站名稱，會自動加上。
 * * description
 * * image: 代表本頁的一張圖片位址。若為相對路徑，會自動轉為絕對路徑。
 * * itemtype: 用於 Microdata 。
 * * breadcrumb_list 導覽標記。不用放首頁；最後一個是本頁自身。
 *   * name: 顯示名稱
 *   * url: 頁面連結，若是最後一個，不要設定此值。
 */
$page_info = [
	'title' => '範例頁面', //
	'description' => '這一行會出現在 HTML 檔頭。',
	'image' => 'https://fakeimg.pl/256x256/?font=noto&text=%E7%AF%84%E4%BE%8B',
	'itemtype' => 'WebPage',
	'breadcrumb_list' => []
];

// 開始輸出 HTML
require 'html-header.php';
?>

Here comes the content.

<?php require 'html-footer.php'; ?>
