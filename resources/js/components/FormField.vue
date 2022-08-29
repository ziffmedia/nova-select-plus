<template>
  <DefaultField :field="field" :errors="errors" :show-help-text="showHelpText">
    <template #field>
      <template v-if="!isInReorderMode">
        <v-select
          class="nova-select-plus-vs"
          v-model="selected"
          :options="options"
          :placeholder="placeholder"
          :loading="isLoading"
          :disabled="field.readonly"
          :multiple="true"
          :selectable="selectable"
          :filterable="filterable"
          @search="handleSearch"
        >
          <template #no-options>
            <span v-if="field.isAjaxSearchable">
              Type to search...
              <span v-if="ajaxSearchNoResults">Nothing found.</span>
            </span>
            <span v-else>
              Sorry, no matching options!
            </span>
          </template>
          <template #option="option">
            <span v-html="option.label" />
          </template>
          <template #selected-option="option">
            <span v-html="option.label" />
          </template>
        </v-select>
      </template>
      <template v-else>
        <v-draggable
          class="nova-select-plus-vd"
          v-model="selected"
          @start="isDragging = true"
          @end="isDragging = false"
        >
          <span
            class="vd__item"
            v-for="(item, index) in selected"
            :key="item.id"
          >
            {{ index + 1 }}. <span v-html="item.label"></span>
            <svg width="16" class="vd__item_drag_icon" aria-hidden="true" focusable="false" data-prefix="far" data-icon="grip-lines" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
              <path fill="currentColor" d="M432 288H16c-8.8 0-16 7.2-16 16v16c0 8.8 7.2 16 16 16h416c8.8 0 16-7.2 16-16v-16c0-8.8-7.2-16-16-16zm0-112H16c-8.8 0-16 7.2-16 16v16c0 8.8 7.2 16 16 16h416c8.8 0 16-7.2 16-16v-16c0-8.8-7.2-16-16-16z"></path>
            </svg>
          </span>
        </v-draggable>
      </template>

      <span
        v-if="field.isReorderable"
        class="float-right text-sm ml-3 border-1 mt-2 mr-4"
      >
        <a
          v-if="!isInReorderMode"
          class="text-primary dim no-underline"
          href="#"
          @click.prevent="isInReorderMode = true"
        >
          Reorder
        </a>
        <a
          v-else
          class="text-primary dim no-underline"
          href="#"
          @click.prevent="isInReorderMode = false"
        >
          Finish Reordering
        </a>
      </span>
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import vSelect from 'vue-select'
import { debounce } from 'lodash'
import { VueDraggableNext as vDraggable } from 'vue-draggable-next'

export default {
  components: {
    vSelect,
    vDraggable
  },

  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data () {
    return {
      isDragging: false,
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

    fill (formData) {
      formData.append(this.field.attribute, JSON.stringify(this.selected))
    },

    selectable () {
      if (this.field['maxSelections'] <= 0) {
        return true
      }

      return this.selected.length < this.field['maxSelections']
    },

    handleSearch: debounce(function (search, loading) {
      if (this.field['isAjaxSearchable'] === false) {
        return
      }

      if (this.field['isAjaxSearchableEmptySearch'] === false && !search) {
        this.ajaxSearchNoResults = false

        return
      }

      loading(true)

      Nova.request().get('/nova-vendor/select-plus/' + this.resourceName + '/' + this.field['relationshipName'], {
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
    }, 500)
  },

  mounted () {
    this.placeholder = this.field?.extraAttributes?.placeholder

    // if there is no options (not yet supported), but needs the full list via ajax
    if (this.field['isAjaxSearchable'] === false
        || (this.field['isAjaxSearchable'] === true && this.field['isAjaxSearchableEmptySearch'] === true)
    ) {
      Nova.request().get('/nova-vendor/select-plus/' + this.resourceName + '/' + this.field['relationshipName'], {
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
