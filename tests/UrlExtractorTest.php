<?php

require_once '../src/UrlExtractor.php';

use PHPUnit\Framework\TestCase;

class UrlExtractorTest extends TestCase
{
    /**
     * @var UrlExtractor
     */
    private $urlExtractor;

    /**
     * @var string
     */
    private $file = 'page.html';

    /**
     * @return string
     */
    private function getHtml()
    {
        return file_get_contents($this->file);
    }

    /**
     * @return UrlExtractor
     */
    private function getUrlExtractor()
    {
        if (!$this->urlExtractor) {
            $this->urlExtractor = new UrlExtractor($this->getHtml());
            $this->urlExtractor->setHomeUrl('https://en.wikipedia.org');
            $this->urlExtractor->setFilesOnly(true);
        }

        return $this->urlExtractor;
    }

    /**
     * The home URL should only include links with the scheme defined.
     */
    public function testSetHomeUrlException()
    {
        $this->expectException(Exception::class);

        $this->getUrlExtractor()->setHomeUrl('www.bad-url.com');
    }

    /**
     * We should get multiple URLs from the content.
     */
    public function testGetUrls()
    {
        $urls = $this->getUrlExtractor()->getUrls();

        $this->assertTrue(count($urls) > 3);
    }

    /**
     * @depends testGetUrls
     */
    public function testGetAbsoluteUrls()
    {
        $urls = $this->getUrlExtractor()->getAbsoluteUrls();

        $this->assertTrue(count($urls) > 3);
    }

    /**
     * The absolute URLs returned should all be valid.
     */
    public function testAbsoluteUrlsAreValid()
    {
        $urls = $this->getUrlExtractor()->getAbsoluteUrls();

        foreach ($urls as $url) {
            $isValid = (bool) filter_var($url, FILTER_VALIDATE_URL);
            $this->assertTrue($isValid);
        }
    }
}
