<template>
  <default-field :field="field" :errors="errors" :full-width-content="true" :show-help-text="field.helpText != null">
    <template slot="field">
      <div v-bind:dusk="field.attribute">
        <template v-if="!isInReorderMode">
          <v-select
            class="nova-select-plus-vs"
            v-model:value="selected"
            v-bind:options="options"
            v-bind:placeholder="placeholder"
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
            <template v-slot:option="option">
              <span v-html="option.label"></span>
            </template>
            <template v-slot:selected-option="option">
              <span v-html="option.label"></span>
            </template>
          </v-select>
        </template>
        <template v-else>
          <v-draggable
            class="nova-select-plus-vd"
            v-model="selected"
            v-on:start="dragging = true"
            v-on:end="dragging = false"
          >
          <span class="vd__item" v-for="(item, index) in selected" v-bind:key="item.id">
            {{ index + 1 }}. <span v-html="item.label"></span>
            <svg width="16" class="float-right" aria-hidden="true" focusable="false" data-prefix="far" data-icon="grip-lines" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M432 288H16c-8.8 0-16 7.2-16 16v16c0 8.8 7.2 16 16 16h416c8.8 0 16-7.2 16-16v-16c0-8.8-7.2-16-16-16zm0-112H16c-8.8 0-16 7.2-16 16v16c0 8.8 7.2 16 16 16h416c8.8 0 16-7.2 16-16v-16c0-8.8-7.2-16-16-16z"></path></svg>
          </span>
        </v-draggable>
      </template>

        <span v-if="field.reorderable" class="float-right text-sm ml-3 border-1 mt-2 mr-4">
          <a v-if="!isInReorderMode" v-on:click.prevent="isInReorderMode = true" class="text-primary dim no-underline" href="#">
            Reorder
          </a>
          <a v-else class="text-primary dim no-underline" v-on:click.prevent="isInReorderMode = false" href="#">
            Finish Reordering
          </a>
        </span>
      </div>
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
        isInReorderMode: false,
        placeholder: ''
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
        if (this.field['ajax_searchable'] === false) {
          return
        }

        if (this.field['ajax_searchable_empty_search'] === false && !search) {
          this.ajaxSearchNoResults = false

          return
        }

        loading(true)

        axios.get('/nova-vendor/select-plus/' + this.resourceName + '/' + this.field['relationship_name'], {
          params: { search: search, resourceId: this.resourceId }
        })
          .then(resp => {
            this.options = resp.data

            if (this.options.length === 0) {
              this.ajaxSearchNoResults = true
            }

            loading(false)
          })
          .catch(err => {
            console.error(err)

            loading(false)
          })

        return true
      }, 500),

      fill (formData) {
        formData.append(this.field.attribute, JSON.stringify(this.selected))
      }
    },

    mounted () {
      this.placeholder = this.field?.extraAttributes?.placeholder

      // if there a no options (not yet supported), but needs the full list via ajax
      if (this.field['ajax_searchable'] === false
          || (this.field['ajax_searchable'] === true && this.field['ajax_searchable_empty_search'] === true)
      ) {
        axios.get('/nova-vendor/select-plus/' + this.resourceName + '/' + this.field['relationship_name'], {
          params: { resourceId: this.resourceId }
        })
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
