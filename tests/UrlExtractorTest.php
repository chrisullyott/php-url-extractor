<?php

use PHPUnit\Framework\TestCase;

class UrlExtractorTest extends TestCase
{
    /**
     * @var UrlExtractor
     */
    public $urlExtractor;

    /**
     * @var string
     */
    public $file = __DIR__ . DIRECTORY_SEPARATOR . 'page.html';

    /**
     * Set up.
     */
    public function setUp()
    {
        $this->urlExtractor = new UrlExtractor($this->getHtml());
        $this->urlExtractor->setHomeUrl('http://mk036.monkpreview.com');
        $this->urlExtractor->setFilesOnly(true);
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return file_get_contents($this->file);
    }

    /**
     * The home URL should be an absolute URL.
     */
    public function testSetHomeUrlException()
    {
        $this->expectException(Exception::class);

        $this->urlExtractor->setHomeUrl('www.bad-url.com');
    }

    /**
     * We should get multiple URLs from the content.
     */
    public function testGetUrls()
    {
        $urls = $this->urlExtractor->getUrls();

        $this->assertTrue(count($urls) > 3);
    }

    /**
     * The absolute URLs returned should all be valid.
     */
    public function testAbsoluteUrlsAreValid()
    {
        $urls = $this->urlExtractor->getUrls();

        foreach ($urls as $url) {
            $isValid = (bool) filter_var($url->url, FILTER_VALIDATE_URL);
            $this->assertTrue($isValid);
        }
    }

    /**
     * Our URLs should include items from our alternate domain patterns.
     */
    public function testUseOfDomainPatterns()
    {
        $successful = false;
        $domainPattern = '/.*\.rackcdn\.com/';
        $this->urlExtractor->setAlternateDomains([$domainPattern]);
        $urls = $this->urlExtractor->getUrls();

        foreach ($urls as $url) {
            $host = parse_url($url->url, PHP_URL_HOST);

            if (preg_match($domainPattern, $host)) {
                $successful = true;
                break;
            }
        }

        $this->assertTrue($successful);
        $this->urlExtractor->setAlternateDomains([]);
    }

    /**
     * File extensions are filtered.
     */
    public function testIgnoredExtensions()
    {
        $ignoredExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        $this->urlExtractor->setIgnoredExtensions($ignoredExtensions);

        $urls = $this->urlExtractor->getUrls();

        foreach ($urls as $url) {
            $path = parse_url($url->value, PHP_URL_PATH);
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            $this->assertTrue(!in_array($ext, $ignoredExtensions));
        }

        $this->urlExtractor->setIgnoredExtensions([]);
    }
}
