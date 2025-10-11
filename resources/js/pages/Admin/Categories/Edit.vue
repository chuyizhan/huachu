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
    slug: string
    description: string | null
    color: string
    icon: string | null
    sort_order: number
    is_active: boolean
    is_nav_item: boolean
    nav_route: string | null
    posts_count: number
    created_at: string
    updated_at: string
}

interface Props {
    category: Category
}

const props = defineProps<Props>()

const form = useForm({
    name: props.category.name,
    slug: props.category.slug,
    description: props.category.description || '',
    color: props.category.color,
    icon: props.category.icon || '',
    sort_order: props.category.sort_order,
    is_active: props.category.is_active,
    is_nav_item: props.category.is_nav_item,
    nav_route: props.category.nav_route || '',
})

const submit = () => {
    form.put(`/admin/categories/${props.category.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Success message will be shown via Laravel flash message
        },
    })
}

const goBack = () => {
    router.visit('/admin/categories')
}
</script>

<template>
    <AppLayout>
        <Head :title="`编辑分类 - ${category.name}`" />

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
                                <h1 class="text-2xl font-semibold">编辑分类</h1>
                            </div>
                        </div>

                        <Card>
                            <CardHeader>
                                <CardTitle>分类信息</CardTitle>
                                <CardDescription>
                                    编辑分类的基本信息和设置
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form @submit.prevent="submit" class="space-y-6">
                                    <!-- Name -->
                                    <div class="space-y-2">
                                        <Label for="name">分类名称 *</Label>
                                        <Input
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            required
                                            :class="{ 'border-red-500': form.errors.name }"
                                        />
                                        <p v-if="form.errors.name" class="text-sm text-red-500">
                                            {{ form.errors.name }}
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
                                            placeholder="category-slug"
                                            :class="{ 'border-red-500': form.errors.slug }"
                                        />
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            用于URL的唯一标识符，只能包含小写字母、数字和连字符
                                        </p>
                                        <p v-if="form.errors.slug" class="text-sm text-red-500">
                                            {{ form.errors.slug }}
                                        </p>
                                    </div>

                                    <!-- Description -->
                                    <div class="space-y-2">
                                        <Label for="description">描述</Label>
                                        <Textarea
                                            id="description"
                                            v-model="form.description"
                                            rows="3"
                                            placeholder="分类描述..."
                                            :class="{ 'border-red-500': form.errors.description }"
                                        />
                                        <p v-if="form.errors.description" class="text-sm text-red-500">
                                            {{ form.errors.description }}
                                        </p>
                                    </div>

                                    <!-- Color and Icon -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Color -->
                                        <div class="space-y-2">
                                            <Label for="color">颜色</Label>
                                            <div class="flex gap-2">
                                                <Input
                                                    id="color"
                                                    v-model="form.color"
                                                    type="color"
                                                    class="w-20 h-10 p-1 cursor-pointer"
                                                    :class="{ 'border-red-500': form.errors.color }"
                                                />
                                                <Input
                                                    v-model="form.color"
                                                    type="text"
                                                    placeholder="#3b82f6"
                                                    class="flex-1"
                                                    :class="{ 'border-red-500': form.errors.color }"
                                                />
                                            </div>
                                            <p v-if="form.errors.color" class="text-sm text-red-500">
                                                {{ form.errors.color }}
                                            </p>
                                        </div>

                                        <!-- Icon -->
                                        <div class="space-y-2">
                                            <Label for="icon">图标类名</Label>
                                            <Input
                                                id="icon"
                                                v-model="form.icon"
                                                type="text"
                                                placeholder="lucide-icon-name"
                                                :class="{ 'border-red-500': form.errors.icon }"
                                            />
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                图标类名或标识符
                                            </p>
                                            <p v-if="form.errors.icon" class="text-sm text-red-500">
                                                {{ form.errors.icon }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Sort Order -->
                                    <div class="space-y-2">
                                        <Label for="sort_order">排序顺序</Label>
                                        <Input
                                            id="sort_order"
                                            v-model.number="form.sort_order"
                                            type="number"
                                            :class="{ 'border-red-500': form.errors.sort_order }"
                                        />
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            数字越小排序越靠前
                                        </p>
                                        <p v-if="form.errors.sort_order" class="text-sm text-red-500">
                                            {{ form.errors.sort_order }}
                                        </p>
                                    </div>

                                    <!-- Is Active -->
                                    <div class="flex items-center space-x-2">
                                        <input
                                            id="is_active"
                                            v-model="form.is_active"
                                            type="checkbox"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                        />
                                        <Label for="is_active" class="cursor-pointer">
                                            启用分类
                                        </Label>
                                    </div>

                                    <!-- Is Nav Item -->
                                    <div class="space-y-4">
                                        <div class="flex items-center space-x-2">
                                            <input
                                                id="is_nav_item"
                                                v-model="form.is_nav_item"
                                                type="checkbox"
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                            />
                                            <Label for="is_nav_item" class="cursor-pointer">
                                                显示在导航菜单
                                            </Label>
                                        </div>

                                        <!-- Nav Route (only show if is_nav_item is true) -->
                                        <div v-if="form.is_nav_item" class="space-y-2 pl-6">
                                            <Label for="nav_route">导航路由</Label>
                                            <Input
                                                id="nav_route"
                                                v-model="form.nav_route"
                                                type="text"
                                                placeholder="/category/slug"
                                                :class="{ 'border-red-500': form.errors.nav_route }"
                                            />
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                导航菜单中的链接地址
                                            </p>
                                            <p v-if="form.errors.nav_route" class="text-sm text-red-500">
                                                {{ form.errors.nav_route }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Category Stats -->
                                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                        <h3 class="text-sm font-medium mb-2">分类统计</h3>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                            <p>关联帖子数: <span class="font-semibold">{{ category.posts_count }}</span></p>
                                            <p>创建时间: {{ new Date(category.created_at).toLocaleString('zh-CN') }}</p>
                                            <p>更新时间: {{ new Date(category.updated_at).toLocaleString('zh-CN') }}</p>
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
