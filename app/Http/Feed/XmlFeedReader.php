<?php

namespace App\Http\Feed;

use File;
use XMLReader;
use Verdant\XML2Array;
use Prewk\XmlStringStreamer;
use App\Exceptions\FeedDirectoryException;
use Prewk\XmlStringStreamer\Stream\Guzzle;
use Prewk\XmlStringStreamer\Parser\UniqueNode;
use App\Exceptions\MissingNodeToExtractException;

abstract class XmlFeedReader
{
    /**
     * The size in bytes to be streamed at a time.
     *
     * @var integer
     */
    protected $chunkSize = 4096;

    /**
     * The counter for the set that was processed.
     *
     * @var integer
     */
    protected $setCount = 0;

    /**
     * The limit of records to be processed as a set.
     * 
     * @var integer
     */
    protected $limit = 30;

    /**
     * The name of the directory where the processed data are stored.
     *
     * @var string
     */
    protected $feedDirectory;

    /**
     * Attempt to validate the feed url.
     *
     * @param  string  $url
     *
     * @return boolean
     */
    public function isValidUrl($url)
    {
        $stream = $this->createGuzzleStream($url);
        $chunk = trim($stream->getChunk());

        if ( ! $chunk) return false;

        return (strpos($chunk, '<?xml') === 0);
    }

    /**
     * Process the feed from the given url.
     *
     * @param  string $url
     *
     * @return void
     */
    public function processFromUrl($url)
    {
        $streamer = $this->createStreamer($url);
        $dataCounter = 0;
        $data = [];

        while ($node = $streamer->getNode()) {
            $data[] = $this->createPayloadFrom($node);

            if (++$dataCounter === $this->limit) {
                $this->saveData($data);

                $dataCounter = 0;
                $data = [];

                sleep(1);
                continue;
            }
        }

        $this->saveLastSetOfData($data);
    }

    /**
     * Create a temporary directory to store the processed xml data.
     *
     * @param string $name
     *  
     * @return string
     */
    public function createTempDirectory($name = '')
    {
        $name = $name ?: $this->generateName();
        $path = 'root';
        $path = $this->feedPath($name);

        if (! File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        return $name;
    }

    /**
     * Setter for the feed directory property.
     *
     * @param string $directory
     *
     * @return $this
     */
    public function setFeedDirectory($directory)
    {
        $this->feedDirectory = $directory;

        return $this;
    }

    /**
     * Get the feed file as per the page given.
     *
     * @param  int $page
     *
     * @return array|bool
     */
    public function getPage($page)
    {
        $feedDirectory = $this->getFeedDirectoryPath();
        $path = $feedDirectory . DIRECTORY_SEPARATOR . $page;

        if (! File::exists($path)) {
            return false;
        }

        return unserialize(File::get($path));
    }

    /**
     * Attempt to convert an xml string/file to array.
     *
     * @param  string|file $xml
     *
     * @return array
     */
    protected function xmlToArray($xml)
    {
        return XML2Array::createArray($xml);
    }

    /**
     * Save the processed data to a file.
     *
     * @param  array $data
     *
     * @return void
     */
    protected function saveData($data = [])
    {
        if (count($data)) {
            $filePath = $this->getFeedDirectoryPath() . '/' . ++$this->setCount;

            File::append($filePath, serialize($data));
        }
    }

    /**
     * Wrapper for saving the last set of data.
     *
     * @param  array  $data
     *
     * @return void
     */
    protected function saveLastSetOfData($data = [])
    {
        return $this->saveData($data);
    }

    /**
     * Get the absolute current feed directory path.
     *
     * @return string
     *
     * @throws \App\Exceptions\FeedDirectoryException
     */
    protected function getFeedDirectoryPath()
    {
        $directory = $this->feedDirectory;

        if (is_null($directory)) {
            throw new FeedDirectoryException('No feed directory provided.');
        }

        $path = $this->feedPath($directory);

        if (! File::exists($path)) {
            throw new FeedDirectoryException('Feed directory does not exist.');
        }

        return $path;
    }

    /**
     * Convert the parsed node (string) to array.
     *
     * @param  string $node
     *
     * @return array
     */
    protected abstract function createPayloadFrom($node);

    /**
     * Create a streamer instance.
     *
     * @param  string $url
     *
     * @return \Prewk\XmlStringStreamer
     */
    protected function createStreamer($url)
    {
        $stream = $this->createGuzzleStream($url);
        $parser = new UniqueNode(["uniqueNode" => $this->getNodeToExtract()]);

        return new XmlStringStreamer($parser, $stream);
    }

    /**
     * Create a guzzle stream instance.
     *
     * @param  string $url
     *
     * @return \Prewk\XmlStringStreamer\Stream\Guzzle
     */
    protected function createGuzzleStream($url)
    {
        return new Guzzle($url, $this->chunkSize);
    }

    /**
     * A feed should have a name of node to be extracted.
     *
     * @return string
     *
     * @throws \App\Exceptions\MissingNodeToExtractException
     */
    protected function getNodeToExtract()
    {
        if (is_null($this->nodeToExtract)) {
            throw new MissingNodeToExtractException('Missing nodeToExtract property.');
        }

        return $this->nodeToExtract;
    }

    /**
     * Generate a random name.
     *
     * @return string
     */
    protected function generateName()
    {
        return substr(md5(time()), 0, 20);
    }

    /**
     * The absolute path of the feeds from the server.
     *
     * @param  string $directory
     *
     * @return string
     */
    protected function feedPath($directory = '')
    {
        $basePath  = storage_path('app/feed');
        $directory = $directory ? DIRECTORY_SEPARATOR.$directory : $directory;

        return $basePath . $directory;
    }
}
