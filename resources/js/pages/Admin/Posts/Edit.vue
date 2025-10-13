<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import Textarea from '@/components/ui/Textarea.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { ArrowLeft } from 'lucide-vue-next'

interface Category {
    id: number
    name: string
}

interface User {
    id: number
    name: string
    login_name: string
}

interface Post {
    id: number
    title: string
    slug: string
    excerpt: string | null
    content: string
    user: User
    category: Category
    post_category_id: number
    type: string
    status: string
    is_featured: boolean
    is_premium: boolean
    price: number | null
    free_after: string | null
    published_at: string | null
    view_count: number
    like_count: number
    created_at: string
    updated_at: string
}

interface Props {
    post: Post
    categories: Category[]
}

const props = defineProps<Props>()

const form = useForm({
    title: props.post.title,
    slug: props.post.slug,
    post_category_id: props.post.post_category_id,
    excerpt: props.post.excerpt || '',
    content: props.post.content,
    type: props.post.type,
    status: props.post.status,
    is_featured: props.post.is_featured,
    is_premium: props.post.is_premium,
    price: props.post.price || '',
    free_after: props.post.free_after ? props.post.free_after.split('T')[0] : '',
    published_at: props.post.published_at ? props.post.published_at.split('T')[0] : '',
})

const submit = () => {
    form.put(`/admin/posts/${props.post.id}`, {
        preserveScroll: true,
    })
}

const goBack = () => {
    router.visit('/admin/posts')
}
</script>

<template>
    <AppLayout>
        <Head :title="`编辑帖子 - ${post.title}`" />

        <div class="py-12">
            <div class="w-full sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-6 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <Button
                                    variant="outline"
                                    size="icon"
                                    @click="goBack"
                                >
                                    <ArrowLeft class="h-4 w-4" />
                                </Button>
                                <h1 class="text-2xl font-semibold">编辑帖子</h1>
                            </div>
                        </div>

                        <Card>
                            <CardHeader>
                                <CardTitle>帖子信息</CardTitle>
                                <CardDescription>
                                    编辑帖子的详细内容和设置
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form @submit.prevent="submit" class="space-y-6">
                                    <!-- Basic Info Section -->
                                    <div class="space-y-4">
                                        <h3 class="text-lg font-medium">基本信息</h3>

                                        <!-- Title -->
                                        <div class="space-y-2">
                                            <Label for="title">标题 *</Label>
                                            <Input
                                                id="title"
                                                v-model="form.title"
                                                type="text"
                                                required
                                                :class="{ 'border-red-500': form.errors.title }"
                                            />
                                            <p v-if="form.errors.title" class="text-sm text-red-500">
                                                {{ form.errors.title }}
                                            </p>
                                        </div>

                                        <!-- Slug -->
                                        <div class="space-y-2">
                                            <Label for="slug">别名 (URL) *</Label>
                                            <Input
                                                id="slug"
                                                v-model="form.slug"
                                                type="text"
                                                required
                                                :class="{ 'border-red-500': form.errors.slug }"
                                            />
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                用于URL的唯一标识符
                                            </p>
                                            <p v-if="form.errors.slug" class="text-sm text-red-500">
                                                {{ form.errors.slug }}
                                            </p>
                                        </div>

                                        <!-- Category -->
                                        <div class="space-y-2">
                                            <Label for="post_category_id">分类 *</Label>
                                            <select
                                                id="post_category_id"
                                                v-model.number="form.post_category_id"
                                                required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                :class="{ 'border-red-500': form.errors.post_category_id }"
                                            >
                                                <option value="">选择分类</option>
                                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                                    {{ category.name }}
                                                </option>
                                            </select>
                                            <p v-if="form.errors.post_category_id" class="text-sm text-red-500">
                                                {{ form.errors.post_category_id }}
                                            </p>
                                        </div>

                                        <!-- Excerpt -->
                                        <div class="space-y-2">
                                            <Label for="excerpt">摘要</Label>
                                            <Textarea
                                                id="excerpt"
                                                v-model="form.excerpt"
                                                rows="2"
                                                placeholder="简短的帖子描述..."
                                                :class="{ 'border-red-500': form.errors.excerpt }"
                                            />
                                            <p v-if="form.errors.excerpt" class="text-sm text-red-500">
                                                {{ form.errors.excerpt }}
                                            </p>
                                        </div>

                                        <!-- Content -->
                                        <div class="space-y-2">
                                            <Label for="content">内容 *</Label>
                                            <Textarea
                                                id="content"
                                                v-model="form.content"
                                                rows="10"
                                                required
                                                placeholder="帖子的完整内容..."
                                                :class="{ 'border-red-500': form.errors.content }"
                                            />
                                            <p v-if="form.errors.content" class="text-sm text-red-500">
                                                {{ form.errors.content }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Settings Section -->
                                    <div class="space-y-4 pt-6 border-t">
                                        <h3 class="text-lg font-medium">帖子设置</h3>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <!-- Type -->
                                            <div class="space-y-2">
                                                <Label for="type">类型</Label>
                                                <select
                                                    id="type"
                                                    v-model="form.type"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                    :class="{ 'border-red-500': form.errors.type }"
                                                >
                                                    <option value="article">文章</option>
                                                    <option value="video">视频</option>
                                                    <option value="image">图片</option>
                                                </select>
                                                <p v-if="form.errors.type" class="text-sm text-red-500">
                                                    {{ form.errors.type }}
                                                </p>
                                            </div>

                                            <!-- Status -->
                                            <div class="space-y-2">
                                                <Label for="status">状态</Label>
                                                <select
                                                    id="status"
                                                    v-model="form.status"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                    :class="{ 'border-red-500': form.errors.status }"
                                                >
                                                    <option value="draft">草稿</option>
                                                    <option value="published">已发布</option>
                                                    <option value="archived">已归档</option>
                                                </select>
                                                <p v-if="form.errors.status" class="text-sm text-red-500">
                                                    {{ form.errors.status }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Pricing -->
                                        <div class="space-y-4">
                                            <h4 class="text-md font-medium">付费设置</h4>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                <!-- Price -->
                                                <div class="space-y-2">
                                                    <Label for="price">价格 (积分)</Label>
                                                    <Input
                                                        id="price"
                                                        v-model.number="form.price"
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        placeholder="0 = 免费"
                                                        :class="{ 'border-red-500': form.errors.price }"
                                                    />
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        设置为0或留空表示免费
                                                    </p>
                                                    <p v-if="form.errors.price" class="text-sm text-red-500">
                                                        {{ form.errors.price }}
                                                    </p>
                                                </div>

                                                <!-- Free After -->
                                                <div class="space-y-2">
                                                    <Label for="free_after">免费开放日期</Label>
                                                    <Input
                                                        id="free_after"
                                                        v-model="form.free_after"
                                                        type="date"
                                                        :class="{ 'border-red-500': form.errors.free_after }"
                                                    />
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        此日期后付费内容将变为免费
                                                    </p>
                                                    <p v-if="form.errors.free_after" class="text-sm text-red-500">
                                                        {{ form.errors.free_after }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Flags -->
                                        <div class="space-y-4 pt-4">
                                            <h4 class="text-md font-medium">标记</h4>

                                            <!-- Is Featured -->
                                            <div class="flex items-center space-x-2">
                                                <input
                                                    id="is_featured"
                                                    v-model="form.is_featured"
                                                    type="checkbox"
                                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                />
                                                <Label for="is_featured" class="cursor-pointer">
                                                    精选帖子
                                                </Label>
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 pl-6">
                                                在首页和特殊位置展示
                                            </p>

                                            <!-- Is Premium -->
                                            <div class="flex items-center space-x-2">
                                                <input
                                                    id="is_premium"
                                                    v-model="form.is_premium"
                                                    type="checkbox"
                                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                />
                                                <Label for="is_premium" class="cursor-pointer">
                                                    会员专享
                                                </Label>
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 pl-6">
                                                仅会员可访问
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Post Info -->
                                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                        <h3 class="text-sm font-medium mb-2">帖子信息</h3>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                            <p>帖子ID: <span class="font-semibold">{{ post.id }}</span></p>
                                            <p>作者: <span class="font-semibold">{{ post.user.name }} (@{{ post.user.login_name }})</span></p>
                                            <p>浏览量: <span class="font-semibold">{{ post.view_count }}</span></p>
                                            <p>点赞数: <span class="font-semibold">{{ post.like_count }}</span></p>
                                            <p>创建时间: {{ new Date(post.created_at).toLocaleString('zh-CN') }}</p>
                                            <p>更新时间: {{ new Date(post.updated_at).toLocaleString('zh-CN') }}</p>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="flex gap-4">
                                        <Button
                                            type="submit"
                                            :disabled="form.processing"
                                        >
                                            {{ form.processing ? '保存中...' : '保存更改' }}
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            @click="goBack"
                                        >
                                            取消
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
