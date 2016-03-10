<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Feed\ProductsFeed;

class ProductsFeedController extends Controller
{
    /**
     * @var \App\Http\Feed\ProductsFeed
     */
    protected $productsFeed;

    /**
     * Create ProductsController instance.
     *
     * @param ProductsFeed $productsFeed
     */
    public function __construct(ProductsFeed $productsFeed)
    {
        $this->productsFeed = $productsFeed;
    }

    /**
     * Process feed from the given url.
     *
     * @param  Request $request 
     *
     * @return Response
     */
    public function process(Request $request)
    {
        $directory = $request->get('feed_directory');
        $url = $request->get('url');

        if ( ! $this->productsFeed->isValidUrl($url)) {
            return $this->respondWithError('The feed url is invalid.');
        }

        if (! $directory) {
            return $this->respond([
                'feed_directory' => $this->productsFeed->createTempDirectory()
            ]);
        }

        $this->productsFeed->setFeedDirectory($directory)->processFromUrl($url);

        return $this->respond(['success' => true]);
    }

    /**
     * Request a feed file to be displayed.
     *
     * @param  Request $request
     *
     * @return Response
     */
    public function display(Request $request)
    {
        $feed_directory = $request->get('feed_directory');
        $page = $request->get('page');

        $products = $this->productsFeed
                         ->setFeedDirectory($feed_directory)
                         ->getPage($page);

        return $this->respond(compact('products', 'feed_directory', 'page'));
    }
}
