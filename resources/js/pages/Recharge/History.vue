<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Coins, Calendar, CreditCard, Plus } from 'lucide-vue-next';

interface Order {
    id: number;
    order_number: string;
    total_amount: number;
    payment_method?: string;
    status: string;
}

interface Transaction {
    id: number;
    credits: number;
    reason: string;
    created_at: string;
    metadata?: {
        amount?: number;
        bonus?: number;
        order_number?: string;
    };
    order?: Order;
}

interface Props {
    transactions: {
        data: Transaction[];
        links: any;
        meta: any;
    };
    userCredits: number;
}

const props = defineProps<Props>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString('zh-CN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatCredits = (credits: number | string) => {
    const value = typeof credits === 'number' ? credits : parseFloat(String(credits)) || 0;
    return value.toFixed(2);
};

const translatePaginationLabel = (label: string) => {
    return label
        .replace(/&laquo;\s*Previous/i, '&laquo; 上一页')
        .replace(/Next\s*&raquo;/i, '下一页 &raquo;');
};
</script>

<template>
    <WebLayout>
        <Head title="充值记录" />

        <div class="min-h-screen py-8">
            <div class="max-w-4xl mx-auto px-4">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h1 class="text-3xl font-bold text-white mb-2">充值记录</h1>
                            <p class="text-[#999999]">查看你的所有充值历史</p>
                        </div>
                        <Link href="/recharge">
                            <button class="px-4 py-2 bg-[#ff6e02] hover:bg-[#e55a00] text-white rounded-lg transition-colors flex items-center gap-2">
                                <Plus class="h-4 w-4" />
                                充值
                            </button>
                        </Link>
                    </div>

                    <!-- Current Balance Card -->
                    <Card class="bg-gradient-to-br from-[#ff6e02] to-[#e55a00] border-0">
                        <CardContent class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-white/80 text-sm mb-1">当前余额</p>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-4xl font-bold text-white">{{ formatCredits(userCredits) }}</span>
                                        <span class="text-white/80 text-sm">金币</span>
                                    </div>
                                </div>
                                <Coins class="h-16 w-16 text-white/20" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Transactions List -->
                <div v-if="transactions.data.length > 0" class="space-y-4">
                    <Card
                        v-for="transaction in transactions.data"
                        :key="transaction.id"
                        class="bg-[#374151] border-[#4B5563]"
                    >
                        <CardContent class="p-6">
                            <div class="flex items-start justify-between">
                                <!-- Transaction Info -->
                                <div class="flex-1">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1">
                                            <CreditCard class="h-6 w-6 text-[#ff6e02]" />
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-white mb-1">充值成功</h3>

                                            <!-- Order Number -->
                                            <p v-if="transaction.order" class="text-sm text-[#999999] mb-2">
                                                订单号: {{ transaction.order.order_number }}
                                            </p>

                                            <!-- Details -->
                                            <div class="flex flex-wrap gap-4 text-sm text-[#999999] mb-3">
                                                <div v-if="transaction.metadata?.amount" class="flex items-center gap-1">
                                                    <span>充值金额:</span>
                                                    <span class="text-white">¥{{ transaction.metadata.amount }}</span>
                                                </div>
                                                <div v-if="transaction.metadata?.bonus && transaction.metadata.bonus > 0" class="flex items-center gap-1">
                                                    <span>赠送:</span>
                                                    <span class="text-[#ff6e02]">{{ transaction.metadata.bonus }} 金币</span>
                                                </div>
                                            </div>

                                            <!-- Date -->
                                            <div class="flex items-center gap-2 text-sm text-[#999999]">
                                                <Calendar class="h-4 w-4" />
                                                <span>{{ formatDate(transaction.created_at) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Credits Amount -->
                                <div class="text-right ml-4">
                                    <div class="text-2xl font-bold text-green-400 mb-1">
                                        +{{ formatCredits(transaction.credits) }}
                                    </div>
                                    <p class="text-xs text-[#999999]">金币</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Empty State -->
                <div v-else>
                    <Card class="bg-[#374151] border-[#4B5563]">
                        <CardContent class="p-12 text-center">
                            <div class="text-6xl mb-4">💳</div>
                            <h3 class="text-lg font-medium text-white mb-2">还没有充值记录</h3>
                            <p class="text-[#999999] mb-6">充值金币，享受更多服务</p>
                            <Link href="/recharge">
                                <button class="px-6 py-2 bg-[#ff6e02] hover:bg-[#e55a00] text-white rounded-lg transition-colors">
                                    立即充值
                                </button>
                            </Link>
                        </CardContent>
                    </Card>
                </div>

                <!-- Pagination -->
                <div v-if="transactions.links && transactions.data.length > 0" class="mt-8 flex justify-center gap-2">
                    <Link
                        v-for="(link, index) in transactions.links"
                        :key="index"
                        :href="link.url || '#'"
                        :class="[
                            'px-4 py-2 rounded-lg transition-colors',
                            link.active
                                ? 'bg-[#ff6e02] text-white'
                                : link.url
                                    ? 'bg-[#374151] text-white hover:bg-[#4B5563]'
                                    : 'bg-[#374151] text-[#999999] cursor-not-allowed'
                        ]"
                        v-html="translatePaginationLabel(link.label)"
                    />
                </div>
            </div>
        </div>
    </WebLayout>
</template>
