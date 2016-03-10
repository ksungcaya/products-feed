var elixir = require('laravel-elixir');

var paths = {
    bower: "resources/assets/bower/"
};

elixir(function(mix) {
    mix.sass('main.scss');

    mix.scripts([
        '../bower/jquery/dist/jquery.js',
        'feed.js'
    ], 'public/js/main.js');
});