<template>
  <default-field :field="field" :errors="errors" full-width-content="true">
    <template slot="field">
      <multiselect
        track-by="key"
        label="label"
        v-on:input="msHandleChange"
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
      <br>
      <v-select
        v-model:value="selected"
        v-bind:options="options"
        v-bind:loading="isLoading"
        v-bind:disabled="field.readonly"
        v-bind:multiple="true"
        v-bind:selectable="vSelectable"
        v-bind:filterable="vFilterable"
        v-on:search="vSearch"
      ></v-select>
    </template>
  </default-field>
</template>

<script>
  import { FormField, HandlesValidationErrors, Errors } from 'laravel-nova'
  import Multiselect from 'vue-multiselect'
  import vSelect from 'vue-select'
  import { debounce } from 'lodash'

  export default {
    components: {
      Multiselect,
      vSelect
    },

    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    data () {
      return {
        selected: [],
        options: [],
        isLoading: true,
        vFilterable: true
      }
    },

    methods: {
      checkIt () {
        console.log(this.selected)
      },

      setInitialValue () {
        this.selected = this.field.value || []
      },

      msHandleChange (newValues) {
        this.selected = newValues
      },

      vSelectable () {
        if (this.field.max_selections <= 0) {
          return true
        }

        return this.selected.length < this.field.max_selections
      },

      vSearch: debounce(function (search, loading) {
        axios.get('/nova-vendor/select-plus/' + this.resourceName + '/' + this.field['relationship_name'] + '?search=' + search)
          .then(resp => {
            this.options = resp.data
            loading(false)
          })
      }, 1000),

      fill (formData) {
        formData.append(this.field.attribute, JSON.stringify(this.selected))
      },
    },

    mounted () {
      if (this.field['ajax_searchable'] == false) {
        axios.get('/nova-vendor/select-plus/' + this.resourceName + '/' + this.field['relationship_name'])
          .then(resp => {
            this.options = resp.data
            this.isLoading = false
          })
      } else {
        this.vFilterable = false
      }
    }
  }
</script>
