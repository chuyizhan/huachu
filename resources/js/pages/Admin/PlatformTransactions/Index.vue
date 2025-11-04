<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { TrendingUp, DollarSign, Users, Activity } from 'lucide-vue-next'
import { usePagination } from '@/composables/usePagination'

interface PlatformTransaction {
    id: number
    post_id: number
    creator_id: number
    amount: number
    percentage: number
    creator_amount: number
    total_transaction: number
    created_at: string
    creator: {
        id: number
        name: string
        login_name: string
    }
    post: {
        id: number
        title: string
        slug: string
    }
}

interface PaginatedTransactions {
    data: PlatformTransaction[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: any[]
}

interface Stats {
    total_earnings: number
    total_transactions: number
    total_creator_payments: number
    average_commission: number
    total_revenue: number
}

interface Props {
    transactions: PaginatedTransactions
    creators: any[]
    stats: Stats
    monthlyEarnings: Record<string, number>
    filters: {
        search?: string
        creator_id?: string
        amount_min?: string
        amount_max?: string
        date_from?: string
        date_to?: string
        sort_by?: string
        sort_direction?: string
        per_page?: string
    }
}

const props = defineProps<Props>()

const { translatePaginationLabel } = usePagination()

const search = ref(props.filters.search || '')
const creatorId = ref(props.filters.creator_id || '')
const amountMin = ref(props.filters.amount_min || '')
const amountMax = ref(props.filters.amount_max || '')
const dateFrom = ref(props.filters.date_from || '')
const dateTo = ref(props.filters.date_to || '')
const sortBy = ref(props.filters.sort_by || 'created_at')
const sortDirection = ref(props.filters.sort_direction || 'desc')
const perPage = ref(props.filters.per_page || '20')

const applyFilters = () => {
    router.get('/admin/platform-transactions', {
        search: search.value,
        creator_id: creatorId.value,
        amount_min: amountMin.value,
        amount_max: amountMax.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
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
    creatorId.value = ''
    amountMin.value = ''
    amountMax.value = ''
    dateFrom.value = ''
    dateTo.value = ''
    sortBy.value = 'created_at'
    sortDirection.value = 'desc'
    perPage.value = '20'
    applyFilters()
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    })
}
</script>

<template>
    <AppLayout>
        <Head title="平台收益" />

        <div class="py-12">
            <div class="w-full sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-6">
                            <h1 class="text-2xl font-semibold">平台收益</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                查看和分析平台从付费内容中获得的佣金收益
                            </p>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">总收益</CardTitle>
                                    <DollarSign class="h-4 w-4 text-green-600" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold text-green-600">¥{{ stats.total_earnings?.toFixed(2) || '0.00' }}</div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">平台佣金总额</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">交易总额</CardTitle>
                                    <TrendingUp class="h-4 w-4 text-blue-600" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold">¥{{ stats.total_revenue?.toFixed(2) || '0.00' }}</div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">总交易金额</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">创作者收益</CardTitle>
                                    <Users class="h-4 w-4 text-purple-600" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold">¥{{ stats.total_creator_payments?.toFixed(2) || '0.00' }}</div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">支付给创作者</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">交易数量</CardTitle>
                                    <Activity class="h-4 w-4 text-orange-600" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold">{{ stats.total_transactions || 0 }}</div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">笔交易</p>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Filters -->
                        <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <Input
                                    v-model="search"
                                    placeholder="搜索创作者或帖子..."
                                    @input="applyFilters"
                                />
                            </div>
                            <div>
                                <select
                                    v-model="creatorId"
                                    @change="applyFilters"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">全部创作者</option>
                                    <option v-for="creator in creators" :key="creator.id" :value="creator.id">
                                        {{ creator.name }} (@{{ creator.login_name }})
                                    </option>
                                </select>
                            </div>
                            <div>
                                <Input
                                    v-model="dateFrom"
                                    type="date"
                                    placeholder="开始日期"
                                    @change="applyFilters"
                                />
                            </div>
                            <div>
                                <Button variant="outline" @click="clearFilters" class="w-full">
                                    清除筛选
                                </Button>
                            </div>
                        </div>

                        <!-- Transactions Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">时间</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">帖子</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">创作者</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">交易总额</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">平台佣金</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">创作者所得</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">佣金比例</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ formatDate(transaction.created_at) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <Link :href="`/posts/${transaction.post.slug}`" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm">
                                                {{ transaction.post.title }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium">{{ transaction.creator.name }}</div>
                                            <div class="text-xs text-gray-500">@{{ transaction.creator.login_name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-semibold">¥{{ transaction.total_transaction }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-semibold text-green-600">¥{{ transaction.amount }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm">¥{{ transaction.creator_amount }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ transaction.percentage }}%
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="transactions.last_page > 1" class="mt-6 flex justify-center">
                            <nav class="inline-flex rounded-md shadow-sm -space-x-px">
                                <Link
                                    v-for="(link, index) in transactions.links"
                                    :key="index"
                                    :href="link.url || '#'"
                                    :class="[
                                        'px-3 py-2 text-sm border',
                                        link.active
                                            ? 'bg-blue-50 border-blue-500 text-blue-600 dark:bg-blue-900 dark:text-blue-200'
                                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400',
                                        index === 0 ? 'rounded-l-md' : '',
                                        index === transactions.links.length - 1 ? 'rounded-r-md' : '',
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
