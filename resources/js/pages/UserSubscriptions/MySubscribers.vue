<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Users, Calendar, Coins, CreditCard } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
}

interface CreatorSubscriptionPlan {
    id: number;
    name: string;
    price: number;
    duration_days: number;
}

interface Subscriber {
    id: number;
    subscriber_id: number;
    creator_id: number;
    status: string;
    started_at: string;
    expires_at?: string;
    created_at: string;
    creator_amount: number;
    platform_amount: number;
    subscriber: User;
    creator_subscription_plan?: CreatorSubscriptionPlan;
}

interface Props {
    subscribers: {
        data: Subscriber[];
        links: any;
        meta: any;
    };
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

const getStatusBadge = (status: string) => {
    const badges = {
        active: { text: '活跃', class: 'bg-green-500' },
        expired: { text: '已过期', class: 'bg-gray-500' },
        cancelled: { text: '已取消', class: 'bg-red-500' },
    };
    return badges[status as keyof typeof badges] || { text: status, class: 'bg-gray-500' };
};

const translatePaginationLabel = (label: string) => {
    return label
        .replace(/&laquo;\s*Previous/i, '&laquo; 上一页')
        .replace(/Next\s*&raquo;/i, '下一页 &raquo;');
};
</script>

<template>
    <WebLayout>
        <Head title="我的被订阅记录" />

        <div class="min-h-screen py-8">
            <div class="max-w-4xl mx-auto px-4">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-white mb-2">我的被订阅记录</h1>
                    <p class="text-[#999999]">查看订阅你的用户</p>
                </div>

                <!-- Stats Card -->
                <Card class="bg-gradient-to-br from-[#ff6e02] to-[#e55a00] border-0 mb-8">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white/80 text-sm mb-1">总订阅人数</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-4xl font-bold text-white">{{ subscribers.total || 0 }}</span>
                                    <span class="text-white/80 text-sm">人</span>
                                </div>
                            </div>
                            <Users class="h-16 w-16 text-white/20" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Subscribers List -->
                <div v-if="subscribers.data.length > 0" class="space-y-4">
                    <Card
                        v-for="subscription in subscribers.data"
                        :key="subscription.id"
                        class="bg-[#374151] border-[#4B5563] hover:border-[#ff6e02] transition-colors"
                    >
                        <CardContent class="p-6">
                            <div class="flex items-start gap-4">
                                <!-- Subscriber Avatar -->
                                <Avatar class="w-16 h-16 border-2 border-[#4B5563]">
                                    <AvatarImage
                                        v-if="subscription.subscriber.avatar"
                                        :src="subscription.subscriber.avatar.startsWith('http')
                                            ? subscription.subscriber.avatar
                                            : `/storage/${subscription.subscriber.avatar}`"
                                        :alt="subscription.subscriber.name"
                                    />
                                    <AvatarFallback class="bg-[#4B5563] text-white text-lg">
                                        {{ subscription.subscriber.name.charAt(0).toUpperCase() }}
                                    </AvatarFallback>
                                </Avatar>

                                <!-- Subscription Info -->
                                <div class="flex-1">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <h3 class="text-lg font-semibold text-white">
                                                {{ subscription.subscriber.name }}
                                            </h3>
                                            <p class="text-sm text-[#999999]">
                                                {{ subscription.subscriber.email }}
                                            </p>
                                        </div>
                                        <Badge :class="getStatusBadge(subscription.status).class" class="text-white">
                                            {{ getStatusBadge(subscription.status).text }}
                                        </Badge>
                                    </div>

                                    <!-- Plan Info -->
                                    <div v-if="subscription.creator_subscription_plan" class="mb-3">
                                        <div class="inline-flex items-center gap-2 bg-[#1c1c1c] px-3 py-1 rounded-lg">
                                            <CreditCard class="h-4 w-4 text-[#ff6e02]" />
                                            <span class="text-sm text-white">{{ subscription.creator_subscription_plan.name }}</span>
                                            <span class="text-sm text-[#999999]">-</span>
                                            <span class="text-sm text-[#ff6e02]">{{ formatCredits(subscription.creator_subscription_plan.price) }} 金币</span>
                                        </div>
                                    </div>

                                    <!-- Revenue Info -->
                                    <div class="flex flex-wrap gap-4 text-sm mb-3">
                                        <div class="flex items-center gap-1">
                                            <Coins class="h-4 w-4 text-green-400" />
                                            <span class="text-[#999999]">你的收益:</span>
                                            <span class="text-green-400 font-semibold">{{ formatCredits(subscription.creator_amount) }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span class="text-[#999999]">平台分成:</span>
                                            <span class="text-white">{{ formatCredits(subscription.platform_amount) }}</span>
                                        </div>
                                    </div>

                                    <!-- Dates -->
                                    <div class="space-y-1 text-sm text-[#999999]">
                                        <div class="flex items-center gap-2">
                                            <Calendar class="h-4 w-4" />
                                            <span>开始: {{ formatDate(subscription.started_at) }}</span>
                                        </div>
                                        <div v-if="subscription.expires_at" class="flex items-center gap-2">
                                            <Calendar class="h-4 w-4" />
                                            <span>到期: {{ formatDate(subscription.expires_at) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Empty State -->
                <div v-else>
                    <Card class="bg-[#374151] border-[#4B5563]">
                        <CardContent class="p-12 text-center">
                            <div class="text-6xl mb-4">👥</div>
                            <h3 class="text-lg font-medium text-white mb-2">还没有订阅者</h3>
                            <p class="text-[#999999]">创作优质内容，吸引更多订阅者</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Pagination -->
                <div v-if="subscribers.links && subscribers.data.length > 0" class="mt-8 flex justify-center gap-2">
                    <Link
                        v-for="(link, index) in subscribers.links"
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
