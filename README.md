# php-url-extractor

Extract URLs from HTML content, applying optional filters.

## Installation

With [Composer](https://getcomposer.org/):

```
$ composer require chrisullyott/php-url-extractor
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

## Options

### setAttributeFilter _(array)_

The `#getUrls` method creates a [DOMDocument](http://php.net/manual/en/class.domdocument.php) and checks given element attributes, such as `src` and `href`, for URLs you might be interested in. Use `#setAttributeFilter` to override the default set of attributes with your own.

### setHomeUrl _(string)_

Providing a home URL filters results to those local to the domain. Any relative URL beginning with one slash `/` and not two slashes is considered local as well. Setting this also builds the `url` property (an absolute URL) for the objects returned by the `#getUrls` method.

### setAlternateDomains _(array)_

Used with `#setHomeUrl`. If set, the returned URLs will include those whose domain is found in the array. In this array, you may enter strings, like  `media.site.com` and/or regular expressions, like `/.*\.site\.com/`.

### setFilesOnly _(boolean)_

Whether we should only return URLs with file extensions.

### setIgnoredExtensions _(array)_

Used with `#setFilesOnly`. Excludes URLs whose file extension is found in the array.

