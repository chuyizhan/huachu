<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { ShoppingCart, DollarSign, CheckCircle2, Clock, XCircle } from 'lucide-vue-next'
import { usePagination } from '@/composables/usePagination'

interface Order {
    id: number
    order_number: string
    user_id: number
    type: string
    amount: string
    status: string
    payment_method: string
    paid_at: string | null
    created_at: string
    user: {
        id: number
        name: string
        login_name: string
        email: string
    }
}

interface PaginatedOrders {
    data: Order[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: any[]
}

interface Stats {
    total_orders: number
    total_amount: number
    completed_orders: number
    pending_orders: number
    failed_orders: number
    recharge_orders: number
    recharge_amount: number
}

interface Props {
    orders: PaginatedOrders
    users: any[]
    stats: Stats
    filters: {
        search?: string
        type?: string
        status?: string
        payment_method?: string
        user_id?: string
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
const type = ref(props.filters.type || '')
const status = ref(props.filters.status || '')
const paymentMethod = ref(props.filters.payment_method || '')
const userId = ref(props.filters.user_id || '')
const dateFrom = ref(props.filters.date_from || '')
const dateTo = ref(props.filters.date_to || '')
const perPage = ref(props.filters.per_page || '20')

const applyFilters = () => {
    router.get('/admin/orders', {
        search: search.value,
        type: type.value,
        status: status.value,
        payment_method: paymentMethod.value,
        user_id: userId.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearFilters = () => {
    search.value = ''
    type.value = ''
    status.value = ''
    paymentMethod.value = ''
    userId.value = ''
    dateFrom.value = ''
    dateTo.value = ''
    perPage.value = '20'
    applyFilters()
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('zh-CN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const getStatusColor = (status: string) => {
    const colors = {
        completed: 'bg-green-900 text-green-200',
        pending: 'bg-yellow-900 text-yellow-200',
        failed: 'bg-red-900 text-red-200',
        refunded: 'bg-gray-900 text-gray-200',
    }
    return colors[status as keyof typeof colors] || 'bg-gray-900 text-gray-200'
}

const getStatusText = (status: string) => {
    const texts = {
        completed: '已完成',
        pending: '待处理',
        failed: '失败',
        refunded: '已退款',
    }
    return texts[status as keyof typeof texts] || status
}

const getTypeText = (type: string) => {
    const texts = {
        recharge: '充值',
        subscription: '订阅',
        post_purchase: '购买帖子',
    }
    return texts[type as keyof typeof texts] || type
}

const getPaymentMethodText = (method: string) => {
    const texts = {
        fake: '模拟支付',
        alipay: '支付宝',
        wechat: '微信支付',
        credit_card: '信用卡',
    }
    return texts[method as keyof typeof texts] || method
}
</script>

<template>
    <AppLayout>
        <Head title="订单管理" />

        <div class="py-12">
            <div class="w-full sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-6">
                            <h1 class="text-2xl font-semibold">订单管理</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                查看和管理所有用户订单
                            </p>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">总订单数</CardTitle>
                                    <ShoppingCart class="h-4 w-4 text-blue-600" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold">{{ stats.total_orders }}</div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">所有订单</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">订单总额</CardTitle>
                                    <DollarSign class="h-4 w-4 text-green-600" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold">¥{{ stats.total_amount.toFixed(2) }}</div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">已完成订单</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">充值订单</CardTitle>
                                    <CheckCircle2 class="h-4 w-4 text-purple-600" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold">{{ stats.recharge_orders }}</div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">¥{{ stats.recharge_amount.toFixed(2) }}</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle class="text-sm font-medium">待处理</CardTitle>
                                    <Clock class="h-4 w-4 text-orange-600" />
                                </CardHeader>
                                <CardContent>
                                    <div class="text-2xl font-bold">{{ stats.pending_orders }}</div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">待处理订单</p>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Filters -->
                        <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <Input
                                    v-model="search"
                                    placeholder="搜索订单号或用户..."
                                    @input="applyFilters"
                                />
                            </div>
                            <div>
                                <select
                                    v-model="type"
                                    @change="applyFilters"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">全部类型</option>
                                    <option value="recharge">充值</option>
                                    <option value="subscription">订阅</option>
                                    <option value="post_purchase">购买帖子</option>
                                </select>
                            </div>
                            <div>
                                <select
                                    v-model="status"
                                    @change="applyFilters"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">全部状态</option>
                                    <option value="completed">已完成</option>
                                    <option value="pending">待处理</option>
                                    <option value="failed">失败</option>
                                    <option value="refunded">已退款</option>
                                </select>
                            </div>
                            <div>
                                <Button variant="outline" @click="clearFilters" class="w-full">
                                    清除筛选
                                </Button>
                            </div>
                        </div>

                        <!-- Orders Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">订单号</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">用户</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">类型</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">金额</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">支付方式</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">状态</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">创建时间</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                    <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-mono text-blue-600 dark:text-blue-400">{{ order.order_number }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium">{{ order.user.name }}</div>
                                            <div class="text-xs text-gray-500">@{{ order.user.login_name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm">{{ getTypeText(order.type) }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-semibold">¥{{ order.amount }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm">{{ getPaymentMethodText(order.payment_method) }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusColor(order.status)]">
                                                {{ getStatusText(order.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(order.created_at) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="orders.last_page > 1" class="mt-6 flex justify-center">
                            <nav class="inline-flex rounded-md shadow-sm -space-x-px">
                                <Link
                                    v-for="(link, index) in orders.links"
                                    :key="index"
                                    :href="link.url || '#'"
                                    :class="[
                                        'px-3 py-2 text-sm border',
                                        link.active
                                            ? 'bg-blue-50 border-blue-500 text-blue-600 dark:bg-blue-900 dark:text-blue-200'
                                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400',
                                        index === 0 ? 'rounded-l-md' : '',
                                        index === orders.links.length - 1 ? 'rounded-r-md' : '',
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
