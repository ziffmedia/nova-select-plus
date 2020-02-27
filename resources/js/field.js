Nova.booting((Vue, router, store) => {
  Vue.component('index-relation-multiselect', require('./components/IndexField').default)
  Vue.component('detail-relation-multiselect', require('./components/DetailField').default)
  Vue.component('form-relation-multiselect', require('./components/FormField').default)
})
