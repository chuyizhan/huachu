<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Link } from '@inertiajs/vue3';
import { Users, MessageSquare, Eye, Heart, Trophy, Star, ChefHat, Crown } from 'lucide-vue-next';

interface Post {
    id: number;
    title: string;
    excerpt: string;
    slug: string;
    user: {
        id: number;
        name: string;
        email: string;
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
    first_image?: {
        url: string;
        thumb: string;
    } | null;
}

interface Creator {
    id: number;
    display_name: string;
    bio: string;
    specialty: string;
    follower_count: number;
    rating: number;
    is_featured: boolean;
    user: {
        id: number;
        name: string;
    };
}

interface Category {
    id: number;
    name: string;
    slug: string;
    color: string;
    icon: string;
    posts_count: number;
}

interface UserPoints {
    user: {
        id: number;
        name: string;
        creator_profile?: {
            display_name: string;
        };
    };
    points: number;
    level: number;
}

interface Props {
    featuredPosts: Post[];
    recentPosts: Post[];
    categories: Category[];
    featuredCreators: Creator[];
    topUsers: UserPoints[];
}

const props = defineProps<Props>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
    });
};

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
</script>

<template>
    <AppLayout>
        <div class="space-y-8">
            <!-- Hero Section -->
            <div class="bg-gradient-to-r from-orange-500 via-red-500 to-pink-500 text-white rounded-xl p-8 relative overflow-hidden">
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-4 right-4 text-6xl">🍳</div>
                    <div class="absolute top-16 right-20 text-4xl">🥘</div>
                    <div class="absolute bottom-4 left-4 text-5xl">👨‍🍳</div>
                    <div class="absolute bottom-16 left-16 text-3xl">🍽️</div>
                </div>
                <div class="max-w-4xl relative z-10">
                    <h1 class="text-4xl font-bold mb-4">Welcome to the Culinary Creator Community</h1>
                    <p class="text-xl mb-6 text-orange-100">
                        Connect with talented culinary artists, share your recipes and techniques, and discover amazing content from our community of passionate creators.
                    </p>
                    <div class="flex gap-4">
                        <Button size="lg" variant="secondary" class="bg-white text-orange-600 hover:bg-orange-50" as-child>
                            <Link href="/posts/create">
                                <MessageSquare class="w-4 h-4 mr-2" />
                                Share Recipe
                            </Link>
                        </Button>
                        <Button size="lg" variant="outline" class="text-white border-white hover:bg-white hover:text-orange-600" as-child>
                            <Link href="/creators/apply">
                                <ChefHat class="w-4 h-4 mr-2" />
                                Become a Chef
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <Card class="bg-gradient-to-br from-orange-50 to-red-50 border-orange-200">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-orange-800">Recipes Shared</CardTitle>
                        <div class="text-2xl">📝</div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-orange-700">{{ recentPosts.length }}+</div>
                    </CardContent>
                </Card>
                <Card class="bg-gradient-to-br from-green-50 to-emerald-50 border-green-200">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-green-800">Master Chefs</CardTitle>
                        <div class="text-2xl">👨‍🍳</div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-700">{{ featuredCreators.length }}+</div>
                    </CardContent>
                </Card>
                <Card class="bg-gradient-to-br from-purple-50 to-pink-50 border-purple-200">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-purple-800">Cuisines</CardTitle>
                        <div class="text-2xl">🍽️</div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-purple-700">{{ categories.length }}</div>
                    </CardContent>
                </Card>
                <Card class="bg-gradient-to-br from-yellow-50 to-orange-50 border-yellow-200">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-yellow-800">Community Stars</CardTitle>
                        <div class="text-2xl">⭐</div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-yellow-700">{{ topUsers.reduce((sum, user) => sum + user.points, 0).toLocaleString() }}</div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Featured Posts -->
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-2">
                                <div class="text-2xl">🔥</div>
                                <h2 class="text-2xl font-bold">Featured Recipes</h2>
                            </div>
                            <Button variant="outline" as-child>
                                <Link href="/community/posts">View All</Link>
                            </Button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <Card v-for="post in featuredPosts.slice(0, 4)" :key="post.id" class="hover:shadow-lg transition-shadow">
                                <CardHeader class="pb-3">
                                    <div class="flex items-center gap-2 mb-2">
                                        <Badge :class="getTypeColor(post.type)" variant="secondary">
                                            {{ post.type }}
                                        </Badge>
                                        <Badge v-if="post.is_premium" variant="default" class="bg-amber-500">
                                            <Crown class="w-3 h-3 mr-1" />
                                            Premium
                                        </Badge>
                                    </div>
                                    <CardTitle class="line-clamp-2">
                                        <Link :href="`/posts/${post.slug}`" class="hover:text-primary">
                                            {{ post.title }}
                                        </Link>
                                    </CardTitle>
                                    <CardDescription class="line-clamp-2">
                                        {{ post.excerpt }}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
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
                                        <div class="flex items-center gap-3 text-sm text-muted-foreground">
                                            <div class="flex items-center gap-1">
                                                <Eye class="w-4 h-4" />
                                                {{ post.view_count }}
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <Heart class="w-4 h-4" />
                                                {{ post.like_count }}
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    <!-- Recent Posts -->
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-2">
                                <div class="text-2xl">🆕</div>
                                <h2 class="text-2xl font-bold">Fresh Recipes</h2>
                            </div>
                            <Button variant="outline" as-child>
                                <Link href="/community/posts">View All</Link>
                            </Button>
                        </div>
                        <div class="space-y-4">
                            <Card v-for="post in recentPosts.slice(0, 6)" :key="post.id">
                                <CardContent class="p-4">
                                    <div class="flex items-start gap-4">
                                        <Avatar>
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
                                                <span class="text-sm text-muted-foreground">
                                                    by {{ post.user.creator_profile?.display_name || post.user.name }}
                                                </span>
                                                <div class="flex items-center gap-3 text-xs text-muted-foreground">
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
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Categories -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <div class="text-xl">🍽️</div>
                                Browse Cuisines
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div v-for="category in categories" :key="category.id">
                                <Link
                                    :href="`/community/posts?category=${category.slug}`"
                                    class="flex items-center justify-between p-3 rounded-lg hover:bg-muted transition-colors"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-3 h-3 rounded-full"
                                            :style="{ backgroundColor: category.color }"
                                        ></div>
                                        <span class="font-medium">{{ category.name }}</span>
                                    </div>
                                    <span class="text-sm text-muted-foreground">
                                        {{ category.posts_count }}
                                    </span>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Featured Creators -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <div class="text-xl">👨‍🍳</div>
                                Master Chefs
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-for="creator in featuredCreators.slice(0, 5)" :key="creator.id">
                                <Link
                                    :href="`/creators/${creator.id}`"
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-muted transition-colors"
                                >
                                    <Avatar>
                                        <AvatarFallback>
                                            {{ getInitials(creator.display_name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-sm">{{ creator.display_name }}</span>
                                            <Badge v-if="creator.is_featured" variant="secondary" class="text-xs">
                                                ⭐ Featured
                                            </Badge>
                                        </div>
                                        <p class="text-xs text-muted-foreground line-clamp-1">
                                            {{ creator.specialty }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-muted-foreground">
                                                {{ creator.follower_count }} followers
                                            </span>
                                            <span class="text-xs">⭐ {{ creator.rating }}/5</span>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Top Contributors -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <div class="text-xl">🏆</div>
                                Top Contributors
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div v-for="(user, index) in topUsers" :key="user.user.id" class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-6 h-6 rounded-full bg-muted text-xs font-bold">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="font-semibold text-sm">
                                        {{ user.user.creator_profile?.display_name || user.user.name }}
                                    </span>
                                    <div class="text-xs text-muted-foreground">
                                        Level {{ user.level }} • {{ user.points.toLocaleString() }} points
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Button class="w-full bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600" as-child>
                                <Link href="/posts/create">Share Your Recipe</Link>
                            </Button>
                            <Button variant="outline" class="w-full border-orange-300 text-orange-600 hover:bg-orange-50" as-child>
                                <Link href="/creators/apply">Join as Chef</Link>
                            </Button>
                            <Button variant="outline" class="w-full border-purple-300 text-purple-600 hover:bg-purple-50" as-child>
                                <Link href="/vip">Go Premium</Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>