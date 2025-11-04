<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { usePagination } from '@/composables/usePagination'

interface Plan {
    id: number
    level: number
    name: string
    slug: string
    description: string
    price: number
    period_days: number
    features: string[]
    max_premium_posts: number | null
    can_create_premium_content: boolean
    priority_support: boolean
    badge_color: string
    badge_text_color: string
    sort_order: number
    is_active: boolean
    subscriptions_count: number
}

interface PaginatedPlans {
    data: Plan[]
    current_page: number
    last_page: number
    per_page: number
    total: number
}

interface Props {
    plans: PaginatedPlans
    filters: {
        search?: string
        is_active?: string
        level?: string
        sort_by?: string
        sort_direction?: string
        per_page?: string
    }
}

const props = defineProps<Props>()

const { translatePaginationLabel } = usePagination()

const search = ref(props.filters.search || '')
const isActive = ref(props.filters.is_active || '')
const level = ref(props.filters.level || '')
const sortBy = ref(props.filters.sort_by || 'sort_order')
const sortDirection = ref(props.filters.sort_direction || 'asc')
const perPage = ref(props.filters.per_page || '20')

const applyFilters = () => {
    router.get('/admin/plans', {
        search: search.value,
        is_active: isActive.value,
        level: level.value,
        sort_by: sortBy.value,
        sort_direction: sortDirection.value,
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearFilters = () => {
    search.value = ''
    isActive.value = ''
    level.value = ''
    sortBy.value = 'sort_order'
    sortDirection.value = 'asc'
    perPage.value = '20'
    applyFilters()
}

const deletePlan = (id: number) => {
    if (confirm('确定要删除此套餐吗？')) {
        router.delete(`/admin/plans/${id}`, {
            preserveScroll: true,
        })
    }
}
</script>

<template>
    <AppLayout>
        <Head title="套餐管理" />

        <div class="py-12">
            <div class="w-full sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-6 flex items-center justify-between">
                            <h1 class="text-2xl font-semibold">套餐管理</h1>
                            <Link href="/admin/plans/create">
                                <Button>创建套餐</Button>
                            </Link>
                        </div>

                        <!-- Filters -->
                        <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <Input
                                    v-model="search"
                                    placeholder="搜索套餐名称或别名..."
                                    @input="applyFilters"
                                />
                            </div>
                            <div>
                                <select
                                    v-model="isActive"
                                    @change="applyFilters"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">全部状态</option>
                                    <option value="1">启用</option>
                                    <option value="0">禁用</option>
                                </select>
                            </div>
                            <div>
                                <Input
                                    v-model="level"
                                    type="number"
                                    placeholder="等级..."
                                    @input="applyFilters"
                                />
                            </div>
                            <div>
                                <Button variant="outline" @click="clearFilters" class="w-full">
                                    清除筛选
                                </Button>
                            </div>
                        </div>

                        <!-- Plans Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">等级</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">套餐名称</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">价格</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">周期</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">订阅数</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">状态</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">排序</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">操作</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    <tr v-for="plan in plans.data" :key="plan.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-semibold">{{ plan.level }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="w-4 h-4 rounded"
                                                    :style="{ backgroundColor: plan.badge_color }"
                                                ></div>
                                                <div>
                                                    <div class="text-sm font-medium">{{ plan.name }}</div>
                                                    <div class="text-xs text-gray-500">{{ plan.slug }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-semibold text-green-600">¥{{ plan.price }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm">{{ plan.period_days }} 天</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm">{{ plan.subscriptions_count }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Badge :variant="plan.is_active ? 'default' : 'secondary'">
                                                {{ plan.is_active ? '启用' : '禁用' }}
                                            </Badge>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm">{{ plan.sort_order }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="`/admin/plans/${plan.id}/edit`" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 mr-3">
                                                编辑
                                            </Link>
                                            <button
                                                @click="deletePlan(plan.id)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400"
                                            >
                                                删除
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="plans.last_page > 1" class="mt-6 flex justify-center">
                            <nav class="inline-flex rounded-md shadow-sm -space-x-px">
                                <Link
                                    v-for="(link, index) in plans.links"
                                    :key="index"
                                    :href="link.url || '#'"
                                    :class="[
                                        'px-3 py-2 text-sm border',
                                        link.active
                                            ? 'bg-blue-50 border-blue-500 text-blue-600 dark:bg-blue-900 dark:text-blue-200'
                                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400',
                                        index === 0 ? 'rounded-l-md' : '',
                                        index === plans.links.length - 1 ? 'rounded-r-md' : '',
                                    ]"
                                    v-html="translatePaginationLabel(link.label)"
                                    :disabled="!link.url"
                                />
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
