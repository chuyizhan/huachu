<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Crown, ArrowLeft, Calendar, CreditCard, Check, X } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';

interface Plan {
    id: number;
    name: string;
    slug: string;
    level: number;
    badge_color: string;
    badge_text_color: string;
}

interface PlanSubscription {
    id: number;
    subscription_number: string;
    status: string;
    price_paid: string;
    payment_method: string;
    started_at: string;
    expires_at: string | null;
    auto_renew: boolean;
    renewal_count: number;
    plan: Plan;
}

interface PaginatedSubscriptions {
    data: PlanSubscription[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    subscriptions: PaginatedSubscriptions;
    activeSubscription?: PlanSubscription | null;
}

const props = defineProps<Props>();

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusText = (status: string) => {
    const statusMap: Record<string, string> = {
        'active': '进行中',
        'cancelled': '已取消',
        'expired': '已过期',
        'suspended': '已暂停',
        'trial': '试用中'
    };
    return statusMap[status] || status;
};

const getStatusColor = (status: string) => {
    const colorMap: Record<string, string> = {
        'active': 'bg-green-500/20 text-green-400',
        'cancelled': 'bg-red-500/20 text-red-400',
        'expired': 'bg-gray-500/20 text-gray-400',
        'suspended': 'bg-yellow-500/20 text-yellow-400',
        'trial': 'bg-blue-500/20 text-blue-400'
    };
    return colorMap[status] || 'bg-gray-500/20 text-gray-400';
};
</script>

<template>
    <WebLayout>
        <Head title="我的会员订阅" />

        <div class="min-h-screen bg-[#1c1c1c] pb-20">
            <!-- Header -->
            <div class="bg-[#1f2937] u-p-25 sticky top-0 z-10">
                <div class="flex items-center u-m-b-25">
                    <Link href="/profile" class="u-p-r-25">
                        <ArrowLeft class="w-6 h-6 text-white" />
                    </Link>
                    <h1 class="colorz font32 font-w-600">我的会员订阅</h1>
                </div>
            </div>

            <!-- Active Subscription -->
            <div v-if="activeSubscription" class="u-m-25">
                <div class="listclass u-p-25">
                    <div class="flex items-center justify-between u-m-b-20">
                        <h3 class="font28 colorfff font-w-600 flex items-center">
                            <Crown class="w-6 h-6 u-p-r-10 text-[#ff6e02]" />
                            当前会员
                        </h3>
                        <Link :href="`/plan-subscriptions/${activeSubscription.id}`">
                            <span class="font22 text-[#ff6e02]">管理</span>
                        </Link>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font24 color999">套餐</span>
                            <Badge
                                :style="{
                                    backgroundColor: activeSubscription.plan.badge_color,
                                    color: activeSubscription.plan.badge_text_color
                                }"
                                class="font22"
                            >
                                {{ activeSubscription.plan.name }}
                            </Badge>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font24 color999">状态</span>
                            <Badge :class="getStatusColor(activeSubscription.status)" class="font22">
                                {{ getStatusText(activeSubscription.status) }}
                            </Badge>
                        </div>
                        <div v-if="activeSubscription.expires_at" class="flex items-center justify-between">
                            <span class="font24 color999">到期时间</span>
                            <span class="font24 colorz">{{ formatDateTime(activeSubscription.expires_at) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font24 color999">自动续费</span>
                            <Badge :class="activeSubscription.auto_renew ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400'" class="font22">
                                <Check v-if="activeSubscription.auto_renew" class="w-3 h-3 u-p-r-5 inline" />
                                <X v-else class="w-3 h-3 u-p-r-5 inline" />
                                {{ activeSubscription.auto_renew ? '已开启' : '已关闭' }}
                            </Badge>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Subscription Button -->
            <div v-if="!activeSubscription" class="u-m-25">
                <Link href="/plan-subscriptions/create" class="block">
                    <div class="listclass u-p-25 text-center">
                        <Crown class="w-12 h-12 text-[#ff6e02] mx-auto u-m-b-15" />
                        <h3 class="font28 colorfff font-w-600 u-m-b-10">开通VIP会员</h3>
                        <p class="font22 color999">享受更多专属特权</p>
                    </div>
                </Link>
            </div>

            <!-- Subscription History -->
            <div class="u-m-25">
                <h3 class="font28 colorfff font-w-600 u-m-b-20 u-p-l-10">订阅历史</h3>

                <div v-if="subscriptions.data.length === 0" class="listclass u-p-40 text-center">
                    <p class="color999 font24">暂无订阅记录</p>
                </div>

                <div v-else class="space-y-3">
                    <Link
                        v-for="subscription in subscriptions.data"
                        :key="subscription.id"
                        :href="`/plan-subscriptions/${subscription.id}`"
                        class="block"
                    >
                        <div class="listclass u-p-25">
                            <div class="flex items-center justify-between u-m-b-15">
                                <Badge
                                    :style="{
                                        backgroundColor: subscription.plan.badge_color,
                                        color: subscription.plan.badge_text_color
                                    }"
                                    class="font24"
                                >
                                    {{ subscription.plan.name }}
                                </Badge>
                                <Badge :class="getStatusColor(subscription.status)" class="font22">
                                    {{ getStatusText(subscription.status) }}
                                </Badge>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font22 color999">订单号</span>
                                    <span class="font22 colorz">{{ subscription.subscription_number }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font22 color999 flex items-center">
                                        <Calendar class="w-4 h-4 u-p-r-5" />
                                        开始时间
                                    </span>
                                    <span class="font22 colorz">{{ formatDateTime(subscription.started_at) }}</span>
                                </div>
                                <div v-if="subscription.expires_at" class="flex items-center justify-between">
                                    <span class="font22 color999 flex items-center">
                                        <Calendar class="w-4 h-4 u-p-r-5" />
                                        到期时间
                                    </span>
                                    <span class="font22 colorz">{{ formatDateTime(subscription.expires_at) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font22 color999 flex items-center">
                                        <CreditCard class="w-4 h-4 u-p-r-5" />
                                        支付金额
                                    </span>
                                    <span class="font22 text-[#ff6e02] font-w-600">¥{{ parseFloat(subscription.price_paid).toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="subscriptions.last_page > 1" class="flex items-center justify-center u-m-t-25 gap-3">
                    <Link
                        v-for="page in subscriptions.last_page"
                        :key="page"
                        :href="`/plan-subscriptions?page=${page}`"
                        class="w-10 h-10 flex items-center justify-center rounded-full"
                        :class="page === subscriptions.current_page ? 'bg-[#ff6e02] text-white' : 'bg-[#374151] text-[#999999]'"
                    >
                        <span class="font24">{{ page }}</span>
                    </Link>
                </div>
            </div>
        </div>
    </WebLayout>
</template>
