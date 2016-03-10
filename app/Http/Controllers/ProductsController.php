<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * Display the product feed form.
     *
     * @return Response
     */
    public function index()
    {
        return view('products.index');
    }

    /**
     * Display the processed products feed.
     *
     * @param  Request $request 
     *
     * @return Response
     */
    public function feed(Request $request)
    {
        $url = $request->get('url');

        return view('products.feed', compact('url'));
    }
}
