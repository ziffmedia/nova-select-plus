module.exports = {
  presets: [
    require('./vendor/laravel/nova/tailwind.config.js'),
  ],
  prefix: 'nsp-',
  content: [
    './src/**/*.php',
    './resources/**/*{js,vue,blade.php}',
  ]
}
