<template>
  <default-field :field="field" :errors="errors" full-width-content="true">
    <template slot="field">
      <multiselect
        track-by="key"
        label="label"
        v-on:input="handleChange"
        v-bind:value="selected"
        v-bind:options="options"
        v-bind:multiple="true"
        v-bind:taggable="true"
        v-bind:loading="isLoading"
        v-bind:disabled="field.readonly"
        v-bind:max="field.max_selections"
        options-limit="10000"
      >
      </multiselect>
    </template>
  </default-field>
</template>

<script>
  import { FormField, HandlesValidationErrors, Errors } from 'laravel-nova'
  import Multiselect from 'vue-multiselect';

  export default {
    components: {
      Multiselect
    },

    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    data () {
      return {
        selected: [],
        options: [],
        isLoading: true
      }
    },

    methods: {
      setInitialValue () {
        this.selected = this.field.value || []
      },

      handleChange (newValues) {
        this.selected = newValues
      },

      fill (formData) {
        formData.append(this.field.attribute, JSON.stringify(this.selected))
      },
    },

    mounted () {
      axios.get('/nova-vendor/select-plus/' + this.resourceName + '/' + this.field['relationship_name'])
        .then(resp => {
          this.options = resp.data
          this.isLoading = false
        })
    }
  }
</script>
