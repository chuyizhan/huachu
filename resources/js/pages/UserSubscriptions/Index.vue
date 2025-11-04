<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Calendar, Star, Clock } from 'lucide-vue-next';

interface Creator {
    id: number;
    name: string;
    avatar?: string;
    creator_profile?: {
        id: number;
        display_name: string;
        specialty: string;
    };
}

interface SubscriptionPlan {
    id: number;
    name: string;
    duration_days: number;
    price: number;
}

interface Subscription {
    id: number;
    status: 'active' | 'expired' | 'cancelled';
    started_at: string;
    expires_at: string | null;
    creator: Creator;
    creator_subscription_plan?: SubscriptionPlan;
}

interface Props {
    subscriptions: {
        data: Subscription[];
        links: any;
        meta: any;
    };
}

const props = defineProps<Props>();

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'active':
            return { class: 'bg-green-500', text: '订阅中' };
        case 'expired':
            return { class: 'bg-gray-500', text: '已过期' };
        case 'cancelled':
            return { class: 'bg-red-500', text: '已取消' };
        default:
            return { class: 'bg-gray-500', text: status };
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const translatePaginationLabel = (label: string) => {
    return label
        .replace(/&laquo;\s*Previous/i, '&laquo; 上一页')
        .replace(/Next\s*&raquo;/i, '下一页 &raquo;');
};
</script>

<template>
    <WebLayout>
        <Head title="博主订阅" />

        <div class="min-h-screen py-8">
            <div class="max-w-4xl mx-auto px-4">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-white mb-2">我的博主订阅</h1>
                    <p class="text-[#999999]">管理你订阅的所有博主</p>
                </div>

                <!-- Subscriptions List -->
                <div v-if="subscriptions.data.length > 0" class="space-y-4">
                    <Card
                        v-for="subscription in subscriptions.data"
                        :key="subscription.id"
                        class="bg-[#374151] border-[#4B5563] hover:border-[#ff6e02] transition-colors"
                    >
                        <CardContent class="p-6">
                            <div class="flex items-start gap-4">
                                <!-- Creator Avatar -->
                                <Link :href="`/creators/${subscription.creator.creator_profile?.id}`">
                                    <Avatar class="w-16 h-16 border-2 border-[#4B5563]">
                                        <AvatarImage
                                            v-if="subscription.creator.avatar"
                                            :src="subscription.creator.avatar.startsWith('http')
                                                ? subscription.creator.avatar
                                                : `/storage/${subscription.creator.avatar}`"
                                            :alt="subscription.creator.creator_profile?.display_name || subscription.creator.name"
                                        />
                                        <AvatarFallback class="bg-[#4B5563] text-white text-lg">
                                            {{ (subscription.creator.creator_profile?.display_name || subscription.creator.name).charAt(0).toUpperCase() }}
                                        </AvatarFallback>
                                    </Avatar>
                                </Link>

                                <!-- Subscription Info -->
                                <div class="flex-1">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <Link :href="`/creators/${subscription.creator.creator_profile?.id}`">
                                                <h3 class="text-lg font-semibold text-white hover:text-[#ff6e02] transition-colors">
                                                    {{ subscription.creator.creator_profile?.display_name || subscription.creator.name }}
                                                </h3>
                                            </Link>
                                            <p v-if="subscription.creator.creator_profile?.specialty" class="text-sm text-[#999999]">
                                                {{ subscription.creator.creator_profile.specialty }}
                                            </p>
                                        </div>
                                        <Badge :class="getStatusBadge(subscription.status).class" class="text-white">
                                            {{ getStatusBadge(subscription.status).text }}
                                        </Badge>
                                    </div>

                                    <!-- Plan Info -->
                                    <div v-if="subscription.creator_subscription_plan" class="mb-3">
                                        <div class="inline-flex items-center gap-2 bg-[#1c1c1c] px-3 py-1 rounded-lg">
                                            <Star class="h-4 w-4 text-[#ff6e02]" />
                                            <span class="text-sm text-white">{{ subscription.creator_subscription_plan.name }}</span>
                                        </div>
                                    </div>

                                    <!-- Dates -->
                                    <div class="flex flex-wrap gap-4 text-sm text-[#999999]">
                                        <div class="flex items-center gap-2">
                                            <Calendar class="h-4 w-4" />
                                            <span>开始: {{ formatDate(subscription.started_at) }}</span>
                                        </div>
                                        <div v-if="subscription.expires_at" class="flex items-center gap-2">
                                            <Clock class="h-4 w-4" />
                                            <span>到期: {{ formatDate(subscription.expires_at) }}</span>
                                        </div>
                                        <div v-else class="flex items-center gap-2">
                                            <Clock class="h-4 w-4" />
                                            <span class="text-[#ff6e02]">永久订阅</span>
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
                            <div class="text-6xl mb-4">📭</div>
                            <h3 class="text-lg font-medium text-white mb-2">还没有订阅</h3>
                            <p class="text-[#999999] mb-6">发现喜欢的博主，订阅他们的专享内容</p>
                            <Link href="/creators">
                                <button class="px-6 py-2 bg-[#ff6e02] hover:bg-[#e55a00] text-white rounded-lg transition-colors">
                                    浏览博主
                                </button>
                            </Link>
                        </CardContent>
                    </Card>
                </div>

                <!-- Pagination -->
                <div v-if="subscriptions.links && subscriptions.data.length > 0" class="mt-8 flex justify-center gap-2">
                    <Link
                        v-for="(link, index) in subscriptions.links"
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
