<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Head, Link, router } from '@inertiajs/vue3';
import { Star, Users, ChefHat, TrendingUp, Clock } from 'lucide-vue-next';

interface Creator {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    is_creator: boolean;
    follower_count: number;
    following_count: number;
    posts_count: number;
    likes_received: number;
    display_name: string;
    specialty?: string;
    bio?: string;
    verification_status: string;
    created_at: string;
    creator_profile?: {
        id: number;
        display_name: string;
        specialty: string;
        bio: string;
        verification_status: string;
        rating: number;
        follower_count: number;
    };
}

interface PaginatedCreators {
    data: Creator[];
    links: any[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Props {
    creators: PaginatedCreators;
}

const props = defineProps<Props>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: 'long',
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
        <Head title="创作者" />

        <div class="min-h-screen py-4 md:py-8">
            <div class="max-w-6xl mx-auto px-3 md:px-4">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4 md:mb-8">
                    <div>
                        <h1 class="text-xl md:text-3xl font-bold text-white mb-1 md:mb-2 flex items-center gap-2 md:gap-3">
                            <ChefHat class="h-6 w-6 md:h-8 md:w-8 text-[#ff6e02]" />
                            创作者
                        </h1>
                        <p class="text-xs md:text-base text-[#999999]">发现并关注优秀的内容创作者</p>
                    </div>
                </div>

                <!-- Results Count -->
                <div v-if="creators" class="flex items-center justify-between mb-4 md:mb-6 text-xs md:text-sm text-[#999999]">
                    <p>
                        共 {{ creators.total }} 位创作者
                    </p>
                    <p v-if="creators.last_page > 1">
                        第 {{ creators.current_page }} / {{ creators.last_page }} 页
                    </p>
                </div>

                <!-- Creators Grid -->
                <div v-if="creators?.data" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 md:gap-6">
                    <Card v-for="creator in creators.data" :key="creator.id" class="bg-[#374151] border-[#4B5563] hover:border-[#6B7280] transition-all flex flex-col h-full">
                        <CardHeader class="text-center pb-2 p-3 md:p-6">
                            <!-- Avatar -->
                            <div class="flex justify-center mb-2 md:mb-4">
                                <Link :href="`/creators/${creator.creator_profile?.id || creator.id}`">
                                    <img
                                        :src="creator.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(creator.display_name)}&size=80&background=ff6e02&color=fff`"
                                        class="w-14 h-14 md:w-20 md:h-20 rounded-full object-cover border-2 border-[#ff6e02]"
                                        :alt="creator.display_name"
                                        @error="(e) => e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(creator.display_name)}&size=80&background=ff6e02&color=fff`"
                                    />
                                </Link>
                            </div>

                            <!-- Verification Badge -->
                            <div class="flex items-center justify-center gap-2 mb-1 md:mb-2">
                                <Badge
                                    v-if="creator.verification_status === 'verified'"
                                    class="bg-green-500/20 text-green-500 border-green-500/30 text-[10px] md:text-xs px-1.5 py-0.5"
                                >
                                    ✓ 已认证
                                </Badge>
                                <Badge
                                    v-else-if="creator.verification_status === 'pending'"
                                    class="bg-yellow-500/20 text-yellow-500 border-yellow-500/30 text-[10px] md:text-xs px-1.5 py-0.5"
                                >
                                    审核中
                                </Badge>
                            </div>

                            <!-- Name -->
                            <CardTitle class="text-sm md:text-xl text-white">
                                <Link :href="`/creators/${creator.creator_profile?.id || creator.id}`" class="hover:text-[#ff6e02]">
                                    {{ creator.display_name }}
                                </Link>
                            </CardTitle>

                            <!-- Specialty -->
                            <CardDescription v-if="creator.specialty" class="text-[#999999] line-clamp-1 text-xs md:text-sm">
                                {{ creator.specialty }}
                            </CardDescription>

                            <!-- Stats -->
                            <div class="flex items-center justify-center gap-2 md:gap-4 mt-2 md:mt-3 text-[10px] md:text-sm text-[#999999]">
                                <div class="flex items-center gap-0.5 md:gap-1">
                                    <Users class="w-3 h-3 md:w-4 md:h-4" />
                                    <span class="hidden md:inline">{{ creator.follower_count }}</span>
                                    <span class="md:hidden">{{ creator.follower_count > 999 ? Math.floor(creator.follower_count / 1000) + 'k' : creator.follower_count }}</span>
                                </div>
                                <div class="flex items-center gap-0.5 md:gap-1">
                                    <TrendingUp class="w-3 h-3 md:w-4 md:h-4" />
                                    <span class="hidden md:inline">{{ creator.posts_count }}</span>
                                    <span class="md:hidden">{{ creator.posts_count > 999 ? Math.floor(creator.posts_count / 1000) + 'k' : creator.posts_count }}</span>
                                </div>
                                <div class="flex items-center gap-0.5 md:gap-1">
                                    <Star class="w-3 h-3 md:w-4 md:h-4 fill-current text-[#ff6e02]" />
                                    <span class="hidden md:inline">{{ creator.likes_received }}</span>
                                    <span class="md:hidden">{{ creator.likes_received > 999 ? Math.floor(creator.likes_received / 1000) + 'k' : creator.likes_received }}</span>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="space-y-2 md:space-y-4 flex flex-col flex-1 p-3 md:p-6 pt-0">
                            <!-- Bio - hidden on mobile -->
                            <p v-if="creator.bio" class="hidden md:block text-sm text-[#999999] line-clamp-2">
                                {{ creator.bio }}
                            </p>
                            <p v-else class="hidden md:block text-sm text-[#666666] italic">
                                暂无简介
                            </p>

                            <!-- Joined Date - hidden on mobile -->
                            <div class="hidden md:flex items-center gap-2 text-xs text-[#999999]">
                                <Clock class="w-3 h-3" />
                                加入于 {{ formatDate(creator.created_at) }}
                            </div>

                            <!-- View Profile Button -->
                            <Button class="w-full bg-[#ff6e02] hover:bg-[#e55a00] text-white mt-auto text-xs md:text-sm py-1.5 md:py-2" as-child>
                                <Link :href="`/creators/${creator.creator_profile?.id || creator.id}`">
                                    查看主页
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>

                <!-- Empty State -->
                <div v-if="creators?.data && creators.data.length === 0" class="text-center py-12">
                    <ChefHat class="h-16 w-16 text-[#999999] mx-auto mb-4" />
                    <h3 class="text-lg font-semibold text-white mb-2">暂无创作者</h3>
                    <p class="text-[#999999]">
                        还没有创作者加入
                    </p>
                </div>

                <!-- Pagination -->
                <div v-if="creators && creators.last_page > 1" class="flex items-center justify-center gap-1 md:gap-2 mt-6 md:mt-8">
                    <Button
                        v-for="link in creators?.links || []"
                        :key="link.label"
                        :variant="link.active ? 'default' : 'outline'"
                        :disabled="!link.url"
                        size="sm"
                        @click="link.url && router.visit(link.url, { preserveState: true })"
                        v-html="translatePaginationLabel(link.label)"
                        :class="{
                            'bg-[#ff6e02] hover:bg-[#e55a00]': link.active,
                            'border-[#4B5563] text-white hover:bg-[#4B5563]': !link.active,
                            'text-xs px-2 py-1 md:text-sm md:px-3 md:py-2': true
                        }"
                    />
                </div>
            </div>
        </div>
    </WebLayout>
</template>
