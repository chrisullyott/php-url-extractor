# php-url-extractor

Extract URLs from HTML content, optionally filtering for local and/or file URLs.

## Installation

Install with Composer:

```
"require": {
    "chrisullyott/php-url-extractor": "dev-master"
}
```

## Usage

```
$html = file_get_contents('about-us.html');

$extractor = new UrlExtractor($html);
$extractor->setHomeUrl('http://www.site.com');
$extractor->setFilesOnly(true);

$urls = $extractor->getUrls();
print_r($urls);
```

```
Array
(
    [0] => http://www.site.com/images/billboard.jpg
    [1] => http://www.site.com/docs/presentation.pdf
    [2] => http://www.site.com/assets/js/global.js
)
```
