<?php

/**
 * Parses HTML with DOMDocument and reads URLs from a whitelist of attributes.
 * Provide the site's homepage URL to get absolute local URLs.
 *
 * @author Chris Ullyott <contact@chrisullyott.com>
 */
class UrlExtractor
{
    /**
     * The HTML content.
     *
     * @var string
     */
    private $content = '';

    /**
     * A homepage url, like "http://php.net".
     *
     * @var string
     */
    private $homeUrl = '';

    /**
     * Whether to extract only file URLs.
     *
     * @var boolean
     */
    private $filesOnly = false;

    /**
     * An array of HTML tag attributes to read.
     *
     * @var array
     */
    private $attributeFilter = array();

    /**
     * Default array of HTML attributes to read.
     *
     * @var array
     */
    private static $defaultAttributeFilter = array(
        'src',
        'href',
        'poster'
    );

    /**
     * @var DOMDocument
     */
    private $dom;

    /**
     * @param string $content
     */
    public function __construct($content)
    {
        $this->setContent($content)
             ->setAttributeFilter(static::$defaultAttributeFilter);
    }

    /**
     * @param string $content
     * @return self
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $homeUrl
     * @return self
     */
    public function setHomeUrl($homeUrl)
    {
        if (!self::isAbsoluteUrl($homeUrl)) {
            throw new Exception('homeUrl must be an absolute URL');
        }

        $this->homeUrl = rtrim($homeUrl, '/');

        return $this;
    }

    /**
     * @return string
     */
    public function getHomeUrl()
    {
        return $this->homeUrl;
    }

    /**
     * @param boolean $filesOnly
     * @return self
     */
    public function setFilesOnly($filesOnly)
    {
        $this->filesOnly = $filesOnly;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getFilesOnly()
    {
        return $this->filesOnly;
    }

    /**
     * @param array $attributeFilter
     * @return self
     */
    public function setAttributeFilter(array $attributeFilter)
    {
        if (!$attributeFilter) {
            $attributeFilter = static::$defaultAttributeFilter;
        }

        $this->attributeFilter = $attributeFilter;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributeFilter()
    {
        return $this->attributeFilter;
    }

    /**
     * @param string $html
     * @return DOMDocument
     */
    private function createDom($html)
    {
        // Prevent HTML errors from bubbling up.
        libxml_use_internal_errors(true);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $dom->loadHTML($html);

        return $dom;
    }

    /**
     * @return DOMDocument
     */
    private function getDom()
    {
        if (!$this->dom) {
            $this->dom = $this->createDom($this->content);
        }

        return $this->dom;
    }

    /**
     * @return array
     */
    public function getUrls()
    {
        $urls = array();

        foreach ($this->getDom()->getElementsByTagName('*') as $node) {
            if ($node->hasAttributes()) {
                foreach ($node->attributes as $attr) {
                    if ($this->isDesiredNode($attr)) {
                        $value = trim($attr->nodeValue);

                        if ($this->isDesiredUrl($value)) {
                            $urls[] = $value;
                        }
                    }
                }
            }
        }

        return $urls;
    }

    /**
     * @return array
     */
    public function getAbsoluteUrls()
    {
        $method = array($this, 'makeAbsoluteUrl');
        $urls = array_filter(array_map($method, $this->getUrls()));

        return array_values($urls);
    }

    /**
     * @param DOMNode $node
     * @return boolean
     */
    private function isDesiredNode(DOMNode $node)
    {
        return in_array($node->nodeName, $this->getAttributeFilter());
    }

    /**
     * @param string $url
     * @return boolean
     */
    private function isDesiredUrl($url)
    {
        if (!self::isUrl($url)) {
            return false;
        }

        if ($this->getHomeUrl() && !self::isLocalUrl($url, $this->getHomeUrl())) {
            return false;
        }

        if ($this->getFilesOnly() && !self::isFileUrl($url)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $url
     * @return string
     */
    private function makeAbsoluteUrl($url)
    {
        if (!$this->getHomeUrl()) {
            return null;
        }

        if (self::isSchemeAgnosticUrl($url)) {
            return parse_url($this->getHomeUrl(), PHP_URL_SCHEME) . ":{$url}";
        }

        if (self::isRelativeUrl($url)) {
            return $this->getHomeUrl() . $url;
        }

        return null;
    }

    /**
     * @param string $url
     * @param string $homeUrl
     * @return boolean
     */
    private static function isLocalUrl($url, $homeUrl)
    {
        if (self::isRelativeUrl($url) && !self::isSchemeAgnosticUrl($url)) {
            return true;
        }

        $urlDomain = self::stripWWW(parse_url($url, PHP_URL_HOST));
        $localDomain = self::stripWWW(parse_url($homeUrl, PHP_URL_HOST));

        return $urlDomain === $localDomain;
    }

    /**
     * @param string $url
     * @return boolean
     */
    private static function isFileUrl($url)
    {
        $path = parse_url($url, PHP_URL_PATH);

        return (bool) pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * @param string $url
     * @return boolean
     */
    private static function isRelativeUrl($url)
    {
        return substr($url, 0, 1) === '/';
    }

    /**
     * @param string $url
     * @return boolean
     */
    private static function isSchemeAgnosticUrl($url)
    {
        return substr($url, 0, 2) === '//';
    }

    /**
     * @param string $url
     * @return boolean
     */
    private static function isAbsoluteUrl($url)
    {
        return (bool) filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * @param string $url
     * @return boolean
     */
    private function isUrl($url)
    {
        return self::isRelativeUrl($url) || self::isAbsoluteUrl($url);
    }

    /**
     * @param string $string
     * @return string
     */
    private static function stripWWW($string)
    {
        return preg_replace('/^www\./', '', $string);
    }

    /**
     * @param string $string
     * @param string $wrapper
     * @return string
     */
    private static function wrap($string, $wrapper)
    {
        return $wrapper . $string . $wrapper;
    }
}