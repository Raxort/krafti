<template>
  <b-form-textarea
    v-if="multiple"
    v-model="myValue"
    v-bind="$attrs"
    rows="2"
    max-rows="10"
    inputmode="numeric"
    @keydown="onKeyDown"
    @keyup="onChange"
    @change="onChange"
  />
  <b-form-input
    v-else
    v-model="myValue"
    v-bind="$attrs"
    inputmode="numeric"
    @keydown="onKeyDown"
    @keyup="onChange"
    @change="onChange"
  />
</template>

<script>
export default {
  props: {
    value: {
      type: [String, Array],
      default: null,
    },
    multiple: {
      type: Boolean,
      default: false,
    },
    maxLength: {
      type: [String, Number],
      default: 10,
    },
  },
  data() {
    return {
      myValue: typeof this.value === 'object' && this.value !== null ? this.value.join(' ') : this.value,
    }
  },
  methods: {
    onKeyDown(e) {
      const keys = ['Backspace', 'Alt', 'Ctrl', 'Meta', 'Tab']
      if (!this.multiple) {
        keys.push('Enter')
      }

      const allow =
        keys.includes(e.key) ||
        e.altKey ||
        e.ctrlKey ||
        e.metaKey ||
        (/\d+/.test(e.key) && (this.multiple || !this.myValue || this.myValue.length < this.maxLength))
      if (!allow) {
        e.preventDefault()
      }
    },
    onChange() {
      if (!this.myValue) {
        return
      }
      let value = this.myValue.replace(/\D/g, '')
      if (this.multiple) {
        const regexp = new RegExp('(\\d{' + this.maxLength + '})', 'g')
        value = value.replace(regexp, '$1 ').trim()
      }
      this.myValue = value
      this.$emit('input', this.multiple ? value.split(' ') : value)
    },
  },
}
</script>
