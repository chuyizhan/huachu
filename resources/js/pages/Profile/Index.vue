<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Settings, ChevronRight, Coins, Star, Users, Heart, FileText, ShoppingBag, Crown, Gift, MessageSquare, Award, UserPlus, CreditCard, LogOut } from 'lucide-vue-next';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';

interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    credits: number;
    points: number;
    is_creator: boolean;
    created_at: string;
    creator_profile?: {
        id: number;
        display_name: string;
        verification_status: string;
    };
}

interface PlanSubscription {
    id: number;
    subscription_number: string;
    status: string;
    started_at: string;
    expires_at: string | null;
    auto_renew: boolean;
    plan: {
        id: number;
        name: string;
        slug: string;
        level: number;
        badge_color: string;
        badge_text_color: string;
    };
}

interface Props {
    user: User;
    stats: {
        posts_count: number;
        followers_count: number;
        following_count: number;
        favorites_count: number;
        subscriptions_count: number;
    };
    activePlanSubscription?: PlanSubscription | null;
}

const props = defineProps<Props>();

const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
};

const logout = () => {
    if (confirm('确认退出登录吗？')) {
        router.post('/logout');
    }
};

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
        <Head title="个人中心" />

        <div class="min-h-screen bg-[#1c1c1c] pb-20">
            <!-- Header -->
            <div class="bg-[#1f2937] u-p-25">
                <div class="flex items-center justify-between u-m-b-25">
                    <h1 class="colorz font32 font-w-600">个人中心</h1>
                    <Link href="/settings/profile">
                        <Settings class="w-6 h-6 text-white" />
                    </Link>
                </div>

                <!-- User Info -->
                <div class="flex items-start u-m-b-25">
                    <Link href="/settings/profile">
                        <Avatar class="w-16 h-16">
                            <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                            <AvatarFallback class="bg-[#ff6e02] text-white text-xl">
                                {{ getInitials(user.name) }}
                            </AvatarFallback>
                        </Avatar>
                    </Link>
                    <div class="flex-1 u-p-l-25">
                        <div class="flex items-center u-m-b-10">
                            <span class="colorfff font28 font-w-500 u-p-r-25">
                                {{ user.creator_profile?.display_name || user.name }}
                            </span>
                            <!-- VIP Badge -->
                            <Badge
                                v-if="activePlanSubscription"
                                :style="{
                                    backgroundColor: activePlanSubscription.plan.badge_color,
                                    color: activePlanSubscription.plan.badge_text_color
                                }"
                                class="text-xs u-p-r-10"
                            >
                                <Crown class="w-3 h-3 u-p-r-5 inline" />
                                {{ activePlanSubscription.plan.name }}
                            </Badge>
                            <!-- Creator Badge -->
                            <Badge v-if="user.creator_profile?.verification_status === 'verified'" class="bg-[#ff6e02] text-white text-xs">
                                已认证
                            </Badge>
                            <Badge v-else-if="user.is_creator" class="bg-[#374151] text-[#ff6e02] text-xs">
                                未认证
                            </Badge>
                        </div>
                        <div class="flex items-center u-m-b-15">
                            <span class="color999 font22">用户ID：{{ user.id }}</span>
                            <span class="color999 font22 u-p-l-25">
                                加入时间：{{ formatDate(user.created_at) }}
                            </span>
                        </div>

                        <!-- Stats -->
                        <div class="flex items-center gap-6">
                            <div class="flex flex-col items-center">
                                <span class="font28 colorz font-w-600">{{ stats.posts_count }}</span>
                                <span class="color999 font22">发帖</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="font28 colorz font-w-600">{{ stats.followers_count }}</span>
                                <span class="color999 font22">粉丝</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="font28 colorz font-w-600">{{ stats.following_count }}</span>
                                <span class="color999 font22">关注</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="font28 colorz font-w-600">{{ stats.favorites_count }}</span>
                                <span class="color999 font22">收藏</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="font28 colorz font-w-600">{{ stats.subscriptions_count }}</span>
                                <span class="color999 font22">订阅</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plan Subscription Status -->
            <div v-if="activePlanSubscription" class="u-m-25">
                <div class="listclass u-p-25">
                    <div class="flex items-center justify-between u-m-b-20">
                        <h3 class="font28 colorfff font-w-600">会员订阅状态</h3>
                        <Link :href="`/plan-subscriptions/${activePlanSubscription.id}`">
                            <span class="font22 text-[#ff6e02]">详情</span>
                        </Link>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font24 color999">当前套餐</span>
                            <Badge
                                :style="{
                                    backgroundColor: activePlanSubscription.plan.badge_color,
                                    color: activePlanSubscription.plan.badge_text_color
                                }"
                                class="font22"
                            >
                                {{ activePlanSubscription.plan.name }}
                            </Badge>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font24 color999">状态</span>
                            <Badge :class="getStatusColor(activePlanSubscription.status)" class="font22">
                                {{ getStatusText(activePlanSubscription.status) }}
                            </Badge>
                        </div>
                        <div v-if="activePlanSubscription.expires_at" class="flex items-center justify-between">
                            <span class="font24 color999">到期时间</span>
                            <span class="font24 colorz">{{ formatDateTime(activePlanSubscription.expires_at) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font24 color999">自动续费</span>
                            <Badge :class="activePlanSubscription.auto_renew ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400'" class="font22">
                                {{ activePlanSubscription.auto_renew ? '已开启' : '已关闭' }}
                            </Badge>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menu Items -->
            <div class="u-m-25">
                <div class="listclass space-y-0">
                    <!-- VIP -->
                    <Link href="/plan-subscriptions/create" class="flex items-center justify-between u-p-25 border-b border-[#374151]">
                        <div class="flex items-center">
                            <Crown class="w-7 h-7 text-white" />
                            <span class="font28 colorfff u-p-l-15">VIP会员</span>
                        </div>
                        <div class="flex items-center">
                            <ChevronRight class="w-5 h-5 text-[#999999]" />
                        </div>
                    </Link>

                    <!-- Creator Application -->
                    <Link href="/creators/apply" class="flex items-center justify-between u-p-25 border-b border-[#374151]">
                        <div class="flex items-center">
                            <Award class="w-7 h-7 text-white" />
                            <span class="font28 colorfff u-p-l-15">申请认证</span>
                        </div>
                        <div class="flex items-center">
                            <ChevronRight class="w-5 h-5 text-[#999999]" />
                        </div>
                    </Link>

                    <!-- My Credits -->
                    <Link href="/recharge" class="flex items-center justify-between u-p-25 border-b border-[#374151]">
                        <div class="flex items-center">
                            <Coins class="w-7 h-7 text-white" />
                            <span class="font28 colorfff u-p-l-15">我的金币</span>
                        </div>
                        <div class="flex items-center">
                            <span class="colorz font32 u-p-r-20">{{ parseFloat(user.credits).toFixed(2) }}</span>
                            <ChevronRight class="w-5 h-5 text-[#999999]" />
                        </div>
                    </Link>

                    <!-- My Points -->
                    <Link href="/points/rules" class="flex items-center justify-between u-p-25 border-b border-[#374151]">
                        <div class="flex items-center">
                            <Star class="w-7 h-7 text-white" />
                            <span class="font28 colorfff u-p-l-15">我的积分</span>
                        </div>
                        <div class="flex items-center">
                            <span class="colorz font32 u-p-r-20">{{ user.points }}</span>
                            <ChevronRight class="w-5 h-5 text-[#999999]" />
                        </div>
                    </Link>

                    <!-- My Posts -->
                    <Link href="/posts" class="flex items-center justify-between u-p-25 border-b border-[#374151]">
                        <div class="flex items-center">
                            <FileText class="w-7 h-7 text-white" />
                            <span class="font28 colorfff u-p-l-15">我的发帖</span>
                        </div>
                        <div class="flex items-center">
                            <span class="colorz font32 u-p-r-20">{{ stats.posts_count }}</span>
                            <ChevronRight class="w-5 h-5 text-[#999999]" />
                        </div>
                    </Link>

                    <!-- My Favorites -->
                    <Link href="/favorites" class="flex items-center justify-between u-p-25 border-b border-[#374151]">
                        <div class="flex items-center">
                            <Heart class="w-7 h-7 text-white" />
                            <span class="font28 colorfff u-p-l-15">我的收藏</span>
                        </div>
                        <div class="flex items-center">
                            <span class="colorz font32 u-p-r-20">{{ stats.favorites_count }}</span>
                            <ChevronRight class="w-5 h-5 text-[#999999]" />
                        </div>
                    </Link>

                    <!-- My Plan Subscriptions -->
                    <Link href="/plan-subscriptions" class="flex items-center justify-between u-p-25 border-b border-[#374151]">
                        <div class="flex items-center">
                            <Users class="w-7 h-7 text-white" />
                            <span class="font28 colorfff u-p-l-15">我的会员订阅</span>
                        </div>
                        <div class="flex items-center">
                            <ChevronRight class="w-5 h-5 text-[#999999]" />
                        </div>
                    </Link>

                    <!-- Feedback -->
                    <Link href="/feedback" class="flex items-center justify-between u-p-25 border-b border-[#374151]">
                        <div class="flex items-center">
                            <MessageSquare class="w-7 h-7 text-white" />
                            <span class="font28 colorfff u-p-l-15">问题反馈</span>
                        </div>
                        <div class="flex items-center">
                            <ChevronRight class="w-5 h-5 text-[#999999]" />
                        </div>
                    </Link>

                    <!-- Points Rules -->
                    <Link href="/points/rules" class="flex items-center justify-between u-p-25 border-b border-[#374151]">
                        <div class="flex items-center">
                            <Gift class="w-7 h-7 text-white" />
                            <span class="font28 colorfff u-p-l-15">积分规则</span>
                        </div>
                        <div class="flex items-center">
                            <ChevronRight class="w-5 h-5 text-[#999999]" />
                        </div>
                    </Link>

                    <!-- Recharge -->
                    <Link href="/recharge" class="flex items-center justify-between u-p-25">
                        <div class="flex items-center">
                            <CreditCard class="w-7 h-7 text-white" />
                            <span class="font28 colorfff u-p-l-15">充值</span>
                        </div>
                        <div class="flex items-center">
                            <ChevronRight class="w-5 h-5 text-[#999999]" />
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Logout Button -->
            <div class="u-m-25">
                <button
                    @click="logout"
                    class="w-full listclass flex items-center justify-center u-p-25"
                >
                    <LogOut class="w-5 h-5 text-white" />
                    <span class="font30 colorfff u-p-l-15">注销登录</span>
                </button>
            </div>
        </div>
    </WebLayout>
</template>
