<template>
  <default-field :field="field" :errors="errors" full-width-content="true">
    <template slot="field">
      <span v-if="!isInReorderMode">
        <v-select
          v-model:value="selected"
          v-bind:options="options"
          v-bind:loading="isLoading"
          v-bind:disabled="field.readonly"
          v-bind:multiple="true"
          v-bind:selectable="selectable"
          v-bind:filterable="filterable"
          v-on:search="handleSearch"
        >
          <template v-slot:no-options>
            <span v-if="field.ajax_searchable">
              Type to search...
              <span v-if="ajaxSearchNoResults">Nothing found.</span>
            </span>
            <span v-else>Sorry, no matching options!</span>
          </template>
        </v-select>
      </span>
      <span v-else>
        <v-draggable
          v-model="selected"
          v-on:start="dragging = true"
          v-on:end="dragging = false"
          tag="ol"
          >
            <li v-for="(item, index) in selected" v-bind:key="item.id">
              <p>
                {{ item.label }}
              </p>
            </li>
        </v-draggable>
      </span>

      <span v-if="field.reorderable" class="w-full text-right text-sm">
        <a v-if="!isInReorderMode" href="#" v-on:click.prevent="isInReorderMode = true">
          Reorder
        </a>
        <a v-else href="#" v-on:click.prevent="isInReorderMode = false">
          Finish Reordering
        </a>
      </span>
    </template>
  </default-field>
</template>

<script>
  import { FormField, HandlesValidationErrors, Errors } from 'laravel-nova'
  import vSelect from 'vue-select'
  import vDraggable from 'vuedraggable'
  import { debounce } from 'lodash'

  export default {
    components: {
      vSelect,
      vDraggable
    },

    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    data () {
      return {
        selected: [],
        options: [],
        isLoading: true,
        filterable: true,
        ajaxSearchNoResults: false,
        isInReorderMode: false
      }
    },

    methods: {
      setInitialValue () {
        this.selected = this.field.value || []
      },

      selectable () {
        if (this.field.max_selections <= 0) {
          return true
        }

        return this.selected.length < this.field.max_selections
      },

      handleSearch: debounce(function (search, loading) {
        if (!search) {
          this.ajaxSearchNoResults = false

          return
        }

        loading(true)

        axios.get('/nova-vendor/select-plus/' + this.resourceName + '/' + this.field['relationship_name'] + '?search=' + search)
          .then(resp => {
            this.options = resp.data

            if (this.options.length == 0) {
              this.ajaxSearchNoResults = true
            }

            loading(false)
          })
      }, 500),

      fill (formData) {
        formData.append(this.field.attribute, JSON.stringify(this.selected))
      }
    },

    mounted () {
      // if there a no options (not yet supported), but needs the full list via ajax
      if (this.field['ajax_searchable'] == false) {
        axios.get('/nova-vendor/select-plus/' + this.resourceName + '/' + this.field['relationship_name'])
          .then(resp => {
            this.options = resp.data
            this.isLoading = false
          })

        return
      }

      this.isLoading = false
      this.filterable = false
    }
  }
</script>

<style>
  .vs__selected {
    border: 1px solid var(--primary-dark) !important;
    background: var(--20) !important; /* #D2CBDC */
    color: var(--primary-dark) !important;
  }
</style>
