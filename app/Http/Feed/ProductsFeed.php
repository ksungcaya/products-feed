<?php

namespace App\Http\Feed;

use App\Http\Feed\XmlFeedReader;

class ProductsFeed extends XmlFeedReader
{
    /**
     * The name of the node from the xml to be extracted.
     *
     * @var string
     */
    protected $nodeToExtract = 'product';

    /**
     * The limit of records to be processed as a set.
     * 
     * @var integer
     */
    protected $limit = 32;

    /**
     * Get a product by its id.
     *
     * @param  integer $productId
     * @param  integer $page
     *
     * @return array|bool
     */
    public function getByProductId($productId, $page)
    {
        if ($products = $this->getPage($page)) {
            return array_first($products, function ($key, $value) use ($productId) {
                return $value['productId'] == $productId;
            });
        }

        return false;
    }

    /**
     * Create a product payload from the processed node.
     *
     * @param  string $node
     *
     * @return array
     */
    protected function createPayloadFrom($node)
    {
        $product = $this->xmlToArray($node)['product'];

        return [
            'productId'   => $product['productID'],
            'name'        => $product['name'],
            'description' => $this->extractDescription($product['description']),
            'price'       => $product['price']['@value'],
            'currency'    => $product['price']['@attributes']['currency'],
            'categories'  => $this->extractCategories($product['categories']),
            'productUrl'  => $product['productURL'],
            'imageUrl'    => $product['imageURL']
        ];
    }

    /**
     * Check if the description node is a cdata then
     * attempt to extract it, else return it as is.
     *
     * @param  array|string $description
     *
     * @return string
     */
    private function extractDescription($description)
    {
        if ($description = $description['@cdata']) {
            return $description;
        }

        return $description;
    }

    /**
     * Attempt to extract the categories from the node
     * while ignoring the attributes.
     *
     * @param  array|string $categories
     *
     * @return array
     */
    private function extractCategories($categories)
    {
        $result = [];

        if ($categories) {
            foreach (array_values($categories) as $category) {
                $result[] = $category['@value'];
            }
        }

        return $result;
    }
}
