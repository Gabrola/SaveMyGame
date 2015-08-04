var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.sass('app.scss');

    mix.scripts([
        '../bower/jquery/dist/jquery.js',
        '../bower/materialize/dist/js/materialize.js',
        'app.js'
    ]);

    mix.version(["css/app.css", "js/all.js"]);

    mix.copy('resources/assets/bower/materialize/font', 'public/build/font');
});
