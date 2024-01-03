<template>
  <DefaultField :field="field" :errors="errors" :show-help-text="showHelpText">
    <template #field>
      <template v-if="!isInReorderMode">
        <vue-select
          class="nova-select-plus-vs"
          v-model="selected"
          :options="options"
          :placeholder="placeholder"
          :loading="isLoading"
          :disabled="currentField.readonly"
          :multiple="true"
          :selectable="selectable"
          :filterable="filterable"
          @search="handleSearch"
          @option:selected="$emit('field-changed')"
          @option:deselected="$emit('field-changed')"
          append-to-body
          :calculate-position="vueSelectCalculatePosition"
        >
          <template #open-indicator="{ attributes }">
            <svg
              v-bind="attributes"
              class="flex-shrink-0 pointer-events-none form-select-arrow"
              xmlns="http://www.w3.org/2000/svg"
              width="10"
              height="6"
              viewBox="0 0 10 6"
            >
              <path
                class="fill-current"
                d="M8.292893.292893c.390525-.390524 1.023689-.390524 1.414214 0 .390524.390525.390524 1.023689 0 1.414214l-4 4c-.390525.390524-1.023689.390524-1.414214 0l-4-4c-.390524-.390525-.390524-1.023689 0-1.414214.390525-.390524 1.023689-.390524 1.414214 0L5 3.585786 8.292893.292893z"
              ></path>
            </svg>
          </template>
          <template #no-options>
            <span v-if="currentField.isAjaxSearchable">
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
        </vue-select>
      </template>
      <template v-else>
        <vue-draggable
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
        </vue-draggable>
      </template>

      <span
        v-if="currentField.isReorderable"
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
import { DependentFormField, HandlesValidationErrors } from 'laravel-nova'
import vueSelect from 'vue-select'
import { debounce } from 'lodash'
import { VueDraggableNext as vueDraggable } from 'vue-draggable-next'

export default {
  components: {
    vueSelect,
    vueDraggable
  },

  mixins: [DependentFormField, HandlesValidationErrors],

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
    onSyncedField() {
       this.setup()
    },

    setInitialValue () {
      this.selected = this.currentField.value || []
    },

    vueSelectCalculatePosition (dropdownList, component, { width, top, left }) {
      // default built-in logic
      dropdownList.style.top = top
      dropdownList.style.left = left
      dropdownList.style.width = width

      // add our custom class to the node that is appended to body, see the stylesheet field.css
      dropdownList.classList.add('nova-select-plus-vs')
    },

    setup () {
      this.placeholder = this.currentField?.extraAttributes?.placeholder

      // if there is no options (not yet supported), but needs the full list via ajax
      if (this.currentField['isAjaxSearchable'] === false
        || (this.currentField['isAjaxSearchable'] === true && this.currentField['isAjaxSearchableEmptySearch'] === true)
      ) {
        const params = {}

        if (this.currentField.dependsOn) {
          Object.assign(params, this.currentField.dependsOn)
        }

        Object.assign(params, { resourceId: this.resourceId })

        Nova.request().get('/nova-vendor/select-plus/' + this.resourceName + '/' + this.currentField['relationshipName'], { params })
          .then(resp => {
            this.options = resp.data
            this.isLoading = false
          })

        return
      }

      this.isLoading = false
      this.filterable = false
    },

    fill (formData) {
      this.fillIfVisible(formData, this.currentField.attribute, JSON.stringify(this.selected))
    },

    selectable () {
      if (this.currentField['maxSelections'] <= 0) {
        return true
      }

      return this.selected.length < this.currentField['maxSelections']
    },

    handleSearch: debounce(function (search, loading) {
      if (this.currentField['isAjaxSearchable'] === false) {
        return
      }

      if (this.currentField['isAjaxSearchableEmptySearch'] === false && !search) {
        this.ajaxSearchNoResults = false

        return
      }

      loading(true)

      const params = {}

      if (this.currentField.dependsOn) {
        Object.assign(params, this.currentField.dependsOn)
      }

      Object.assign(params, { search: search, resourceId: this.resourceId })

      Nova.request().get('/nova-vendor/select-plus/' + this.resourceName + '/' + this.currentField['relationshipName'], { params })
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
    this.setup()
  }
}
</script>
