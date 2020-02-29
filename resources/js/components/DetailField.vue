<template>
  <panel-item :field="field">
    <template slot="value">
      <componet
        v-bind:is="field.reorderable == true ? 'ol' : 'ul'"
        v-if="Array.isArray(field.value_for_detail_display)"
      >
        <li v-for="item in field.value_for_detail_display">
          {{ item }}
        </li>
      </componet>
      <span v-if="typeof field.value_for_detail_display == 'string'">
        {{ field.value_for_detail_display || '-'}}
      </span>
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
