<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import Textarea from '@/components/ui/Textarea.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { ArrowLeft, X } from 'lucide-vue-next'
import { ref } from 'vue'

const form = useForm({
    name: '',
    slug: '',
    description: '',
    color: '#3b82f6',
    icon: '',
    icon_image: null as File | null,
    category_image: null as File | null,
    sort_order: 0,
    is_active: true,
    is_nav_item: false,
    nav_route: '',
    allowed_user_types: ['all'] as string[],
})

const iconImagePreview = ref<string | null>(null)
const categoryImagePreview = ref<string | null>(null)

const handleIconImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]
    if (file) {
        form.icon_image = file
        iconImagePreview.value = URL.createObjectURL(file)
    }
}

const handleCategoryImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]
    if (file) {
        form.category_image = file
        categoryImagePreview.value = URL.createObjectURL(file)
    }
}

const removeIconImage = () => {
    form.icon_image = null
    iconImagePreview.value = null
    const fileInput = document.getElementById('icon_image') as HTMLInputElement
    if (fileInput) fileInput.value = ''
}

const removeCategoryImage = () => {
    form.category_image = null
    categoryImagePreview.value = null
    const fileInput = document.getElementById('category_image') as HTMLInputElement
    if (fileInput) fileInput.value = ''
}

const submit = () => {
    form.post('/admin/categories', {
        preserveScroll: true,
        forceFormData: true,
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
        <Head title="创建分类" />

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
                                <h1 class="text-2xl font-semibold">创建分类</h1>
                            </div>
                        </div>

                        <Card>
                            <CardHeader>
                                <CardTitle>分类信息</CardTitle>
                                <CardDescription>
                                    填写分类的基本信息和设置
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

                                    <!-- Icon Image Upload -->
                                    <div class="space-y-2">
                                        <Label for="icon_image">图标图片</Label>
                                        <div class="flex items-start gap-4">
                                            <div v-if="iconImagePreview" class="relative">
                                                <img
                                                    :src="iconImagePreview"
                                                    alt="Icon preview"
                                                    class="w-24 h-24 object-cover rounded-lg border-2 border-gray-200 dark:border-gray-700"
                                                />
                                                <Button
                                                    type="button"
                                                    variant="destructive"
                                                    size="icon"
                                                    class="absolute -top-2 -right-2 h-6 w-6 rounded-full"
                                                    @click="removeIconImage"
                                                >
                                                    <X class="h-4 w-4" />
                                                </Button>
                                            </div>
                                            <div class="flex-1">
                                                <Input
                                                    id="icon_image"
                                                    type="file"
                                                    accept="image/*"
                                                    @change="handleIconImageChange"
                                                    :class="{ 'border-red-500': form.errors.icon_image }"
                                                />
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    推荐尺寸: 200x200px, 最大 2MB
                                                </p>
                                                <p v-if="form.errors.icon_image" class="text-sm text-red-500 mt-1">
                                                    {{ form.errors.icon_image }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Category Image Upload -->
                                    <div class="space-y-2">
                                        <Label for="category_image">分类封面图</Label>
                                        <div class="flex items-start gap-4">
                                            <div v-if="categoryImagePreview" class="relative">
                                                <img
                                                    :src="categoryImagePreview"
                                                    alt="Category preview"
                                                    class="w-48 h-32 object-cover rounded-lg border-2 border-gray-200 dark:border-gray-700"
                                                />
                                                <Button
                                                    type="button"
                                                    variant="destructive"
                                                    size="icon"
                                                    class="absolute -top-2 -right-2 h-6 w-6 rounded-full"
                                                    @click="removeCategoryImage"
                                                >
                                                    <X class="h-4 w-4" />
                                                </Button>
                                            </div>
                                            <div class="flex-1">
                                                <Input
                                                    id="category_image"
                                                    type="file"
                                                    accept="image/*"
                                                    @change="handleCategoryImageChange"
                                                    :class="{ 'border-red-500': form.errors.category_image }"
                                                />
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    推荐尺寸: 800x600px, 最大 5MB
                                                </p>
                                                <p v-if="form.errors.category_image" class="text-sm text-red-500 mt-1">
                                                    {{ form.errors.category_image }}
                                                </p>
                                            </div>
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

                                    <!-- Allowed User Types -->
                                    <div class="space-y-2">
                                        <Label>允许发帖的用户类型</Label>
                                        <div class="space-y-2">
                                            <div class="flex items-center space-x-2">
                                                <input
                                                    id="type_all"
                                                    type="checkbox"
                                                    value="all"
                                                    :checked="form.allowed_user_types.includes('all')"
                                                    @change="(e) => {
                                                        const checked = (e.target as HTMLInputElement).checked;
                                                        if (checked) {
                                                            form.allowed_user_types = ['all'];
                                                        } else {
                                                            form.allowed_user_types = form.allowed_user_types.filter(t => t !== 'all');
                                                        }
                                                    }"
                                                    class="rounded"
                                                />
                                                <Label for="type_all" class="cursor-pointer">所有用户</Label>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <input
                                                    id="type_creator"
                                                    type="checkbox"
                                                    value="creator"
                                                    :checked="form.allowed_user_types.includes('creator')"
                                                    :disabled="form.allowed_user_types.includes('all')"
                                                    @change="(e) => {
                                                        const checked = (e.target as HTMLInputElement).checked;
                                                        if (checked) {
                                                            form.allowed_user_types = form.allowed_user_types.filter(t => t !== 'all');
                                                            form.allowed_user_types.push('creator');
                                                        } else {
                                                            form.allowed_user_types = form.allowed_user_types.filter(t => t !== 'creator');
                                                        }
                                                    }"
                                                    class="rounded"
                                                />
                                                <Label for="type_creator" class="cursor-pointer">仅创作者</Label>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <input
                                                    id="type_regular"
                                                    type="checkbox"
                                                    value="regular"
                                                    :checked="form.allowed_user_types.includes('regular')"
                                                    :disabled="form.allowed_user_types.includes('all')"
                                                    @change="(e) => {
                                                        const checked = (e.target as HTMLInputElement).checked;
                                                        if (checked) {
                                                            form.allowed_user_types = form.allowed_user_types.filter(t => t !== 'all');
                                                            form.allowed_user_types.push('regular');
                                                        } else {
                                                            form.allowed_user_types = form.allowed_user_types.filter(t => t !== 'regular');
                                                        }
                                                    }"
                                                    class="rounded"
                                                />
                                                <Label for="type_regular" class="cursor-pointer">仅普通用户</Label>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <input
                                                    id="type_admin"
                                                    type="checkbox"
                                                    value="admin"
                                                    :checked="form.allowed_user_types.includes('admin')"
                                                    :disabled="form.allowed_user_types.includes('all')"
                                                    @change="(e) => {
                                                        const checked = (e.target as HTMLInputElement).checked;
                                                        if (checked) {
                                                            form.allowed_user_types = form.allowed_user_types.filter(t => t !== 'all');
                                                            form.allowed_user_types.push('admin');
                                                        } else {
                                                            form.allowed_user_types = form.allowed_user_types.filter(t => t !== 'admin');
                                                        }
                                                    }"
                                                    class="rounded"
                                                />
                                                <Label for="type_admin" class="cursor-pointer">仅管理员</Label>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            选择哪些类型的用户可以在此分类下发帖。管理员始终可以发帖。
                                        </p>
                                        <p v-if="form.errors.allowed_user_types" class="text-sm text-red-500">
                                            {{ form.errors.allowed_user_types }}
                                        </p>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="flex gap-4">
                                        <Button
                                            type="submit"
                                            :disabled="form.processing"
                                        >
                                            {{ form.processing ? '创建中...' : '创建分类' }}
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
