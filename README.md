## subtitle-ocr

### 功用：把影片截圖圖檔的字幕部份做OCR（光學文字辨識）並作為檔案命名

### 環境需求
 - PHP 7.1
 - Composer
 - php-imagick
 - TesseractOCR & 繁體中文支援檔

### 使用方式
 - 第一次執行前先composer install
 - 把圖檔放置在img目錄
 - 跑 php ocr.php
 - 以字幕命名的檔案會放置在img_ocr目錄下，辨識出文字的檔案會移到img_hit目錄下，辨識不出文字的檔案會移到img_miss下。

