<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Search, Eye, Heart, MessageSquare, Crown, Filter } from 'lucide-vue-next';
import { ref, watch } from 'vue';

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

interface Category {
    id: number;
    name: string;
    slug: string;
    color: string;
}

interface PaginatedPosts {
    data: Post[];
    links: any[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

interface Props {
    posts: PaginatedPosts;
    categories: Category[];
    filters: {
        category?: string;
        type?: string;
        search?: string;
    };
}

const props = defineProps<Props>();
const page = usePage();

const searchQuery = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');
const selectedType = ref(props.filters.type || '');

const typeOptions = [
    { value: '', label: 'All Types' },
    { value: 'discussion', label: 'Discussions' },
    { value: 'tutorial', label: 'Tutorials' },
    { value: 'showcase', label: 'Showcases' },
    { value: 'question', label: 'Questions' },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
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

const getTypeIcon = (type: string) => {
    const icons = {
        discussion: MessageSquare,
        tutorial: '📚',
        showcase: '🎨',
        question: '❓',
    };
    return icons[type as keyof typeof icons] || MessageSquare;
};

const applyFilters = () => {
    const params = new URLSearchParams();

    if (searchQuery.value) params.set('search', searchQuery.value);
    if (selectedCategory.value) params.set('category', selectedCategory.value);
    if (selectedType.value) params.set('type', selectedType.value);

    const queryString = params.toString();
    const url = queryString ? `/community/posts?${queryString}` : '/community/posts';

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = '';
    selectedType.value = '';
    router.visit('/community/posts', {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

watch([searchQuery, selectedCategory, selectedType], () => {
    applyFilters();
}, { debounce: 300 });
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="text-3xl">🍳</div>
                        <h1 class="text-3xl font-bold">Recipe Collection</h1>
                    </div>
                    <p class="text-muted-foreground">
                        Discover and share amazing recipes with our culinary community
                    </p>
                </div>
                <Button class="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600" as-child>
                    <Link href="/posts/create">
                        <MessageSquare class="w-4 h-4 mr-2" />
                        Share Recipe
                    </Link>
                </Button>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="p-6">
                    <div class="flex flex-col lg:flex-row gap-4">
                        <!-- Search -->
                        <div class="flex-1">
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                                <Input
                                    v-model="searchQuery"
                                    placeholder="Search recipes..."
                                    class="pl-10"
                                />
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <Select v-model="selectedCategory">
                            <SelectTrigger class="w-full lg:w-[200px]">
                                <SelectValue placeholder="All Categories" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Categories</SelectItem>
                                <SelectItem v-for="category in categories" :key="category.id" :value="category.slug">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-3 h-3 rounded-full"
                                            :style="{ backgroundColor: category.color }"
                                        ></div>
                                        {{ category.name }}
                                    </div>
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <!-- Type Filter -->
                        <Select v-model="selectedType">
                            <SelectTrigger class="w-full lg:w-[200px]">
                                <SelectValue placeholder="All Types" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="option in typeOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <!-- Clear Filters -->
                        <Button
                            variant="outline"
                            @click="clearFilters"
                            :disabled="!searchQuery && !selectedCategory && !selectedType"
                        >
                            <Filter class="w-4 h-4 mr-2" />
                            Clear
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Results Count -->
            <div class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    Showing {{ posts.meta.total }} {{ posts.meta.total === 1 ? 'recipe' : 'recipes' }}
                </p>
                <div class="text-sm text-muted-foreground">
                    Page {{ posts.meta.current_page }} of {{ posts.meta.last_page }}
                </div>
            </div>

            <!-- Posts Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Card v-for="post in posts.data" :key="post.id" class="hover:shadow-lg transition-all duration-200">
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
                            <div class="flex items-center gap-2">
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
                        <div class="flex items-center justify-between mt-4 pt-3 border-t">
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

                            <Button size="sm" variant="ghost" as-child>
                                <Link :href="`/posts/${post.slug}`">
                                    Read More
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-if="posts.data.length === 0" class="text-center py-12">
                <div class="text-6xl mb-4">🍽️</div>
                <h3 class="text-lg font-semibold mb-2">No recipes found</h3>
                <p class="text-muted-foreground mb-4">
                    Try adjusting your filters or share the first recipe!
                </p>
                <Button class="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600" as-child>
                    <Link href="/posts/create">Share First Recipe</Link>
                </Button>
            </div>

            <!-- Pagination -->
            <div v-if="posts.meta.last_page > 1" class="flex items-center justify-center gap-2">
                <Button
                    v-for="link in posts.links"
                    :key="link.label"
                    :variant="link.active ? 'default' : 'outline'"
                    :disabled="!link.url"
                    size="sm"
                    @click="link.url && router.visit(link.url, { preserveState: true })"
                    v-html="link.label"
                />
            </div>
        </div>
    </AppLayout>
</template>