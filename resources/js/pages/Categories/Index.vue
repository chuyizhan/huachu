<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Head, Link } from '@inertiajs/vue3';
import { Grid3x3, FileText } from 'lucide-vue-next';

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string;
    color: string;
    icon: string;
    icon_image?: string;
    category_image?: string;
    posts_count: number;
    is_active: boolean;
}

interface Props {
    categories: Category[];
}

const props = defineProps<Props>();
</script>

<template>
    <WebLayout>
        <Head title="分类" />

        <div class="min-h-screen py-4 md:py-8">
            <div class="max-w-6xl mx-auto px-3 md:px-4">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4 md:mb-8">
                    <div>
                        <h1 class="text-xl md:text-3xl font-bold text-white mb-1 md:mb-2 flex items-center gap-2 md:gap-3">
                            <Grid3x3 class="h-6 w-6 md:h-8 md:w-8 text-[#ff6e02]" />
                            分类
                        </h1>
                        <p class="text-xs md:text-base text-[#999999]">浏览不同内容分类</p>
                    </div>
                </div>

                <!-- Categories Grid -->
                <div v-if="categories && categories.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-6">
                    <Link
                        v-for="category in categories"
                        :key="category.id"
                        :href="`/community/posts?category=${category.slug}`"
                        class="block"
                    >
                        <Card class="bg-[#374151] border-[#4B5563] hover:border-[#ff6e02] transition-all h-full cursor-pointer group">
                            <CardHeader class="p-4 md:p-6">
                                <!-- Category Image or Icon -->
                                <div class="flex justify-center mb-3 md:mb-4">
                                    <div
                                        v-if="category.category_image"
                                        class="w-16 h-16 md:w-20 md:h-20 rounded-lg overflow-hidden border-2 border-[#ff6e02]"
                                    >
                                        <img
                                            :src="category.category_image"
                                            :alt="category.name"
                                            class="w-full h-full object-cover"
                                        />
                                    </div>
                                    <div
                                        v-else
                                        class="w-16 h-16 md:w-20 md:h-20 rounded-lg flex items-center justify-center border-2 border-[#ff6e02] group-hover:bg-[#ff6e02]/10 transition-colors"
                                        :style="{ backgroundColor: category.color ? `${category.color}20` : '#ff6e0220' }"
                                    >
                                        <span class="text-3xl md:text-4xl">{{ category.icon || '📁' }}</span>
                                    </div>
                                </div>

                                <!-- Category Name -->
                                <CardTitle class="text-center text-base md:text-xl text-white group-hover:text-[#ff6e02] transition-colors mb-1 md:mb-2">
                                    {{ category.name }}
                                </CardTitle>

                                <!-- Category Description -->
                                <CardDescription
                                    v-if="category.description"
                                    class="text-center text-xs md:text-sm text-[#999999] line-clamp-2 mb-2 md:mb-3"
                                >
                                    {{ category.description }}
                                </CardDescription>

                                <!-- Post Count -->
                                <div class="flex items-center justify-center gap-1 md:gap-2 text-xs md:text-sm text-[#999999]">
                                    <FileText class="w-3 h-3 md:w-4 md:h-4" />
                                    <span>{{ category.posts_count }} 篇内容</span>
                                </div>
                            </CardHeader>
                        </Card>
                    </Link>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <Grid3x3 class="h-16 w-16 text-[#999999] mx-auto mb-4" />
                    <h3 class="text-lg font-semibold text-white mb-2">暂无分类</h3>
                    <p class="text-[#999999]">
                        还没有创建任何分类
                    </p>
                </div>
            </div>
        </div>
    </WebLayout>
</template>
