module.exports = {
  presets: [
    require('./vendor/laravel/nova/tailwind.config.js'),
  ],
  content: [
    './src/**/*.php',
    './resources/**/*{js,vue,blade.php}'
  ]
}
