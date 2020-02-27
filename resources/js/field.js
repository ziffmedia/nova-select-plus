Nova.booting((Vue, router, store) => {
  Vue.component('index-select-plus', require('./components/IndexField').default)
  Vue.component('detail-select-plus', require('./components/DetailField').default)
  Vue.component('form-select-plus', require('./components/FormField').default)
})
