let mix = require('laravel-mix')

require('./nova.mix')

mix.setPublicPath('dist')
  .js('resources/js/field.js', 'js')
  .css('resources/css/field.css', 'css')
  .options({
    processCssUrls: false,
    terser: {
      extractComments: false,
    }
  })
  .vue({ version: 3 })
  .nova('nova-select-plus')

if (! mix.inProduction()) {
  mix.sourceMaps()
}

// let mix = require('laravel-mix')
//
// require('./nova.mix')
//
// mix
//     .setPublicPath('dist')
//     .js('resources/js/field.js', 'js')
//     .vue({ version: 3 })
//     .css('resources/css/field.css', 'css')
//     .nova('{{ name }}')
