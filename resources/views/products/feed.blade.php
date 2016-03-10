@extends('layouts.app')

@section('content')
    <div class="products__feed">
    
        <h3 class="products__feed__header">Products feed from: 
            <a href="{{ $url }}" 
                id="feed-url" 
                class="product__feed__url"
                title="{{ $url }}"
            >{{ $url }}</a>
        </h3>

        <hr />
    </div>
    
    <span class="loader" hidden></span>
@endsection

@section('scripts')
    <script src="/js/main.js"></script>
@endsection