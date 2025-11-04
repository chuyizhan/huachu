<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Users, TrendingUp, Calendar } from 'lucide-vue-next';
import { usePagination } from '@/composables/usePagination';

interface User {
    id: number;
    name: string;
    avatar?: string;
}

interface CreatorProfile {
    id: number;
    display_name: string;
    specialty?: string;
    bio?: string;
    verification_status: string;
    user: User;
}

interface Following {
    id: number;
    follower_id: number;
    creator_id: number;
    created_at: string;
    creator: CreatorProfile;
}

interface Props {
    following: {
        data: Following[];
        links: any;
        meta: any;
    };
}

const props = defineProps<Props>();

const { translatePaginationLabel } = usePagination();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getVerificationBadge = (status: string) => {
    if (status === 'verified') {
        return { text: '✓ 已认证', class: 'bg-green-500' };
    } else if (status === 'pending') {
        return { text: '审核中', class: 'bg-yellow-500' };
    }
    return null;
};
</script>

<template>
    <WebLayout>
        <Head title="我的关注" />

        <div class="min-h-screen py-8">
            <div class="max-w-4xl mx-auto px-4">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-white mb-2">我的关注</h1>
                    <p class="text-[#999999]">你关注的所有创作者</p>
                </div>

                <!-- Stats Card -->
                <Card class="bg-gradient-to-br from-[#ff6e02] to-[#e55a00] border-0 mb-8">
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white/80 text-sm mb-1">关注总数</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-4xl font-bold text-white">{{ following.total || 0 }}</span>
                                    <span class="text-white/80 text-sm">位创作者</span>
                                </div>
                            </div>
                            <Users class="h-16 w-16 text-white/20" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Following List -->
                <div v-if="following.data.length > 0" class="space-y-4">
                    <Card
                        v-for="follow in following.data"
                        :key="follow.id"
                        class="bg-[#374151] border-[#4B5563] hover:border-[#ff6e02] transition-colors"
                    >
                        <CardContent class="p-6">
                            <div class="flex items-start gap-4">
                                <!-- Creator Avatar -->
                                <Link :href="`/creators/${follow.creator.id}`">
                                    <Avatar class="w-16 h-16 border-2 border-[#4B5563]">
                                        <AvatarImage
                                            v-if="follow.creator.user.avatar"
                                            :src="follow.creator.user.avatar.startsWith('http')
                                                ? follow.creator.user.avatar
                                                : `/storage/${follow.creator.user.avatar}`"
                                            :alt="follow.creator.display_name"
                                        />
                                        <AvatarFallback class="bg-[#4B5563] text-white text-lg">
                                            {{ follow.creator.display_name.charAt(0).toUpperCase() }}
                                        </AvatarFallback>
                                    </Avatar>
                                </Link>

                                <!-- Creator Info -->
                                <div class="flex-1">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <Link :href="`/creators/${follow.creator.id}`">
                                                <h3 class="text-lg font-semibold text-white hover:text-[#ff6e02] transition-colors">
                                                    {{ follow.creator.display_name }}
                                                </h3>
                                            </Link>
                                            <p v-if="follow.creator.specialty" class="text-sm text-[#999999]">
                                                {{ follow.creator.specialty }}
                                            </p>
                                        </div>
                                        <Badge
                                            v-if="getVerificationBadge(follow.creator.verification_status)"
                                            :class="getVerificationBadge(follow.creator.verification_status)!.class"
                                            class="text-white"
                                        >
                                            {{ getVerificationBadge(follow.creator.verification_status)!.text }}
                                        </Badge>
                                    </div>

                                    <!-- Bio -->
                                    <p v-if="follow.creator.bio" class="text-sm text-[#999999] mb-3 line-clamp-2">
                                        {{ follow.creator.bio }}
                                    </p>

                                    <!-- Follow Date -->
                                    <div class="flex items-center gap-2 text-sm text-[#999999]">
                                        <Calendar class="h-4 w-4" />
                                        <span>关注于 {{ formatDate(follow.created_at) }}</span>
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
                            <div class="text-6xl mb-4">👤</div>
                            <h3 class="text-lg font-medium text-white mb-2">还没有关注任何创作者</h3>
                            <p class="text-[#999999] mb-6">发现感兴趣的创作者并关注他们</p>
                            <Link href="/creators">
                                <button class="px-6 py-2 bg-[#ff6e02] hover:bg-[#e55a00] text-white rounded-lg transition-colors">
                                    浏览创作者
                                </button>
                            </Link>
                        </CardContent>
                    </Card>
                </div>

                <!-- Pagination -->
                <div v-if="following.links && following.data.length > 0" class="mt-8 flex justify-center gap-2">
                    <Link
                        v-for="(link, index) in following.links"
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
