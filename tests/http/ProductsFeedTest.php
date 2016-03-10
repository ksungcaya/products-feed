<?php

use App\Http\Feed\ProductsFeed;
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
    public function setUp()
    {
        parent::setUp();

        $this->productsFeed = App::make(ProductsFeed::class);
    }

    /** 
     * @vcr feed.yaml
     * @test
     */
    public function it_validates_a_feed_url()
    {
        $invalidUrl = 'http://qwerty.com';

        $invalid = $this->productsFeed->isValidUrl($invalidUrl);
        $this->assertFalse($invalid);

        $invalidUrl2 = 'http://twitter.com';

        $invalid2 = $this->productsFeed->isValidUrl($invalidUrl2);
        $this->assertFalse($invalid2);

        $valid = $this->productsFeed->isValidUrl($this->feedUrl);
        $this->assertTrue($valid);
    }
}
