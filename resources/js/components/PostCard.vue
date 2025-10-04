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
    <Card v-if="variant === 'featured'" class="hover:shadow-lg transition-all duration-200">
        <CardHeader class="pb-3">
            <div class="flex items-center gap-2 mb-3">
                <Badge :style="{ backgroundColor: post.category.color }" class="text-white text-xs">
                    {{ post.category.name }}
                </Badge>
                <Badge :class="getTypeColor(post.type)" variant="secondary" class="text-xs">
                    {{ post.type }}
                </Badge>
                <Badge v-if="post.is_premium" variant="default" class="bg-amber-500 text-xs">
                    <Crown class="w-3 h-3 mr-1" />
                    Premium
                </Badge>
            </div>

            <CardTitle class="line-clamp-2 text-lg">
                <Link :href="`/posts/${post.slug}`" class="hover:text-primary">
                    {{ post.title }}
                </Link>
            </CardTitle>

            <CardDescription class="line-clamp-3">
                {{ post.excerpt }}
            </CardDescription>
        </CardHeader>

        <CardContent>
            <div class="flex items-center justify-between">
                <!-- Author Info -->
                <div v-if="showAuthor" class="flex items-center gap-2">
                    <Avatar class="h-7 w-7">
                        <AvatarFallback class="text-xs">
                            {{ getInitials(post.user.creator_profile?.display_name || post.user.name) }}
                        </AvatarFallback>
                    </Avatar>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1">
                            <span class="text-sm font-medium truncate">
                                {{ post.user.creator_profile?.display_name || post.user.name }}
                            </span>
                            <Badge
                                v-if="post.user.creator_profile?.verification_status === 'verified'"
                                variant="secondary"
                                class="text-xs px-1"
                            >
                                ✓
                            </Badge>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{ formatDate(post.published_at) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div v-if="showStats" class="flex items-center justify-between mt-4 pt-3 border-t">
                <div class="flex items-center gap-4 text-sm text-muted-foreground">
                    <div class="flex items-center gap-1">
                        <Eye class="w-4 h-4" />
                        <span>{{ post.view_count }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <Heart class="w-4 h-4" />
                        <span>{{ post.like_count }}</span>
                    </div>
                </div>

                <Button v-if="showReadMore" size="sm" variant="ghost" as-child>
                    <Link :href="`/posts/${post.slug}`">
                        Read More
                    </Link>
                </Button>
            </div>
        </CardContent>
    </Card>

    <!-- Compact Variant -->
    <Card v-else-if="variant === 'compact'">
        <CardContent class="p-4">
            <div class="flex items-start gap-4">
                <Avatar v-if="showAuthor">
                    <AvatarFallback>
                        {{ getInitials(post.user.creator_profile?.display_name || post.user.name) }}
                    </AvatarFallback>
                </Avatar>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <Badge :style="{ backgroundColor: post.category.color }" class="text-white text-xs">
                            {{ post.category.name }}
                        </Badge>
                        <Badge :class="getTypeColor(post.type)" variant="secondary" class="text-xs">
                            {{ post.type }}
                        </Badge>
                        <span class="text-xs text-muted-foreground">
                            {{ formatDate(post.published_at) }}
                        </span>
                    </div>
                    <h3 class="font-semibold line-clamp-1 mb-1">
                        <Link :href="`/posts/${post.slug}`" class="hover:text-primary">
                            {{ post.title }}
                        </Link>
                    </h3>
                    <p class="text-sm text-muted-foreground line-clamp-2 mb-2">
                        {{ post.excerpt }}
                    </p>
                    <div class="flex items-center justify-between">
                        <span v-if="showAuthor" class="text-sm text-muted-foreground">
                            by {{ post.user.creator_profile?.display_name || post.user.name }}
                        </span>
                        <div v-if="showStats" class="flex items-center gap-3 text-xs text-muted-foreground">
                            <span class="flex items-center gap-1">
                                <Eye class="w-3 h-3" />
                                {{ post.view_count }}
                            </span>
                            <span class="flex items-center gap-1">
                                <Heart class="w-3 h-3" />
                                {{ post.like_count }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>

    <!-- Default Variant -->
    <Card v-else class="hover:shadow-lg transition-all duration-200">
        <CardHeader class="pb-3">
            <div class="flex items-center gap-2 mb-3">
                <Badge :style="{ backgroundColor: post.category.color }" class="text-white text-xs">
                    {{ post.category.name }}
                </Badge>
                <Badge :class="getTypeColor(post.type)" variant="secondary" class="text-xs">
                    {{ post.type }}
                </Badge>
                <Badge v-if="post.is_premium" variant="default" class="bg-amber-500 text-xs">
                    <Crown class="w-3 h-3 mr-1" />
                    Premium
                </Badge>
            </div>

            <CardTitle class="line-clamp-2">
                <Link :href="`/posts/${post.slug}`" class="hover:text-primary">
                    {{ post.title }}
                </Link>
            </CardTitle>

            <CardDescription class="line-clamp-3">
                {{ post.excerpt }}
            </CardDescription>
        </CardHeader>

        <CardContent>
            <!-- Author Info -->
            <div v-if="showAuthor" class="flex items-center gap-2 mb-4">
                <Avatar class="h-6 w-6">
                    <AvatarFallback class="text-xs">
                        {{ getInitials(post.user.creator_profile?.display_name || post.user.name) }}
                    </AvatarFallback>
                </Avatar>
                <span class="text-sm text-muted-foreground">
                    {{ post.user.creator_profile?.display_name || post.user.name }}
                </span>
                <Badge v-if="post.user.creator_profile?.verification_status === 'verified'" variant="secondary" class="text-xs">
                    ✓ Verified
                </Badge>
            </div>

            <!-- Stats and Actions -->
            <div class="flex items-center justify-between">
                <div v-if="showStats" class="flex items-center gap-3 text-sm text-muted-foreground">
                    <div class="flex items-center gap-1">
                        <Eye class="w-4 h-4" />
                        {{ post.view_count }}
                    </div>
                    <div class="flex items-center gap-1">
                        <Heart class="w-4 h-4" />
                        {{ post.like_count }}
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-muted-foreground">
                        {{ formatDate(post.published_at) }}
                    </span>
                    <Button v-if="showReadMore" size="sm" variant="ghost" as-child>
                        <Link :href="`/posts/${post.slug}`">
                            Read More
                        </Link>
                    </Button>
                </div>
            </div>
        </CardContent>
    </Card>
</template>