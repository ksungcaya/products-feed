@extends('layouts.app')

@section('content')
    
    <div class="form-center form__feed">

        <h2 class="form__feed__header">Enter product feed url:</h2>

        <form action="/products/feed">
            <div class="form-group">
                <input 
                    type="url"
                    id="url" 
                    name="url" 
                    class="form-control form__feed__text" 
                    placeholder="http://example.com" 
                    value="{{ old('url') }}"
                >
            </div>

            <button type="submit" class="btn btn-primary form__feed__btn">Submit</button>
        </form>    
    </div>

@stop