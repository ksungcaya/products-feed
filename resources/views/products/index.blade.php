@extends('layouts.app')

@section('content')
    
    <div class="form-center form__feed">

        <h2 class="form__feed__header">Enter feed url:</h2>

        <form action="/products/feed" method="GET">

            <div class="form-group">
                <input 
                    type="url"
                    id="url" 
                    name="url" 
                    class="form-control form__feed__text" 
                    placeholder="http://example.com" 
                    value="http://pf.tradetracker.net/?aid=1&type=xml&encoding=utf-8&fid=251713&categoryType=2&additionalType=2&limit=100"
                >
            </div>

            <button type="submit" class="btn btn-primary form__feed__btn">Submit</button>
        </form>    
    </div>
@endsection
