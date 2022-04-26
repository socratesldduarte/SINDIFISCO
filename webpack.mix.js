const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .copy('resources/sass/bootstrap.css', 'public/css/bootstrap.css')
    .copy('resources/sass/signin.css', 'public/css/signin.css')
    .copy('resources/sass/smart_wizard.css', 'public/css/smart_wizard.css')
    .copy('resources/sass/smart_wizard_theme_arrows.css', 'public/css/smart_wizard_theme_arrows.css')
    .copy('resources/js/jquery.js', 'public/js/jquery.js')
    .copy('resources/js/bootstrap.bundle.js', 'public/js/bootstrap.bundle.js')
    .copy('resources/js/jquery.smartWizard.js', 'public/js/jquery.smartWizard.js')
    .copy('resources/js/bootstrap-session-timeout.js', 'public/js/bootstrap-session-timeout.js')
    .copy('resources/sindifisco.png', 'public/img/sindifisco.png');
