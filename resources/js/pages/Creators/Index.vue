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
</script>

<template>
    <WebLayout>
        <Head title="创作者" />

        <div class="min-h-screen py-8">
            <div class="max-w-6xl mx-auto px-4">
                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2 flex items-center gap-3">
                            <ChefHat class="h-8 w-8 text-[#ff6e02]" />
                            创作者
                        </h1>
                        <p class="text-[#999999]">发现并关注优秀的内容创作者</p>
                    </div>
                </div>

                <!-- Results Count -->
                <div v-if="creators" class="flex items-center justify-between mb-6 text-sm text-[#999999]">
                    <p>
                        共 {{ creators.total }} 位创作者
                    </p>
                    <p v-if="creators.last_page > 1">
                        第 {{ creators.current_page }} / {{ creators.last_page }} 页
                    </p>
                </div>

                <!-- Creators Grid -->
                <div v-if="creators?.data" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Card v-for="creator in creators.data" :key="creator.id" class="bg-[#374151] border-[#4B5563] hover:border-[#6B7280] transition-all">
                        <CardHeader class="text-center pb-3">
                            <!-- Avatar -->
                            <div class="flex justify-center mb-4">
                                <Link :href="`/creators/${creator.creator_profile?.id || creator.id}`">
                                    <img
                                        :src="creator.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(creator.display_name)}&size=80&background=ff6e02&color=fff`"
                                        class="w-20 h-20 rounded-full object-cover border-2 border-[#ff6e02]"
                                        :alt="creator.display_name"
                                        @error="(e) => e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(creator.display_name)}&size=80&background=ff6e02&color=fff`"
                                    />
                                </Link>
                            </div>

                            <!-- Verification Badge -->
                            <div class="flex items-center justify-center gap-2 mb-2">
                                <Badge
                                    v-if="creator.verification_status === 'verified'"
                                    class="bg-green-500/20 text-green-500 border-green-500/30 text-xs"
                                >
                                    ✓ 已认证
                                </Badge>
                                <Badge
                                    v-else-if="creator.verification_status === 'pending'"
                                    class="bg-yellow-500/20 text-yellow-500 border-yellow-500/30 text-xs"
                                >
                                    审核中
                                </Badge>
                            </div>

                            <!-- Name -->
                            <CardTitle class="text-xl text-white">
                                <Link :href="`/creators/${creator.creator_profile?.id || creator.id}`" class="hover:text-[#ff6e02]">
                                    {{ creator.display_name }}
                                </Link>
                            </CardTitle>

                            <!-- Specialty -->
                            <CardDescription v-if="creator.specialty" class="text-[#999999]">
                                {{ creator.specialty }}
                            </CardDescription>

                            <!-- Stats -->
                            <div class="flex items-center justify-center gap-4 mt-3 text-sm text-[#999999]">
                                <div class="flex items-center gap-1">
                                    <Users class="w-4 h-4" />
                                    {{ creator.follower_count }}
                                </div>
                                <div class="flex items-center gap-1">
                                    <TrendingUp class="w-4 h-4" />
                                    {{ creator.posts_count }}
                                </div>
                                <div class="flex items-center gap-1">
                                    <Star class="w-4 h-4 fill-current text-[#ff6e02]" />
                                    {{ creator.likes_received }}
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="space-y-4">
                            <!-- Bio -->
                            <p v-if="creator.bio" class="text-sm text-[#999999] line-clamp-3">
                                {{ creator.bio }}
                            </p>
                            <p v-else class="text-sm text-[#666666] italic">
                                暂无简介
                            </p>

                            <!-- Joined Date -->
                            <div class="flex items-center gap-2 text-xs text-[#999999]">
                                <Clock class="w-3 h-3" />
                                加入于 {{ formatDate(creator.created_at) }}
                            </div>

                            <!-- View Profile Button -->
                            <Button class="w-full bg-[#ff6e02] hover:bg-[#e55a00] text-white" as-child>
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
                <div v-if="creators && creators.last_page > 1" class="flex items-center justify-center gap-2 mt-8">
                    <Button
                        v-for="link in creators?.links || []"
                        :key="link.label"
                        :variant="link.active ? 'default' : 'outline'"
                        :disabled="!link.url"
                        size="sm"
                        @click="link.url && router.visit(link.url, { preserveState: true })"
                        v-html="link.label"
                        :class="{
                            'bg-[#ff6e02] hover:bg-[#e55a00]': link.active,
                            'border-[#4B5563] text-white hover:bg-[#4B5563]': !link.active
                        }"
                    />
                </div>
            </div>
        </div>
    </WebLayout>
</template>
