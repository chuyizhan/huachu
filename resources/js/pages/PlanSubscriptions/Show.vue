<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Crown, ArrowLeft, Calendar, CreditCard, RefreshCw, X, Settings, CheckCircle, XCircle, Clock } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

interface Plan {
    id: number;
    name: string;
    slug: string;
    level: number;
    price: string;
    period_days: number;
    badge_color: string;
    badge_text_color: string;
}

interface PlanSubscription {
    id: number;
    subscription_number: string;
    status: string;
    price_paid: string;
    payment_method: string;
    payment_transaction_id: string | null;
    started_at: string;
    expires_at: string | null;
    trial_ends_at: string | null;
    cancelled_at: string | null;
    cancellation_reason: string | null;
    auto_renew: boolean;
    last_renewed_at: string | null;
    renewal_count: number;
    plan: Plan;
}

interface Props {
    subscription: PlanSubscription;
}

const props = defineProps<Props>();

const cancelForm = useForm({
    reason: '',
});

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
        'active': '当前有效',
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

const getStatusIcon = (status: string) => {
    const iconMap: Record<string, any> = {
        'active': CheckCircle,
        'cancelled': XCircle,
        'expired': Clock,
        'suspended': XCircle,
        'trial': CheckCircle
    };
    return iconMap[status] || CheckCircle;
};

const getPaymentMethodText = (method: string) => {
    const methodMap: Record<string, string> = {
        'alipay': '支付宝',
        'wechat': '微信支付',
        'credits': '金币支付',
        'card': '银行卡',
    };
    return methodMap[method] || method;
};

const renewSubscription = () => {
    if (confirm('确认续费此订阅吗？')) {
        router.post(`/plan-subscriptions/${props.subscription.id}/renew`, {}, {
            preserveScroll: true,
        });
    }
};

const cancelSubscription = () => {
    const reason = prompt('请输入取消原因（可选）：');
    if (reason !== null) {
        cancelForm.reason = reason;
        cancelForm.post(`/plan-subscriptions/${props.subscription.id}/cancel`, {
            preserveScroll: true,
        });
    }
};

const toggleAutoRenew = () => {
    router.put(`/plan-subscriptions/${props.subscription.id}`, {
        auto_renew: !props.subscription.auto_renew,
    }, {
        preserveScroll: true,
    });
};

const getDaysRemaining = () => {
    if (!props.subscription.expires_at) return null;
    const now = new Date();
    const expiresAt = new Date(props.subscription.expires_at);
    const diff = expiresAt.getTime() - now.getTime();
    const days = Math.ceil(diff / (1000 * 60 * 60 * 24));
    return days;
};

const isActive = () => {
    return props.subscription.status === 'active';
};

const isCancelled = () => {
    return props.subscription.status === 'cancelled';
};
</script>

<template>
    <WebLayout>
        <Head :title="`订阅详情 - ${subscription.subscription_number}`" />

        <div class="min-h-screen bg-[#1c1c1c] pb-20">
            <!-- Header -->
            <div class="bg-[#1f2937] u-p-25 sticky top-0 z-10">
                <div class="flex items-center">
                    <Link href="/plan-subscriptions" class="u-p-r-25">
                        <ArrowLeft class="w-6 h-6 text-white" />
                    </Link>
                    <h1 class="colorz font32 font-w-600">订阅详情</h1>
                </div>
            </div>

            <!-- Status Card -->
            <div class="u-m-25">
                <div class="listclass u-p-25">
                    <div class="flex items-center justify-center u-m-b-20">
                        <component
                            :is="getStatusIcon(subscription.status)"
                            class="w-16 h-16"
                            :class="subscription.status === 'active' ? 'text-green-400' : subscription.status === 'cancelled' ? 'text-red-400' : 'text-gray-400'"
                        />
                    </div>
                    <div class="text-center u-m-b-20">
                        <Badge :class="getStatusColor(subscription.status)" class="font28 u-p-15">
                            {{ getStatusText(subscription.status) }}
                        </Badge>
                    </div>
                    <div v-if="isActive() && subscription.expires_at && getDaysRemaining() !== null" class="text-center">
                        <p class="font24 color999 u-m-b-5">距离到期还有</p>
                        <p class="font40 text-[#ff6e02] font-w-700">{{ getDaysRemaining() }} 天</p>
                    </div>
                </div>
            </div>

            <!-- Plan Info -->
            <div class="u-m-25">
                <div class="listclass u-p-25">
                    <div class="flex items-center u-m-b-20">
                        <Crown class="w-6 h-6 text-[#ff6e02] u-p-r-10" />
                        <h3 class="font28 colorfff font-w-600">套餐信息</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font24 color999">套餐名称</span>
                            <Badge
                                :style="{
                                    backgroundColor: subscription.plan.badge_color,
                                    color: subscription.plan.badge_text_color
                                }"
                                class="font24"
                            >
                                {{ subscription.plan.name }}
                            </Badge>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font24 color999">套餐等级</span>
                            <span class="font24 colorz">LV{{ subscription.plan.level }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font24 color999">支付金额</span>
                            <span class="font24 text-[#ff6e02] font-w-600">¥{{ parseFloat(subscription.price_paid).toFixed(2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Details -->
            <div class="u-m-25">
                <div class="listclass u-p-25">
                    <div class="flex items-center u-m-b-20">
                        <Calendar class="w-6 h-6 text-[#ff6e02] u-p-r-10" />
                        <h3 class="font28 colorfff font-w-600">订阅详情</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font24 color999">订单号</span>
                            <span class="font24 colorz">{{ subscription.subscription_number }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font24 color999">开始时间</span>
                            <span class="font24 colorz">{{ formatDateTime(subscription.started_at) }}</span>
                        </div>
                        <div v-if="subscription.expires_at" class="flex items-center justify-between">
                            <span class="font24 color999">到期时间</span>
                            <span class="font24 colorz">{{ formatDateTime(subscription.expires_at) }}</span>
                        </div>
                        <div v-if="subscription.last_renewed_at" class="flex items-center justify-between">
                            <span class="font24 color999">最后续费</span>
                            <span class="font24 colorz">{{ formatDateTime(subscription.last_renewed_at) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font24 color999">续费次数</span>
                            <span class="font24 colorz">{{ subscription.renewal_count }} 次</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="u-m-25">
                <div class="listclass u-p-25">
                    <div class="flex items-center u-m-b-20">
                        <CreditCard class="w-6 h-6 text-[#ff6e02] u-p-r-10" />
                        <h3 class="font28 colorfff font-w-600">支付信息</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font24 color999">支付方式</span>
                            <span class="font24 colorz">{{ getPaymentMethodText(subscription.payment_method) }}</span>
                        </div>
                        <div v-if="subscription.payment_transaction_id" class="flex items-center justify-between">
                            <span class="font24 color999">交易号</span>
                            <span class="font22 colorz break-all">{{ subscription.payment_transaction_id }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Auto Renew Settings -->
            <div v-if="isActive()" class="u-m-25">
                <div class="listclass u-p-25">
                    <div class="flex items-center u-m-b-20">
                        <Settings class="w-6 h-6 text-[#ff6e02] u-p-r-10" />
                        <h3 class="font28 colorfff font-w-600">订阅设置</h3>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font28 colorfff font-w-600 u-m-b-5">自动续费</h4>
                            <p class="font22 color999">到期后自动续费，可随时关闭</p>
                        </div>
                        <div
                            @click="toggleAutoRenew"
                            class="w-14 h-8 rounded-full cursor-pointer transition-all relative"
                            :class="subscription.auto_renew ? 'bg-[#ff6e02]' : 'bg-[#374151]'"
                        >
                            <div
                                class="w-6 h-6 bg-white rounded-full absolute top-1 transition-all"
                                :class="subscription.auto_renew ? 'left-7' : 'left-1'"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cancellation Info -->
            <div v-if="isCancelled()" class="u-m-25">
                <div class="listclass u-p-25 bg-red-500/10">
                    <h3 class="font28 text-red-400 font-w-600 u-m-b-15">取消信息</h3>
                    <div class="space-y-3">
                        <div v-if="subscription.cancelled_at" class="flex items-center justify-between">
                            <span class="font24 color999">取消时间</span>
                            <span class="font24 text-red-400">{{ formatDateTime(subscription.cancelled_at) }}</span>
                        </div>
                        <div v-if="subscription.cancellation_reason">
                            <span class="font24 color999 block u-m-b-10">取消原因</span>
                            <p class="font24 colorz">{{ subscription.cancellation_reason }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="u-m-25 space-y-3">
                <Button
                    v-if="isActive()"
                    @click="renewSubscription"
                    class="w-full bg-[#ff6e02] hover:bg-[#ff8534] text-white font30 u-p-25 h-auto rounded-lg"
                >
                    <RefreshCw class="w-6 h-6 u-p-r-10" />
                    立即续费
                </Button>

                <Button
                    v-if="isActive() && !isCancelled()"
                    @click="cancelSubscription"
                    variant="outline"
                    class="w-full border-red-500 text-red-400 hover:bg-red-500/10 font28 u-p-25 h-auto rounded-lg"
                >
                    <X class="w-6 h-6 u-p-r-10" />
                    取消订阅
                </Button>
            </div>
        </div>
    </WebLayout>
</template>
