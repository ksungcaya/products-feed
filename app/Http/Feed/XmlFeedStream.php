<?php

namespace App\Http\Feed;

use Prewk\XmlStringStreamer;
use Prewk\XmlStringStreamer\Stream\Guzzle;
use Prewk\XmlStringStreamer\Parser\UniqueNode;

class XmlFeedStream
{
    /**
     * The size in bytes to be streamed at a time.
     * 
     * @var integer
     */
    protected $chunkSize = 4096;

    /**
     * Chunk size setter.
     *
     * @param int $chunkSize
     *
     * @return $this
     */
    public function setChunkSize($chunkSize)
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    /**
     * Create a streamer instance.
     *
     * @param  string $url
     * @param  string $nodeToExtract
     *
     * @return \Prewk\XmlStringStreamer
     */
    public function createStreamer($url, $nodeToExtract)
    {
        $stream = $this->createGuzzleStream($url);
        $parser = new UniqueNode(["uniqueNode" => $nodeToExtract]);

        return new XmlStringStreamer($parser, $stream);
    }

    /**
     * Create a guzzle stream instance.
     *
     * @param  string $url
     *
     * @return \Prewk\XmlStringStreamer\Stream\Guzzle
     */
    public function createGuzzleStream($url)
    {
        return new Guzzle($url, $this->chunkSize);
    }
}