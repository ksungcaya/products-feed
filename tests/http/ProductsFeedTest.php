<?php

use Prewk\XmlStringStreamer;
use org\bovigo\vfs\vfsStream;
use App\Http\Feed\ProductsFeed;
use org\bovigo\vfs\vfsStreamWrapper;
use App\Exceptions\FeedDirectoryException;
use Prewk\XmlStringStreamer\Stream\Guzzle;
use Prewk\XmlStringStreamer\Parser\UniqueNode;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductsFeedTest extends TestCase
{
    /**
     * @var string
     */
    protected $feedUrl = 'http://pf.tradetracker.net/?aid=1&type=xml&encoding=utf-8&fid=251713&categoryType=2&additionalType=2&limit=5';

    /**
     * @var App\Http\ProductsFeed
     */
    protected $productsFeed;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->productsFeed = App::make(ProductsFeed::class);
    }

    /** 
     * @test
     * @vcr feed.yaml
     */
    public function it_validates_a_feed_url()
    {
        $invalidUrl = 'http://qwerty.com';

        $invalid = $this->productsFeed->isValidUrl($invalidUrl);
        $this->assertFalse($invalid);

        // sorry twitter you're invalid :P
        $invalidUrl2 = 'http://twitter.com';

        $invalid2 = $this->productsFeed->isValidUrl($invalidUrl2);
        $this->assertFalse($invalid2);

        $valid = $this->productsFeed->isValidUrl($this->feedUrl);
        $this->assertTrue($valid);
    }

    /** 
     * @test
     * @vcr products.yaml
     * @expectedException \App\Exceptions\FeedDirectoryException
     */
    public function it_throws_a_feed_directory_exception_if_the_directory_provided_does_not_exists()
    {
        $this->productsFeed->setFeedDirectory('some-random-directory')
                           ->processFromUrl($this->feedUrl);
    }

    /** 
     * The reader should set a feed directory first before processing the feed.
     * 
     * @test
     * @vcr products.yaml
     * @expectedException \App\Exceptions\FeedDirectoryException
     */
    public function it_throws_a_feed_directory_exception_if_the_directory_was_not_set_first()
    {
        $this->productsFeed->processFromUrl($this->feedUrl);
    }

}
