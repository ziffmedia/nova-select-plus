import DetailField from './components/DetailField'
import FormField from './components/FormField'
import IndexField from './components/IndexField'

Nova.booting((Vue, router, store) => {
  Vue.component('detail-select-plus', DetailField)
  Vue.component('form-select-plus', FormField)
  Vue.component('index-select-plus', IndexField)
})
