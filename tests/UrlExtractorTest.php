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
     * @depends testGetUrls
     */
    public function testGetAbsoluteUrls()
    {
        $urls = $this->urlExtractor->getAbsoluteUrls();

        $this->assertTrue(count($urls) > 3);
    }

    /**
     * The absolute URLs returned should all be valid.
     */
    public function testAbsoluteUrlsAreValid()
    {
        $urls = $this->urlExtractor->getAbsoluteUrls();

        foreach ($urls as $url) {
            $isValid = (bool) filter_var($url, FILTER_VALIDATE_URL);
            $this->assertTrue($isValid);
        }
    }

    /**
     * File extensions are filtered.
     */
    public function testIgnoredExtensions()
    {
        $ignoredExtensions = array('jpg', 'jpeg', 'png', 'gif');

        $this->urlExtractor->setIgnoredExtensions($ignoredExtensions);

        $urls = $this->urlExtractor->getUrls();

        foreach ($urls as $url) {
            $ext = strtolower(strtok(pathinfo($url, PATHINFO_EXTENSION), '/'));
            $this->assertTrue(!in_array($ext, $ignoredExtensions));
        }

        $this->urlExtractor->setIgnoredExtensions(array());
    }
}
