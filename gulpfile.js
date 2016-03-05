var elixir = require('laravel-elixir');

var paths = {
    bower: "resources/assets/bower/"
};

elixir(function(mix) {
    mix.sass('main.scss');
});