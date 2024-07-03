// webpack.mix.js
// Using for Laravel Mix to Hot Reload compile assets for tailwindcss and sass
let mix = require('laravel-mix');
const purgecss = require('@fullhuman/postcss-purgecss');
const cssnano = require('cssnano');
const preset = require('cssnano-preset-advanced');

//glob.sync('assets/js/**/*.js').forEach(file => {
  //  mix.js(file, 'dist/js');
//});

processFile('assets/css/admin.sass');
processFile('assets/css/global.sass');
processFile('assets/css/login.sass');

function processFile(file) {
    mix.sass(file, 'dist/css')
        .options({
            postCss: [
                require('tailwindcss'),
                require('autoprefixer'),
                purgecss({
                    content: ['./admin/**/*.php', './login/**/*.php', './index.php', './assets/js/**/*.js'],
                    defaultExtractor: content => content.match(/[\w-/:]+(?<!:)/g) || []
                }),
                cssnano({
                    preset: preset
                })
            ],
        });
}