# php-url-extractor

Extract URLs from HTML content, optionally filtering for local and/or file URLs.

## Installation

Install with Composer:

```
$ composer require chrisullyott/php-url-extractor dev-master
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
(
    [0] => stdClass Object
        (
            [attribute] => href
            [value] => /_assets/img/icons/favicon-96.png
            [url] => https://www.site.com/_assets/img/icons/favicon-96.png
        )

    ...
```
