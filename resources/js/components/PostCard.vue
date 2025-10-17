<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { Eye, Heart, MessageSquare, Crown } from 'lucide-vue-next';

interface Post {
    id: number;
    title: string;
    excerpt: string;
    slug: string;
    user: {
        id: number;
        name: string;
        creator_profile?: {
            display_name: string;
            specialty: string;
            verification_status: string;
        };
    };
    category: {
        id: number;
        name: string;
        color: string;
    };
    type: string;
    view_count: number;
    like_count: number;
    is_premium: boolean;
    published_at: string;
}

interface Props {
    post: Post;
    variant?: 'default' | 'compact' | 'featured';
    showAuthor?: boolean;
    showStats?: boolean;
    showReadMore?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'default',
    showAuthor: true,
    showStats: true,
    showReadMore: true,
});

const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const getTypeColor = (type: string) => {
    const colors = {
        discussion: 'bg-blue-100 text-blue-800',
        tutorial: 'bg-green-100 text-green-800',
        showcase: 'bg-purple-100 text-purple-800',
        question: 'bg-orange-100 text-orange-800',
    };
    return colors[type as keyof typeof colors] || colors.discussion;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};
</script>

<template>
    <!-- Featured Variant -->
    <div v-if="variant === 'featured'" class="listclass hover:shadow-lg transition-all duration-200">
        <div class="pb-3">
            <div class="flex items-center gap-2 u-m-b-15">
                <Badge :style="{ backgroundColor: post.category.color }" class="text-white text-xs rounded-40">
                    {{ post.category.name }}
                </Badge>
                <Badge v-if="post.is_premium" class="bg-[#ff6e02] text-white text-xs rounded-40">
                    <Crown class="w-3 h-3 mr-1" />
                    VIP
                </Badge>
            </div>

            <h3 class="font32 colorfff line-clamp-2 u-m-b-10">
                <Link :href="`/posts/${post.slug}`" class="hover:text-primary">
                    {{ post.title }}
                </Link>
            </h3>

            <p class="font24 color999 line-clamp-3 u-m-b-15">
                {{ post.excerpt }}
            </p>
        </div>

        <div>
            <!-- Author Info -->
            <div v-if="showAuthor" class="flex-x-center gap-2 u-m-b-15">
                <Avatar class="h-6 w-6">
                    <AvatarFallback class="text-xs bg-[#ff6e02] text-white">
                        {{ getInitials(post.user.creator_profile?.display_name || post.user.name) }}
                    </AvatarFallback>
                </Avatar>
                <span class="font24 color999">
                    {{ post.user.creator_profile?.display_name || post.user.name }}
                </span>
                <Badge v-if="post.user.creator_profile?.verification_status === 'verified'" class="bg-[#ff6e02] text-white text-xs rounded-40">
                    ✓
                </Badge>
                <span class="font24 color666 ml-auto">
                    {{ formatDate(post.published_at) }}
                </span>
            </div>

            <!-- Stats -->
            <div v-if="showStats" class="flex-x-center justify-between u-m-t-15 u-p-t-15 border-t border-[#333333]">
                <div class="flex-x-center gap-4 font24 color999">
                    <div class="flex-x-center gap-1">
                        <Eye class="w-4 h-4" />
                        <span>{{ post.view_count }}</span>
                    </div>
                    <div class="flex-x-center gap-1">
                        <Heart class="w-4 h-4" />
                        <span>{{ post.like_count }}</span>
                    </div>
                </div>

                <Link v-if="showReadMore" :href="`/posts/${post.slug}`" class="font24 colorz hover:text-white transition-colors">
                    查看更多 →
                </Link>
            </div>
        </div>
    </div>

    <!-- Compact Variant -->
    <div v-else-if="variant === 'compact'" class="listclass">
        <div class="flex-x items-start gap-4">
            <Avatar v-if="showAuthor" class="h-10 w-10">
                <AvatarFallback class="bg-[#ff6e02] text-white">
                    {{ getInitials(post.user.creator_profile?.display_name || post.user.name) }}
                </AvatarFallback>
            </Avatar>
            <div class="flex-1 min-w-0">
                <div class="flex-x-center gap-2 u-m-b-10">
                    <Badge :style="{ backgroundColor: post.category.color }" class="text-white text-xs rounded-40">
                        {{ post.category.name }}
                    </Badge>
                    <span class="font24 color666">
                        {{ formatDate(post.published_at) }}
                    </span>
                </div>
                <h3 class="font32 colorfff line-clamp-1 u-m-b-10">
                    <Link :href="`/posts/${post.slug}`" class="hover:text-primary">
                        {{ post.title }}
                    </Link>
                </h3>
                <p class="font24 color999 line-clamp-2 u-m-b-10">
                    {{ post.excerpt }}
                </p>
                <div class="flex-x-center justify-between">
                    <span v-if="showAuthor" class="font24 color999">
                        {{ post.user.creator_profile?.display_name || post.user.name }}
                    </span>
                    <div v-if="showStats" class="flex-x-center gap-3 font24 color999">
                        <span class="flex-x-center gap-1">
                            <Eye class="w-3 h-3" />
                            {{ post.view_count }}
                        </span>
                        <span class="flex-x-center gap-1">
                            <Heart class="w-3 h-3" />
                            {{ post.like_count }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Default Variant - UniApp Style -->
    <div v-else class="listclass hover:shadow-lg transition-all duration-200">
        <div class="flex-y">
            <div class="flex-x items-center gap-2 u-m-b-15">
                <Badge :style="{ backgroundColor: post.category.color }" class="text-white text-xs rounded-40">
                    {{ post.category.name }}
                </Badge>
                <Badge v-if="post.is_premium" variant="default" class="bg-[#ff6e02] text-white text-xs rounded-40">
                    <Crown class="w-3 h-3 mr-1" />
                    VIP
                </Badge>
            </div>

            <h3 class="font32 colorfff line-clamp-2 u-m-b-10">
                <Link :href="`/posts/${post.slug}`" class="hover:text-primary">
                    {{ post.title }}
                </Link>
            </h3>

            <p class="font24 color999 line-clamp-2 u-m-b-15">
                {{ post.excerpt }}
            </p>

            <!-- Author Info -->
            <div v-if="showAuthor" class="flex-x-center gap-2 u-m-b-15">
                <Avatar class="h-6 w-6">
                    <AvatarFallback class="text-xs bg-[#ff6e02] text-white">
                        {{ getInitials(post.user.creator_profile?.display_name || post.user.name) }}
                    </AvatarFallback>
                </Avatar>
                <span class="font24 color999">
                    {{ post.user.creator_profile?.display_name || post.user.name }}
                </span>
                <Badge v-if="post.user.creator_profile?.verification_status === 'verified'" class="bg-[#ff6e02] text-white text-xs rounded-40">
                    ✓
                </Badge>
            </div>

            <!-- Stats -->
            <div class="flex-x-center justify-between">
                <div v-if="showStats" class="flex-x-center gap-4 font24 color999">
                    <div class="flex-x-center gap-1">
                        <Eye class="w-4 h-4" />
                        <span>{{ post.view_count }}</span>
                    </div>
                    <div class="flex-x-center gap-1">
                        <Heart class="w-4 h-4" />
                        <span>{{ post.like_count }}</span>
                    </div>
                </div>
                <span class="font24 color666">
                    {{ formatDate(post.published_at) }}
                </span>
            </div>
        </div>
    </div>
</template>