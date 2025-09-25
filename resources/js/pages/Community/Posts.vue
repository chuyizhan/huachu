<script setup lang="ts">
import ChineseLayout from '@/layouts/ChineseLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Input } from '@/components/ui/input';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    Search,
    Filter,
    Eye,
    Heart,
    MessageSquare,
    Calendar,
    ChefHat,
    Tag,
    Grid,
    List,
    TrendingUp,
    Clock,
    Star,
    Users
} from 'lucide-vue-next';

interface Creator {
    id: number;
    display_name: string;
    specialty: string;
    verification_status: string;
    rating?: number;
    follower_count?: number;
}

interface User {
    id: number;
    name: string;
    creator_profile?: Creator;
}

interface Category {
    id: number;
    name: string;
    slug: string;
    color: string;
    icon?: string;
    is_nav_item?: boolean;
    nav_route?: string;
    posts_count?: number;
}

interface Post {
    id: number;
    title: string;
    content: string;
    excerpt: string;
    slug: string;
    type: string;
    status: string;
    is_featured: boolean;
    is_premium: boolean;
    view_count: number;
    like_count: number;
    comment_count: number;
    share_count: number;
    tags: string[];
    published_at: string;
    created_at: string;
    user: User;
    category: Category;
}

interface PaginatedPosts {
    data: Post[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

interface Props {
    posts: PaginatedPosts;
    categories: Category[];
    selectedCategory?: Category | null;
    filters: {
        category?: string;
        type?: string;
        search?: string;
    };
}

const props = defineProps<Props>();

const searchTerm = ref(props.filters.search || '');
const selectedType = ref(props.filters.type || '');

const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('zh-CN', {
        month: 'short',
        day: 'numeric'
    });
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

const handleSearch = () => {
    const params: any = {};

    if (searchTerm.value) params.search = searchTerm.value;
    if (selectedType.value) params.type = selectedType.value;
    if (props.filters.category) params.category = props.filters.category;

    router.get('/community/posts', params, {
        preserveState: true,
        replace: true
    });
};

const handleCategoryFilter = (categorySlug: string | null) => {
    const params: any = {};

    if (categorySlug) params.category = categorySlug;
    if (searchTerm.value) params.search = searchTerm.value;
    if (selectedType.value) params.type = selectedType.value;

    router.get('/community/posts', params, {
        preserveState: true,
        replace: true
    });
};

const handleTypeFilter = (type: string | null) => {
    selectedType.value = type || '';
    const params: any = {};

    if (type) params.type = type;
    if (props.filters.category) params.category = props.filters.category;
    if (searchTerm.value) params.search = searchTerm.value;

    router.get('/community/posts', params, {
        preserveState: true,
        replace: true
    });
};

const postTypes = [
    { value: 'tutorial', label: '教程', icon: '📚' },
    { value: 'discussion', label: '讨论', icon: '💬' },
    { value: 'showcase', label: '展示', icon: '🎨' },
    { value: 'question', label: '问答', icon: '❓' }
];
</script>

<template>
    <ChineseLayout>
        <div class="max-w-[1000px] mx-auto px-4 py-6">
            <!-- Header -->
            <div class="mb-8">
                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 mb-4 text-sm text-[#999999]">
                    <Link href="/" class="hover:text-[#ff6e02]">首页</Link>
                    <span>/</span>
                    <span v-if="selectedCategory" class="text-white">{{ selectedCategory.name }}</span>
                    <span v-else class="text-white">社区帖子</span>
                </div>

                <!-- Title -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">
                            <span v-if="selectedCategory" class="flex items-center gap-2">
                                <span class="text-2xl">{{ selectedCategory.icon || '📝' }}</span>
                                {{ selectedCategory.name }}
                            </span>
                            <span v-else>社区帖子</span>
                        </h1>
                        <p class="text-[#999999]">
                            <span v-if="selectedCategory">浏览 {{ selectedCategory.name }} 分类下的所有内容</span>
                            <span v-else>探索社区中的精彩内容和讨论</span>
                        </p>
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="bg-[#374151] rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-[#999999] w-4 h-4" />
                                <Input
                                    v-model="searchTerm"
                                    placeholder="搜索帖子..."
                                    class="pl-10 bg-[#1f2937] border-[#1f2937] text-white placeholder:text-[#999999]"
                                    @keyup.enter="handleSearch"
                                />
                            </div>
                        </div>

                        <!-- Type Filter -->
                        <div>
                            <select
                                v-model="selectedType"
                                @change="handleTypeFilter(selectedType)"
                                class="w-full px-3 py-2 bg-[#1f2937] border border-[#1f2937] rounded-md text-white"
                            >
                                <option value="">所有类型</option>
                                <option v-for="type in postTypes" :key="type.value" :value="type.value">
                                    {{ type.icon }} {{ type.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Search Button -->
                        <div>
                            <Button @click="handleSearch" class="w-full bg-[#ff6e02] text-white hover:bg-[#e55a00]">
                                <Search class="w-4 h-4 mr-2" />
                                搜索
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Sidebar - Categories -->
                <div class="lg:col-span-1">
                    <Card class="bg-[#374151] border-0 mb-6">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-white text-base flex items-center gap-2">
                                <Tag class="w-4 h-4" />
                                分类筛选
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <!-- All Categories -->
                            <button
                                @click="handleCategoryFilter(null)"
                                :class="[
                                    'w-full text-left p-2 rounded-lg transition-colors flex items-center gap-2',
                                    !filters.category ? 'bg-[#ff6e02] text-white' : 'text-[#999999] hover:text-white hover:bg-[#1f2937]'
                                ]"
                            >
                                <Grid class="w-4 h-4" />
                                <span class="text-sm">全部分类</span>
                            </button>

                            <!-- Individual Categories -->
                            <button
                                v-for="category in categories"
                                :key="category.id"
                                @click="handleCategoryFilter(category.slug)"
                                :class="[
                                    'w-full text-left p-2 rounded-lg transition-colors flex items-center justify-between',
                                    filters.category === category.slug ? 'bg-[#ff6e02] text-white' : 'text-[#999999] hover:text-white hover:bg-[#1f2937]'
                                ]"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="text-sm">{{ category.icon || '📝' }}</span>
                                    <span class="text-sm">{{ category.name }}</span>
                                </div>
                                <span v-if="category.posts_count" class="text-xs bg-[#1f2937] px-2 py-1 rounded">
                                    {{ category.posts_count }}
                                </span>
                            </button>
                        </CardContent>
                    </Card>

                    <!-- Stats -->
                    <Card class="bg-[#374151] border-0">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-white text-base flex items-center gap-2">
                                <TrendingUp class="w-4 h-4" />
                                统计信息
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-[#999999]">总帖子数</span>
                                <span class="text-white font-medium">{{ posts.total }}</span>
                            </div>
                            <div v-if="selectedCategory" class="flex items-center justify-between">
                                <span class="text-sm text-[#999999]">当前分类</span>
                                <span class="text-white font-medium">{{ posts.data.length }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Main Content -->
                <div class="lg:col-span-3">
                    <!-- Posts List -->
                    <div v-if="posts.data.length > 0" class="space-y-4">
                        <Link
                            v-for="post in posts.data"
                            :key="post.id"
                            :href="`/posts/${post.slug}`"
                            class="block group"
                        >
                            <Card class="bg-[#374151] border-0 hover:bg-[#1f2937] transition-colors">
                                <CardContent class="p-4">
                                    <div class="flex items-start gap-4">
                                        <!-- Post Icon/Image -->
                                        <div class="w-16 h-12 bg-[#1f2937] rounded-lg flex-shrink-0 flex items-center justify-center">
                                            <span class="text-lg">{{ post.category.icon || '📝' }}</span>
                                        </div>

                                        <!-- Post Content -->
                                        <div class="flex-1 min-w-0">
                                            <!-- Header -->
                                            <div class="flex items-start justify-between mb-2">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <Badge
                                                        class="text-white text-xs px-2 py-1"
                                                        :style="{ backgroundColor: post.category.color }"
                                                    >
                                                        {{ post.category.name }}
                                                    </Badge>
                                                    <Badge variant="outline" class="text-[#999999] border-[#999999] text-xs">
                                                        {{ getPostTypeText(post.type) }}
                                                    </Badge>
                                                    <Badge v-if="post.is_featured" class="bg-[#ff6e02] text-white text-xs">
                                                        精选
                                                    </Badge>
                                                    <Badge v-if="post.is_premium" class="bg-yellow-500 text-white text-xs">
                                                        ⭐ VIP
                                                    </Badge>
                                                </div>
                                            </div>

                                            <!-- Title -->
                                            <h3 class="text-white font-medium text-base group-hover:text-[#ff6e02] transition-colors line-clamp-2 mb-2">
                                                {{ post.title }}
                                            </h3>

                                            <!-- Excerpt -->
                                            <p class="text-[#999999] text-sm line-clamp-2 mb-3">
                                                {{ post.excerpt }}
                                            </p>

                                            <!-- Meta -->
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <!-- Author -->
                                                    <div class="flex items-center gap-2">
                                                        <Avatar class="w-6 h-6">
                                                            <AvatarFallback class="bg-[#1f2937] text-white text-xs">
                                                                {{ getInitials(post.user.creator_profile?.display_name || post.user.name) }}
                                                            </AvatarFallback>
                                                        </Avatar>
                                                        <span class="text-xs text-[#999999]">
                                                            {{ post.user.creator_profile?.display_name || post.user.name }}
                                                        </span>
                                                        <ChefHat v-if="post.user.creator_profile?.verification_status === 'verified'"
                                                                class="w-3 h-3 text-[#ff6e02]" />
                                                    </div>

                                                    <!-- Date -->
                                                    <div class="flex items-center gap-1 text-xs text-[#999999]">
                                                        <Calendar class="w-3 h-3" />
                                                        {{ formatDate(post.published_at) }}
                                                    </div>
                                                </div>

                                                <!-- Stats -->
                                                <div class="flex items-center gap-4 text-xs text-[#999999]">
                                                    <span class="flex items-center gap-1">
                                                        <Eye class="w-3 h-3" />
                                                        {{ post.view_count }}
                                                    </span>
                                                    <span class="flex items-center gap-1">
                                                        <Heart class="w-3 h-3" />
                                                        {{ post.like_count }}
                                                    </span>
                                                    <span class="flex items-center gap-1">
                                                        <MessageSquare class="w-3 h-3" />
                                                        {{ post.comment_count }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-12">
                        <div class="text-6xl mb-4">📝</div>
                        <h3 class="text-white text-lg font-medium mb-2">暂无帖子</h3>
                        <p class="text-[#999999] mb-4">
                            <span v-if="selectedCategory">该分类下暂时没有帖子</span>
                            <span v-else>暂时没有符合条件的帖子</span>
                        </p>
                        <Button @click="handleCategoryFilter(null)" variant="outline" class="text-[#ff6e02] border-[#ff6e02]">
                            浏览所有帖子
                        </Button>
                    </div>

                    <!-- Pagination -->
                    <div v-if="posts.last_page > 1" class="mt-8">
                        <div class="flex items-center justify-center gap-2">
                            <Link
                                v-for="link in posts.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-2 rounded text-sm transition-colors',
                                    link.active
                                        ? 'bg-[#ff6e02] text-white'
                                        : link.url
                                            ? 'bg-[#374151] text-white hover:bg-[#1f2937]'
                                            : 'bg-[#374151] text-[#999999] cursor-not-allowed'
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ChineseLayout>
</template>