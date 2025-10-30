<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { Eye, Heart, MessageSquare, Crown, Calendar, ChefHat } from 'lucide-vue-next';

interface Post {
    id: number;
    title: string;
    excerpt: string;
    slug: string;
    user: {
        id: number;
        name: string;
        avatar?: string;
        creator_profile?: {
            id: number;
            display_name: string;
            specialty: string;
            verification_status?: string;
        };
    };
    category: {
        id: number;
        name: string;
        color: string;
        icon?: string;
        icon_image?: string | null;
    };
    type?: string;
    view_count: number;
    like_count: number;
    comment_count?: number;
    is_premium?: boolean;
    is_featured?: boolean;
    has_video?: boolean;
    published_at: string;
    first_image?: {
        url: string;
        thumb: string;
    } | null;
    post_images?: Array<{
        url: string;
        thumb: string;
    }>;
}

interface Props {
    post: Post;
    variant?: 'default' | 'compact' | 'featured' | 'home' | 'list';
    showAuthor?: boolean;
    showStats?: boolean;
    showReadMore?: boolean;
    showImages?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'default',
    showAuthor: true,
    showStats: true,
    showReadMore: true,
    showImages: true,
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

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = Math.floor((now.getTime() - date.getTime()) / 1000);

    if (diff < 60) return '刚刚';
    if (diff < 3600) return `${Math.floor(diff / 60)}分钟前`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}小时前`;
    if (diff < 604800) return `${Math.floor(diff / 86400)}天前`;

    return date.toLocaleDateString('zh-CN');
};

const getPostTypeText = (type: string) => {
    const typeMap = {
        'tutorial': '教程',
        'discussion': '讨论',
        'showcase': '展示',
        'question': '问答'
    };
    return typeMap[type as keyof typeof typeMap] || type;
};
</script>

<template>
    <!-- Home Variant - X-like Layout -->
    <Link
        v-if="variant === 'home'"
        :href="`/posts/${post.slug}`"
        class="block hover:bg-accent/50 transition-colors px-4 py-3 border-b border-border"
    >
        <div class="flex gap-3">
            <!-- Author Avatar (Left) -->
            <div class="flex-shrink-0">
                <img
                    :src="post.user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(post.user.creator_profile?.display_name || post.user.name)}&size=48&background=ff6e02&color=fff`"
                    class="w-10 h-10 rounded-full object-cover"
                    :alt="post.user.name"
                    @error="(e) => e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(post.user.name)}&size=48&background=ff6e02&color=fff`"
                />
            </div>

            <!-- Post Content (Right) -->
            <div class="flex-1 min-w-0">
                <!-- Author Info & Time -->
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-semibold text-foreground text-sm">
                        {{ post.user.creator_profile?.display_name || post.user.name }}
                    </span>
                    <span class="text-muted-foreground text-xs">·</span>
                    <span class="text-muted-foreground text-xs">
                        {{ formatTime(post.published_at) }}
                    </span>
                    <span v-if="post.has_video" class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium bg-red-500/20 text-red-400 rounded">
                        视频
                    </span>
                </div>

                <!-- Post Title -->
                <h3 class="text-base font-semibold text-foreground mb-2 hover:text-[#ff6e02] transition-colors line-clamp-2">
                    {{ post.title }}
                </h3>

                <!-- Post Excerpt -->
                <p class="text-sm text-muted-foreground line-clamp-2 mb-3">
                    {{ post.excerpt }}
                </p>

                <!-- Post Images (Max 3 in one row) -->
                <div v-if="showImages && post.post_images && post.post_images.length > 0" class="grid gap-1 mb-3 rounded-sm overflow-hidden "
                     :class="{
                         'grid-cols-1': post.post_images.length === 1,
                         'grid-cols-2': post.post_images.length === 2,
                         'grid-cols-3': post.post_images.length >= 3
                     }">
                    <div
                        v-for="(image, index) in post.post_images.slice(0, 3)"
                        :key="index"
                        class="relative overflow-hidden"
                        :class="{
                            'aspect-video': post.post_images.length === 1,
                            'aspect-square': post.post_images.length >= 2
                        }"
                    >
                        <img
                            :src="image.thumb || image.url"
                            class="w-full h-full object-cover"
                            :alt="`${post.title} - Image ${index + 1}`"
                        />
                    </div>
                </div>

                <!-- Stats -->
                <div class="flex items-center gap-6 text-sm text-muted-foreground">
                    <span class="flex items-center gap-1.5 hover:text-[#ff6e02] transition-colors cursor-pointer">
                        <MessageSquare class="h-4 w-4" />
                        <span>{{ post.comment_count || 0 }}</span>
                    </span>
                    <span class="flex items-center gap-1.5 hover:text-[#ff6e02] transition-colors cursor-pointer">
                        <Heart class="h-4 w-4" />
                        <span>{{ post.like_count }}</span>
                    </span>
                    <span class="flex items-center gap-1.5">
                        <Eye class="h-4 w-4" />
                        <span>{{ post.view_count }}</span>
                    </span>
                </div>
            </div>
        </div>
    </Link>

    <!-- List Variant - Twitter-like Layout for Posts Page -->
    <Link
        v-else-if="variant === 'list'"
        :href="`/posts/${post.slug}`"
        class="block group"
    >
        <Card class="bg-transparent border-0 shadow-none">
            <CardContent class="p-2 px-2">
                <div class="flex gap-3">
                    <!-- Author Avatar (Left) -->
                    <div v-if="showAuthor && post.user" class="flex-shrink-0">
                        <img
                            :src="post.user.avatar ? `/storage/${post.user.avatar}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(post.user.creator_profile?.display_name || post.user.name)}&size=48&background=ff6e02&color=fff`"
                            class="w-12 h-12 rounded-full object-cover"
                            :alt="post.user.name"
                            @error="(e) => e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(post.user.name)}&size=48&background=ff6e02&color=fff`"
                        />
                    </div>

                    <!-- Post Content (Right) -->
                    <div class="flex-1 min-w-0">
                        <!-- Author Info & Meta -->
                        <div v-if="showAuthor && post.user" class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                <div class="flex items-center gap-1">
                                    <span class="text-white font-medium text-sm">
                                        {{ post.user.creator_profile?.display_name || post.user.name }}
                                    </span>
                                    <ChefHat v-if="post.user.creator_profile?.verification_status === 'verified'"
                                            class="w-3 h-3 text-[#ff6e02]" />
                                </div>
                                <span class="text-[#999999] text-xs">·</span>
                                <span class="text-[#999999] text-xs">{{ formatTime(post.published_at) }}</span>
                            </div>
                        </div>

                        <!-- Badges -->
                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                            <Badge
                                class="text-white text-xs px-2 py-0.5"
                                :style="{ backgroundColor: post.category.color }"
                            >
                                {{ post.category.name }}
                            </Badge>
                            <Badge v-if="post.type" variant="outline" class="text-[#999999] border-[#999999] text-xs px-2 py-0.5">
                                {{ getPostTypeText(post.type) }}
                            </Badge>
                            <Badge v-if="post.is_featured" class="bg-[#ff6e02] text-white text-xs px-2 py-0.5">
                                精选
                            </Badge>
                            <Badge v-if="post.is_premium" class="bg-yellow-500 text-white text-xs px-2 py-0.5">
                                ⭐ VIP
                            </Badge>
                            <span v-if="post.has_video" class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-red-500/20 text-red-400 rounded">
                                视频
                            </span>
                        </div>

                        <!-- Title -->
                        <h3 class="text-white font-medium text-base group-hover:text-[#ff6e02] transition-colors line-clamp-2 mb-2">
                            {{ post.title }}
                        </h3>

                        <!-- Excerpt -->
                        <p class="text-[#999999] text-sm line-clamp-2 mb-3">
                            {{ post.excerpt }}
                        </p>

                        <!-- Post Images (First 3) -->
                        <div v-if="showImages && post.post_images && post.post_images.length > 0" class="grid grid-cols-3 gap-2 mb-3 max-w-md">
                            <div
                                v-for="(image, index) in post.post_images.slice(0, 3)"
                                :key="index"
                                class="relative overflow-hidden rounded-lg aspect-square"
                            >
                                <img
                                    :src="image.thumb || image.url"
                                    class="w-full h-full object-cover"
                                    :alt="`${post.title} - Image ${index + 1}`"
                                />
                            </div>
                        </div>

                        <!-- Stats -->
                        <div v-if="showStats" class="flex items-center gap-6 text-xs text-[#999999]">
                            <span class="flex items-center gap-1 hover:text-[#ff6e02] transition-colors cursor-pointer">
                                <MessageSquare class="w-4 h-4" />
                                {{ post.comment_count || 0 }}
                            </span>
                            <span class="flex items-center gap-1 hover:text-[#ff6e02] transition-colors cursor-pointer">
                                <Heart class="w-4 h-4" />
                                {{ post.like_count }}
                            </span>
                            <span class="flex items-center gap-1">
                                <Eye class="w-4 h-4" />
                                {{ post.view_count }}
                            </span>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </Link>

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