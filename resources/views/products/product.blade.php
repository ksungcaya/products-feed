@extends('layouts.app')

@section('content')
    
    <div class="products">
        <div class="product">
            <a href="{{ $product->productUrl }}" class="product__link" target="__blank">
                <img src="{{ $product->imageUrl }}" alt="{{ $product->name }}" class="product__image">
            </a>
            <div class="product__meta">
                <a href="{{ $product->productUrl }}" class="product__name" target="__blank" title="{{ $product->name }}">
                    {{ $product->name }}
                </a>
                <div class="product__price">
                    <span class="product__price__value">Price: {{ $product->price }}</span>
                    <span class="product__price__currency">{{ $product->currency }}</span>
                </span>
                </div>
                <span class="product__categories">Categories: {{ implode(', ', $product->categories) }}</span>
            </div>
        </div>
    </div>
    
@endsection