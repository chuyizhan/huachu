<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Link, useForm } from '@inertiajs/vue3';
import {
    Star,
    MapPin,
    Calendar,
    Users,
    MessageSquare,
    Heart,
    Eye,
    Instagram,
    Twitter,
    Youtube,
    ExternalLink,
    Trophy,
    Crown,
    UserPlus,
    UserMinus
} from 'lucide-vue-next';
import { computed } from 'vue';

interface Post {
    id: number;
    title: string;
    excerpt: string;
    slug: string;
    type: string;
    view_count: number;
    like_count: number;
    is_premium: boolean;
    published_at: string;
    category: {
        id: number;
        name: string;
        color: string;
    };
}

interface Achievement {
    id: number;
    name: string;
    description: string;
    icon: string;
    earned_at: string;
}

interface Creator {
    id: number;
    display_name: string;
    bio: string;
    specialty: string;
    location: string;
    experience_years: number;
    rating: number;
    follower_count: number;
    is_featured: boolean;
    verification_status: string;
    social_links: {
        instagram?: string;
        twitter?: string;
        youtube?: string;
        website?: string;
    };
    user: {
        id: number;
        name: string;
        created_at: string;
    };
    posts_count: number;
    avg_post_rating: number;
    total_views: number;
    total_likes: number;
    is_following?: boolean;
}

interface Props {
    creator: Creator;
    recentPosts: Post[];
    achievements: Achievement[];
    canFollow?: boolean;
}

const props = defineProps<Props>();

const followForm = useForm({});

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

const getSocialIcon = (platform: string) => {
    const icons = {
        instagram: Instagram,
        twitter: Twitter,
        youtube: Youtube,
        website: ExternalLink,
    };
    return icons[platform as keyof typeof icons] || ExternalLink;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatJoinDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'long',
        year: 'numeric',
    });
};

const engagementRate = computed(() => {
    if (props.creator.total_views === 0) return 0;
    return ((props.creator.total_likes / props.creator.total_views) * 100).toFixed(1);
});

const toggleFollow = () => {
    const url = props.creator.is_following
        ? `/creators/${props.creator.id}/unfollow`
        : `/creators/${props.creator.id}/follow`;

    followForm.post(url, {
        preserveState: true,
        onSuccess: () => {
            // Update the follow status
        },
    });
};
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <!-- Creator Header -->
            <Card>
                <CardContent class="p-8">
                    <div class="flex flex-col lg:flex-row gap-8">
                        <!-- Avatar and Basic Info -->
                        <div class="flex flex-col items-center lg:items-start text-center lg:text-left">
                            <Avatar class="h-32 w-32 mb-4">
                                <AvatarFallback class="text-2xl">
                                    {{ getInitials(creator.display_name) }}
                                </AvatarFallback>
                            </Avatar>

                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <Badge v-if="creator.is_featured" variant="default" class="bg-amber-500">
                                        <Star class="w-3 h-3 mr-1" />
                                        Featured
                                    </Badge>
                                    <Badge v-if="creator.verification_status === 'verified'" variant="default" class="bg-green-500">
                                        ✓ Verified
                                    </Badge>
                                </div>

                                <!-- Social Links -->
                                <div class="flex items-center gap-2">
                                    <template v-for="(url, platform) in creator.social_links" :key="platform">
                                        <a
                                            v-if="url"
                                            :href="url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="p-2 rounded-lg hover:bg-muted transition-colors"
                                        >
                                            <component :is="getSocialIcon(platform)" class="w-4 h-4" />
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Creator Details -->
                        <div class="flex-1 space-y-4">
                            <div>
                                <h1 class="text-3xl font-bold mb-2">{{ creator.display_name }}</h1>
                                <p class="text-xl text-muted-foreground mb-4">{{ creator.specialty }}</p>
                                <p class="text-muted-foreground leading-relaxed">{{ creator.bio }}</p>
                            </div>

                            <!-- Quick Stats -->
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                                <div class="text-center lg:text-left">
                                    <div class="text-2xl font-bold">{{ creator.rating }}/5</div>
                                    <div class="text-sm text-muted-foreground flex items-center gap-1">
                                        <Star class="w-4 h-4 fill-current text-yellow-500" />
                                        Rating
                                    </div>
                                </div>
                                <div class="text-center lg:text-left">
                                    <div class="text-2xl font-bold">{{ creator.follower_count }}</div>
                                    <div class="text-sm text-muted-foreground flex items-center gap-1">
                                        <Users class="w-4 h-4" />
                                        Followers
                                    </div>
                                </div>
                                <div class="text-center lg:text-left">
                                    <div class="text-2xl font-bold">{{ creator.posts_count }}</div>
                                    <div class="text-sm text-muted-foreground flex items-center gap-1">
                                        <MessageSquare class="w-4 h-4" />
                                        Posts
                                    </div>
                                </div>
                                <div class="text-center lg:text-left">
                                    <div class="text-2xl font-bold">{{ engagementRate }}%</div>
                                    <div class="text-sm text-muted-foreground flex items-center gap-1">
                                        <Heart class="w-4 h-4" />
                                        Engagement
                                    </div>
                                </div>
                            </div>

                            <!-- Location and Experience -->
                            <div class="flex flex-wrap gap-4 text-sm text-muted-foreground">
                                <div v-if="creator.location" class="flex items-center gap-1">
                                    <MapPin class="w-4 h-4" />
                                    {{ creator.location }}
                                </div>
                                <div class="flex items-center gap-1">
                                    <Trophy class="w-4 h-4" />
                                    {{ creator.experience_years }} years experience
                                </div>
                                <div class="flex items-center gap-1">
                                    <Calendar class="w-4 h-4" />
                                    Joined {{ formatJoinDate(creator.user.created_at) }}
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-3 pt-4">
                                <Button v-if="canFollow" @click="toggleFollow" :disabled="followForm.processing">
                                    <component :is="creator.is_following ? UserMinus : UserPlus" class="w-4 h-4 mr-2" />
                                    {{ creator.is_following ? 'Unfollow' : 'Follow' }}
                                </Button>
                                <Button variant="outline" as-child>
                                    <Link :href="`/community/posts?creator=${creator.id}`">
                                        View All Posts
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Tabs Content -->
            <Tabs default-value="posts" class="space-y-6">
                <TabsList class="grid w-full grid-cols-3">
                    <TabsTrigger value="posts">Recent Posts</TabsTrigger>
                    <TabsTrigger value="achievements">Achievements</TabsTrigger>
                    <TabsTrigger value="stats">Statistics</TabsTrigger>
                </TabsList>

                <!-- Recent Posts Tab -->
                <TabsContent value="posts" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Card v-for="post in recentPosts" :key="post.id" class="hover:shadow-lg transition-shadow">
                            <CardHeader class="pb-3">
                                <div class="flex items-center gap-2 mb-2">
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
                                <CardDescription class="line-clamp-2">
                                    {{ post.excerpt }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="flex items-center justify-between">
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
                                    <span class="text-xs text-muted-foreground">
                                        {{ formatDate(post.published_at) }}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div v-if="recentPosts.length === 0" class="text-center py-8">
                        <MessageSquare class="w-12 h-12 mx-auto text-muted-foreground mb-3" />
                        <p class="text-muted-foreground">No posts yet</p>
                    </div>
                </TabsContent>

                <!-- Achievements Tab -->
                <TabsContent value="achievements" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <Card v-for="achievement in achievements" :key="achievement.id">
                            <CardContent class="p-6 text-center">
                                <div class="text-4xl mb-3">{{ achievement.icon }}</div>
                                <h3 class="font-semibold mb-2">{{ achievement.name }}</h3>
                                <p class="text-sm text-muted-foreground mb-3">{{ achievement.description }}</p>
                                <Badge variant="secondary" class="text-xs">
                                    Earned {{ formatDate(achievement.earned_at) }}
                                </Badge>
                            </CardContent>
                        </Card>
                    </div>

                    <div v-if="achievements.length === 0" class="text-center py-8">
                        <Trophy class="w-12 h-12 mx-auto text-muted-foreground mb-3" />
                        <p class="text-muted-foreground">No achievements yet</p>
                    </div>
                </TabsContent>

                <!-- Statistics Tab -->
                <TabsContent value="stats" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-sm font-medium">Total Views</CardTitle>
                                <Eye class="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold">{{ creator.total_views.toLocaleString() }}</div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-sm font-medium">Total Likes</CardTitle>
                                <Heart class="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold">{{ creator.total_likes.toLocaleString() }}</div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-sm font-medium">Average Rating</CardTitle>
                                <Star class="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold">{{ creator.avg_post_rating }}/5</div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle class="text-sm font-medium">Engagement Rate</CardTitle>
                                <Trophy class="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div class="text-2xl font-bold">{{ engagementRate }}%</div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Performance Chart Placeholder -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Performance Overview</CardTitle>
                            <CardDescription>Content performance over time</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="h-64 flex items-center justify-center bg-muted rounded-lg">
                                <p class="text-muted-foreground">Performance chart will be implemented here</p>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>