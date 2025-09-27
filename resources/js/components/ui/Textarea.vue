<script setup lang="ts">
import { computed } from 'vue'
import type { HTMLAttributes } from 'vue'
import { textareaVariants } from './textarea'
import { cn } from '@/lib/utils'

interface Props {
  class?: HTMLAttributes['class']
  defaultValue?: string | number
  modelValue?: string | number
  variant?: 'default'
}

defineOptions({
  inheritAttrs: false,
})

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
})

const emits = defineEmits<{
  'update:modelValue': [value: string | number]
}>()

const delegatedProps = computed(() => {
  const { class: _, variant, ...delegated } = props

  return delegated
})
</script>

<template>
  <textarea
    :class="cn(textareaVariants({ variant }), props.class)"
    :value="modelValue ?? defaultValue"
    v-bind="delegatedProps"
    @input="
      emits(
        'update:modelValue',
        ($event.target as HTMLTextAreaElement).value,
      )
    "
  />
</template>