<template>
  <panel-item :field="field">
    <template slot="value">
      <span>{{ field.value_for_detail_display }}</span>
    </template>
  </panel-item>
</template>

<script>
  export default {
    props: ['field'], // 'resource', 'resourceName', 'resourceId',

    data () {
      return {
        value: 'Detail'
      }
    }
  }
</script>
