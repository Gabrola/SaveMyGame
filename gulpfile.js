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

    mix.styles([
    ], 'public/css/vendor.css');

    mix.styles([
        '../../../public/css/vendor.css',
        '../../../public/css/app.css'
    ], 'public/css/all.css');

    mix.scripts([
        '../bower/jquery/dist/jquery.js',
        '../bower/materialize/dist/js/materialize.js',
        '../bower/zeroclipboard/dist/ZeroClipboard.js',
        '../bower/qtip2/jquery.qtip.js',
        '../bower/jquery.scrollTableBody/src/jquery.scrollTableBody-1.0.1.js',
        '../bower/slimScroll/jquery.slimscroll.js',
        'app.js'
    ]);

    mix.version(["css/all.css", "js/all.js"]);

    mix.copy('resources/assets/bower/materialize/font', 'public/build/font');
    mix.copy('resources/assets/bower/zeroclipboard/dist/ZeroClipboard.swf', 'public/build/js/ZeroClipboard.swf');
});
