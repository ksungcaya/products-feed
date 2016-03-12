var elixir = require('laravel-elixir');

var paths = {
    bower: "resources/assets/bower/"
};

elixir(function(mix) {
    mix.sass('main.scss');

    mix.scripts([
        '../bower/jquery/dist/jquery.js',
        '../bower/remodal/dist/remodal.min.js'
    ], 'public/js/vendor.js');

    mix.scripts(['feed.js'], 'public/js/feed.js');
});