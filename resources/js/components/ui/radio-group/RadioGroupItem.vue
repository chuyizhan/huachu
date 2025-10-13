<script setup lang="ts">
import { type HTMLAttributes, computed } from 'vue'
import { RadioGroupItem, type RadioGroupItemProps } from 'reka-ui'
import { Circle } from 'lucide-vue-next'
import { cn } from '@/lib/utils'

const props = defineProps<RadioGroupItemProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props

  return delegated
})
</script>

<template>
  <RadioGroupItem
    v-bind="delegatedProps"
    :class="
      cn(
        'aspect-square h-4 w-4 rounded-full border border-primary text-primary shadow focus:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50',
        props.class,
      )
    "
  >
    <div class="flex items-center justify-center">
      <Circle class="h-3.5 w-3.5 fill-primary" />
    </div>
  </RadioGroupItem>
</template>
