# php-website-template

## 目標

- 無障礙
- Schema.org （不選用 JSON-LD ，因資料會重複）
- 能靜態的就靜態化
- [網址標準化](https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls?hl=zh-tw)，例如 `?b=3&a=4&utm_xxxx=yyy` 統一成 `?a=4&b=3` ，以利：
  - 同一頁的 `og:url` 一致
    →這個只要改 HTML
  - 就算設定 `Referrer-Policy: unsafe-url` 也不致洩漏個資。（但最低等級至少設為 `no-referrer-when-downgrade` 為妥）
    →這個需要前端的 `history.replaceState()`
- 暫不建立 sitemap 。Google 提到：
  > 以下是可能「不」需要 Sitemap 的情況：
  > - 網站規模很「小」：所謂的規模很「小」，指的是網站的網頁數不超過 500 個 (以您認為有需要納入搜尋結果的網頁為準)。
  > - 網站內部的連結完善：這表示 Google 可以透過首頁的連結找出網站上所有的重要網頁。

  --- [什麼是 Sitemap | Google 搜尋中心  |  說明文件  |  Google Developers](https://developers.google.com/search/docs/crawling-indexing/sitemaps/overview?hl=zh-tw)

  若有需要的話，可用 `.htaccess` 將動態生成的地圖偽裝成靜態檔案。


## Directory Structure

- `admin`: 網站後台。
- `assets`: 靜態檔案，後台也改不了。
- `lib`: 函式庫。
- `var`:
  藉由後台可以修改的檔案。
  開發環境和生產環境唯一不一樣的地方。
  **手動更新至生產環境時切勿覆蓋此目錄。**
