<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import Textarea from '@/components/ui/Textarea.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { ArrowLeft } from 'lucide-vue-next'
import { ref } from 'vue'

const form = useForm({
    level: 0,
    name: '',
    slug: '',
    description: '',
    price: 0,
    period_days: 30,
    features: [] as string[],
    max_premium_posts: null as number | null,
    can_create_premium_content: false,
    priority_support: false,
    badge_color: '#fbbf24',
    badge_text_color: '#ffffff',
    sort_order: 0,
    is_active: true,
})

const featureInput = ref('')

const addFeature = () => {
    if (featureInput.value.trim()) {
        form.features.push(featureInput.value.trim())
        featureInput.value = ''
    }
}

const removeFeature = (index: number) => {
    form.features.splice(index, 1)
}

const submit = () => {
    form.post('/admin/plans')
}

const goBack = () => {
    router.visit('/admin/plans')
}
</script>

<template>
    <AppLayout>
        <Head title="创建套餐" />

        <div class="py-12">
            <div class="w-full sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-6 flex items-center gap-4">
                            <Button variant="outline" size="icon" @click="goBack">
                                <ArrowLeft class="h-4 w-4" />
                            </Button>
                            <h1 class="text-2xl font-semibold">创建套餐</h1>
                        </div>

                        <Card>
                            <CardHeader>
                                <CardTitle>套餐信息</CardTitle>
                                <CardDescription>填写套餐的详细信息</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form @submit.prevent="submit" class="space-y-6">
                                    <!-- Basic Info -->
                                    <div class="space-y-4">
                                        <h3 class="text-lg font-medium">基本信息</h3>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <Label for="level">等级 *</Label>
                                                <Input
                                                    id="level"
                                                    v-model.number="form.level"
                                                    type="number"
                                                    required
                                                    :class="{ 'border-red-500': form.errors.level }"
                                                />
                                                <p v-if="form.errors.level" class="text-sm text-red-500">{{ form.errors.level }}</p>
                                            </div>

                                            <div class="space-y-2">
                                                <Label for="sort_order">排序 *</Label>
                                                <Input
                                                    id="sort_order"
                                                    v-model.number="form.sort_order"
                                                    type="number"
                                                    required
                                                    :class="{ 'border-red-500': form.errors.sort_order }"
                                                />
                                                <p v-if="form.errors.sort_order" class="text-sm text-red-500">{{ form.errors.sort_order }}</p>
                                            </div>
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="name">套餐名称 *</Label>
                                            <Input
                                                id="name"
                                                v-model="form.name"
                                                required
                                                :class="{ 'border-red-500': form.errors.name }"
                                            />
                                            <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="slug">别名 *</Label>
                                            <Input
                                                id="slug"
                                                v-model="form.slug"
                                                required
                                                :class="{ 'border-red-500': form.errors.slug }"
                                            />
                                            <p class="text-sm text-gray-500">用于URL的唯一标识</p>
                                            <p v-if="form.errors.slug" class="text-sm text-red-500">{{ form.errors.slug }}</p>
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="description">描述</Label>
                                            <Textarea
                                                id="description"
                                                v-model="form.description"
                                                rows="3"
                                                :class="{ 'border-red-500': form.errors.description }"
                                            />
                                            <p v-if="form.errors.description" class="text-sm text-red-500">{{ form.errors.description }}</p>
                                        </div>
                                    </div>

                                    <!-- Pricing -->
                                    <div class="space-y-4 pt-6 border-t">
                                        <h3 class="text-lg font-medium">价格与周期</h3>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <Label for="price">价格 (¥) *</Label>
                                                <Input
                                                    id="price"
                                                    v-model.number="form.price"
                                                    type="number"
                                                    step="0.01"
                                                    required
                                                    :class="{ 'border-red-500': form.errors.price }"
                                                />
                                                <p v-if="form.errors.price" class="text-sm text-red-500">{{ form.errors.price }}</p>
                                            </div>

                                            <div class="space-y-2">
                                                <Label for="period_days">有效期 (天) *</Label>
                                                <Input
                                                    id="period_days"
                                                    v-model.number="form.period_days"
                                                    type="number"
                                                    required
                                                    :class="{ 'border-red-500': form.errors.period_days }"
                                                />
                                                <p v-if="form.errors.period_days" class="text-sm text-red-500">{{ form.errors.period_days }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Features -->
                                    <div class="space-y-4 pt-6 border-t">
                                        <h3 class="text-lg font-medium">功能特性</h3>

                                        <div class="space-y-2">
                                            <Label>功能列表</Label>
                                            <div class="flex gap-2">
                                                <Input
                                                    v-model="featureInput"
                                                    placeholder="输入功能特性..."
                                                    @keyup.enter="addFeature"
                                                />
                                                <Button type="button" @click="addFeature">添加</Button>
                                            </div>
                                            <div v-if="form.features.length > 0" class="mt-2 space-y-2">
                                                <div v-for="(feature, index) in form.features" :key="index" class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-900 rounded">
                                                    <span>{{ feature }}</span>
                                                    <Button type="button" variant="ghost" size="sm" @click="removeFeature(index)">删除</Button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="space-y-2">
                                            <Label for="max_premium_posts">最大付费帖子数</Label>
                                            <Input
                                                id="max_premium_posts"
                                                v-model.number="form.max_premium_posts"
                                                type="number"
                                                placeholder="留空表示无限制"
                                                :class="{ 'border-red-500': form.errors.max_premium_posts }"
                                            />
                                            <p class="text-sm text-gray-500">留空表示无限制</p>
                                            <p v-if="form.errors.max_premium_posts" class="text-sm text-red-500">{{ form.errors.max_premium_posts }}</p>
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <input
                                                id="can_create_premium_content"
                                                v-model="form.can_create_premium_content"
                                                type="checkbox"
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded"
                                            />
                                            <Label for="can_create_premium_content" class="cursor-pointer">允许创建付费内容</Label>
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <input
                                                id="priority_support"
                                                v-model="form.priority_support"
                                                type="checkbox"
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded"
                                            />
                                            <Label for="priority_support" class="cursor-pointer">优先客服支持</Label>
                                        </div>
                                    </div>

                                    <!-- Badge Settings -->
                                    <div class="space-y-4 pt-6 border-t">
                                        <h3 class="text-lg font-medium">徽章设置</h3>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="space-y-2">
                                                <Label for="badge_color">徽章颜色 *</Label>
                                                <div class="flex gap-2">
                                                    <Input
                                                        id="badge_color"
                                                        v-model="form.badge_color"
                                                        type="color"
                                                        class="w-20"
                                                    />
                                                    <Input v-model="form.badge_color" />
                                                </div>
                                                <p v-if="form.errors.badge_color" class="text-sm text-red-500">{{ form.errors.badge_color }}</p>
                                            </div>

                                            <div class="space-y-2">
                                                <Label for="badge_text_color">文字颜色 *</Label>
                                                <div class="flex gap-2">
                                                    <Input
                                                        id="badge_text_color"
                                                        v-model="form.badge_text_color"
                                                        type="color"
                                                        class="w-20"
                                                    />
                                                    <Input v-model="form.badge_text_color" />
                                                </div>
                                                <p v-if="form.errors.badge_text_color" class="text-sm text-red-500">{{ form.errors.badge_text_color }}</p>
                                            </div>
                                        </div>

                                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded">
                                            <p class="text-sm mb-2">预览：</p>
                                            <span
                                                class="inline-block px-3 py-1 rounded text-sm font-medium"
                                                :style="{ backgroundColor: form.badge_color, color: form.badge_text_color }"
                                            >
                                                {{ form.name || '套餐名称' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Status -->
                                    <div class="space-y-4 pt-6 border-t">
                                        <div class="flex items-center space-x-2">
                                            <input
                                                id="is_active"
                                                v-model="form.is_active"
                                                type="checkbox"
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded"
                                            />
                                            <Label for="is_active" class="cursor-pointer">启用套餐</Label>
                                        </div>
                                    </div>

                                    <!-- Submit Buttons -->
                                    <div class="flex gap-4 pt-6">
                                        <Button type="submit" :disabled="form.processing">
                                            {{ form.processing ? '创建中...' : '创建套餐' }}
                                        </Button>
                                        <Button type="button" variant="outline" @click="goBack">
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
