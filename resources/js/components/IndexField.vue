<template>
  <div>
    <component
      v-if="Array.isArray(field.value_for_index_display)"
      :is="field.isReorderable === true ? 'ol' : 'ul'"
    >
      <li v-for="item in field.value_for_index_display">
        {{ item }}
      </li>
    </component>
    <span v-if="typeof field.valueForIndexDisplay == 'string'">
      {{ field.valueForIndexDisplay || '-'}}
    </span>
  </div>
</template>

<script>
export default {
  props: ['resourceName', 'field'],

  computed: {
    fieldValue () {
      debugger
      return this.field.displayedAs || this.field.value
    },
  }
}
</script>
