<template>
  <PanelItem :index="index" :field="field">
    <template #value>
      <component
        v-if="Array.isArray(field.valueForDetailDisplay)"
        :is="field.isReorderable == true ? 'ol' : 'ul'"
      >
        <li v-for="item in field.valueForDetailDisplay">
          {{ item }}
        </li>
      </component>
      <span v-if="typeof field.valueForDetailDisplay == 'string'">
        {{ field.valueForDetailDisplay || '-'}}
      </span>
    </template>
  </PanelItem>
</template>

<script>
export default {
  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],
}
</script>
