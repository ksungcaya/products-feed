<?php

use Prewk\XmlStringStreamer;
use App\Http\Feed\XmlFeedReader;
use App\Http\Feed\XmlFeedStream;
use Prewk\XmlStringStreamer\Stream\Guzzle;
use Prewk\XmlStringStreamer\Parser\UniqueNode;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class XmlFeedReaderTest extends TestCase
{
     /**
     * @var string
     */
    protected $feedUrl = 'http://pf.tradetracker.net/?aid=1&type=xml&encoding=utf-8&fid=251713&categoryType=2&additionalType=2&limit=5';

    /**
     * @var \App\Http\Feed\XmlFeedStream
     */
    protected $stream;

    /**
     * @var \App\Http\Feed\XmlFeedReader
     */
    protected $feedReader;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->stream = $this->getMockBuilder(XmlFeedStream::class)
                             ->setMethods(['createGuzzleStream', 'createStreamer'])
                             ->getMock();

        $this->feedReader = $this->getMockBuilder(XmlFeedReader::class)
                                 ->setConstructorArgs([$this->stream])
                                 ->getMockForAbstractClass();
    }

    /** @test */
    public function it_validates_a_url()
    {
        $guzzleStreamStub = $this->getMockBuilder(Guzzle::class)
                                 ->setConstructorArgs([$this->feedUrl])
                                 ->setMethods(['getChunk'])
                                 ->getMock();

        $guzzleStreamStub->expects($this->once())
                         ->method('getChunk')
                         ->willReturn('<?xml-blablablah');

        $this->stream->expects($this->once())
                     ->method('createGuzzleStream')
                     ->willReturn($guzzleStreamStub);

        $this->assertTrue(
            $this->feedReader->isValidUrl($this->feedUrl)
        );
    }

    /** @test */
    public function it_process_feed_from_a_given_url()
    {
        // mock the streamer that will be called
        $streamerStub = $this->getMockBuilder(XmlStringStreamer::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['getNode'])
                         ->getMock();

        // we dont want to throw an error if nodeToExtract is not present.
        $this->feedReader->nodeToExtract = 'node';

        $this->stream->expects($this->once())
                     ->method('createStreamer')
                     ->with($this->feedUrl, 'node')
                     ->willReturn($streamerStub);

        $nodeStub = 'xml-data-strings';

        // simulate getting node process..
        $streamerStub->expects($this->at(0))
                     ->method('getNode')
                     ->willReturn($nodeStub);

        // stop the iteration please..
        $streamerStub->expects($this->at(1))
                     ->method('getNode')
                     ->willReturn(null);

        $this->feedReader->expects($this->atLeastOnce())
                         ->method('createPayloadFrom')
                         ->with($nodeStub)
                         ->willReturn(['xml', 'payload']);

        File::shouldReceive('exists')
                ->atLeast()->once()
                ->andReturn(true);

        // make sure that the serialized node will be saved to a file.
        File::shouldReceive('append')
                ->atLeast()->once()
                ->andReturn(true);

        // act it!
        $this->feedReader->setFeedDirectory('fake-existing-dir')
                         ->processFromUrl($this->feedUrl);
    }

    /** @test */
    public function it_creates_temp_directory_if_not_exists()
    {
        File::shouldReceive('exists')->once()->andReturn(false);
        File::shouldReceive('makeDirectory')->once();
        File::shouldReceive('cleanDirectory')->once();

        $this->feedReader->createTempDirectory();
    }

    /** @test */
    public function it_sets_the_feed_directory()
    {
        $this->feedReader->setFeedDirectory('feed-dir');

        $this->assertEquals('feed-dir', $this->feedReader->getFeedDirectory());
    }

    /** @test */
    public function it_gets_a_page_file_and_return_unserialized_array_if_file_exists()
    {
        $fakeData = ['unserialized', 'array', 'yeah', 'fake'];

        File::shouldReceive('exists')->andReturn(true);
        File::shouldReceive('get')->once()->andReturn(serialize($fakeData));

        $data = $this->feedReader->setFeedDirectory('fake-dir')->getPage(1);

        $this->assertSame($fakeData, $data);
    }

}
