<template>
  <div>
    <componet
      v-bind:is="field.reorderable == true ? 'ol' : 'ul'"
      v-if="Array.isArray(field.value_for_index_display)"
      >
      <li v-for="item in field.value_for_index_display">
        {{ item }}
      </li>
    </componet>
    <span v-if="typeof field.value_for_index_display == 'string'">
      {{ field.value_for_index_display || '-'}}
    </span>
  </div>
</template>

<script>
  export default {
    props: ['field'],
  }
</script>
