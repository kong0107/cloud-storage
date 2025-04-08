# 待定



# 臭蟲


# 名詞

與案主／使用者溝通時，務必確認彼此的用語一致。


# 需求

盡量支援 PHP 7.4 ，即
* 已有 arrow function ，如 `fn ($arg) => $arg * 2`
* 未支援命名參數，如 PHP 8.0 起的 `htmlspecialchars($string, double_encode: false);`
* 函數宣告時可指定引數和回傳值，但限於單一型別且不支援 `mixed`, `null`, `false`, `true` 。
  （PHP 8.0 起才支援聯合型別 `A|B`； 8.1 起支援交集類型如 `A&B` ； 8.2 起支援 `null`, `false`, `true`）
* 未支援 nullsafe operator ，即 PHP 8.0 引入的 `?->` 。
* 未支援 `enum` 結構。可用 `class` 模擬，但會缺少 `::cases` 方法。
* 八進位表示法僅看得懂 `016` ，看不懂 `0o16` 。（後者於 PHP 8.1 支援）

較簡單的函數則可透過引用 `lib/polyfill8*.php` 來支援。

# 網站地圖


## 後台



# 語法習慣

## 命名

變數、 HTML ID 用 `snake_case` 格式；
函式用 `camelCase` 格式；
CSS Class 用 `kebab-case` 格式。

## HTML

* 善用 `aria-label` 來說明 HTML 元件，好處：
  * 不影響排版
  * 不用另外寫註解
  * 對視障者友善
  參考 [前端的基礎修養：aria-label](https://lepture.com/zh/2015/fe-aria-label)
* `<html lang="zh-Hant-TW">` 是參考 [英傑銳公司 2016 的文章](https://www.injerry.com/blog_view/125)。
* `<link rel="apple-touch-icon" href>` 是參考 [Apple 官網文件](https://developer.apple.com/library/archive/documentation/AppleApplications/Reference/SafariWebContent/ConfiguringWebApplications/ConfiguringWebApplications.html)。
* 麵包屑參考：
  * https://www.w3.org/WAI/ARIA/apg/example-index/breadcrumb/index.html
  * https://getbootstrap.com/docs/5.3/components/breadcrumb/
  * https://schema.org/BreadcrumbList

### 標籤屬性順序

* itemprop
* itemtype （只要列出名字就好，網址和 `itemscope` 會被 `beforeend.js` 補上。）
* role （給視障者聽；給工程師看）
* aria-label （給視障者聽；給工程師看）
* title （給使用者看）
* type (for `<input>`)
* id
* name
* value
* content


## PHP

* 各種命名以 `snake_case` 為主
* 與 HTML 互嵌而有判斷式時，用另一種標記：
  ```php
  <?php if ($a == 5): ?>
	A is equal to 5
  <?php endif; ?>
  ```
  此結構亦可用於迴圈。



## JavaScript

* 各種命名以 `camelCase` 為主
* 善用 `kong-util` 。
  [範例](https://kong0107.github.io/kong-util/demo.html)（←見此頁的 JS ）



# 參考資料

## Schema.org
* `Product.category` 不能巢狀，實際上用 google_product_category 的 2503 吧。
* SKU (stock keeping unit) 不是量詞。 https://www.researchmfg.com/2016/06/sku/

## Google

### Product

* [ID](https://support.google.com/merchants/answer/6324405?hl=zh-Hant&ref_topic=6324338)
  > 請儘量使用 SKU 作為 ID。
  > 每個 SKU 都是獨一無二的，因此還可以幫助您避免意外用到重覆的 ID。

* 沒有提到 `category`，但有提到 `inProductGroupWithID` 且標示「最多指定一個值」。

* [商品群組](https://support.google.com/merchants/answer/6324507?hl=zh-Hant)
  > 如果一件 T 恤有 9 個子類：3 種尺寸 (「小」、「中」、「大」) 和 3 種顏色 (「紅色」、「藍色」、「綠色」)，請將每項子類做為獨立產品提交 (共 9 項不同的產品)，並為每項子類的商品群組 ID 屬性提交相同的值，藉此表示這些子類屬於同一產品。

* [商品](https://support.google.com/merchants/answer/11018531?hl=zh-Hant)長度
  > 與 schema.org 資源 `Product.depth` 相對應的屬性是 `product_length`。


### ImageObject
* 要有 `copyrightNotice`


## SEO
* WAI-ARIA:
  * https://ithelp.ithome.com.tw/users/20152260/ironman/5614
  * https://www.w3.org/WAI/ARIA/apg/practices/read-me-first/
* Google Tag Manager

https://devco.re/blog/2014/06/19/client-ip-detection/
