<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import Textarea from '@/components/ui/Textarea.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { ArrowLeft, RefreshCw } from 'lucide-vue-next'
import { ref } from 'vue'

interface User {
    id: number
    name: string
    email: string
    login_name: string
    is_admin: boolean
    is_creator: boolean
    is_verified: boolean
    is_top_creator: boolean
    type: number
    status: number
    credits: number
    balance: number
}

interface Props {
    user: User
}

const props = defineProps<Props>()

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    login_name: props.user.login_name,
    password: '',
    password_confirmation: '',
    is_creator: props.user.is_creator,
    is_verified: props.user.is_verified,
    is_top_creator: props.user.is_top_creator,
    type: props.user.type,
    status: props.user.status,
    credits: props.user.credits,
    balance: props.user.balance,
})

const submit = () => {
    form.put(`/admin/users/${props.user.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Success message will be shown via Laravel flash message
        },
    })
}

const goBack = () => {
    router.visit('/admin/users')
}

const consolidating = ref(false)

const consolidateBalances = () => {
    if (confirm('确定要根据交易记录重新计算用户的积分和金币余额吗？这将覆盖当前余额。')) {
        consolidating.value = true
        router.post(`/admin/users/${props.user.id}/consolidate-balances`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                consolidating.value = false
            },
            onError: () => {
                consolidating.value = false
            },
        })
    }
}
</script>

<template>
    <AppLayout>
        <Head :title="`编辑用户 - ${user.name}`" />

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
                                <h1 class="text-2xl font-semibold">编辑用户</h1>
                            </div>
                        </div>

                        <Card>
                            <CardHeader>
                                <CardTitle>用户信息</CardTitle>
                                <CardDescription>
                                    编辑用户的基本信息和权限设置
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form @submit.prevent="submit" class="space-y-6">
                                    <!-- Basic Info Section -->
                                    <div class="space-y-4">
                                        <h3 class="text-lg font-medium">基本信息</h3>

                                        <!-- Name -->
                                        <div class="space-y-2">
                                            <Label for="name">姓名 *</Label>
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

                                        <!-- Email -->
                                        <div class="space-y-2">
                                            <Label for="email">邮箱 *</Label>
                                            <Input
                                                id="email"
                                                v-model="form.email"
                                                type="email"
                                                required
                                                :class="{ 'border-red-500': form.errors.email }"
                                            />
                                            <p v-if="form.errors.email" class="text-sm text-red-500">
                                                {{ form.errors.email }}
                                            </p>
                                        </div>

                                        <!-- Login Name -->
                                        <div class="space-y-2">
                                            <Label for="login_name">用户名 *</Label>
                                            <Input
                                                id="login_name"
                                                v-model="form.login_name"
                                                type="text"
                                                required
                                                :class="{ 'border-red-500': form.errors.login_name }"
                                            />
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                用于登录的唯一用户名
                                            </p>
                                            <p v-if="form.errors.login_name" class="text-sm text-red-500">
                                                {{ form.errors.login_name }}
                                            </p>
                                        </div>

                                        <!-- Password -->
                                        <div class="space-y-2">
                                            <Label for="password">新密码</Label>
                                            <Input
                                                id="password"
                                                v-model="form.password"
                                                type="password"
                                                autocomplete="new-password"
                                                :class="{ 'border-red-500': form.errors.password }"
                                            />
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                留空表示不修改密码
                                            </p>
                                            <p v-if="form.errors.password" class="text-sm text-red-500">
                                                {{ form.errors.password }}
                                            </p>
                                        </div>

                                        <!-- Password Confirmation -->
                                        <div class="space-y-2">
                                            <Label for="password_confirmation">确认新密码</Label>
                                            <Input
                                                id="password_confirmation"
                                                v-model="form.password_confirmation"
                                                type="password"
                                                autocomplete="new-password"
                                                :class="{ 'border-red-500': form.errors.password_confirmation }"
                                            />
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                请再次输入新密码
                                            </p>
                                            <p v-if="form.errors.password_confirmation" class="text-sm text-red-500">
                                                {{ form.errors.password_confirmation }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Account Settings Section -->
                                    <div class="space-y-4 pt-6 border-t">
                                        <h3 class="text-lg font-medium">账户设置</h3>

                                        <!-- Type -->
                                        <div class="space-y-2">
                                            <Label for="type">用户类型</Label>
                                            <select
                                                id="type"
                                                v-model.number="form.type"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                :class="{ 'border-red-500': form.errors.type }"
                                            >
                                                <option :value="1">普通用户</option>
                                                <option :value="2">高级用户</option>
                                            </select>
                                            <p v-if="form.errors.type" class="text-sm text-red-500">
                                                {{ form.errors.type }}
                                            </p>
                                        </div>

                                        <!-- Status -->
                                        <div class="space-y-2">
                                            <Label for="status">账户状态</Label>
                                            <select
                                                id="status"
                                                v-model.number="form.status"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                :class="{ 'border-red-500': form.errors.status }"
                                            >
                                                <option :value="1">正常</option>
                                                <option :value="2">暂停</option>
                                                <option :value="3">禁止</option>
                                            </select>
                                            <p v-if="form.errors.status" class="text-sm text-red-500">
                                                {{ form.errors.status }}
                                            </p>
                                        </div>

                                        <!-- Credits -->
                                        <div class="space-y-2">
                                            <Label for="credits">金币余额</Label>
                                            <Input
                                                id="credits"
                                                v-model.number="form.credits"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                :class="{ 'border-red-500': form.errors.credits }"
                                            />
                                            <p v-if="form.errors.credits" class="text-sm text-red-500">
                                                {{ form.errors.credits }}
                                            </p>
                                        </div>

                                        <!-- Balance -->
                                        <div class="space-y-2">
                                            <Label for="balance">积分余额</Label>
                                            <Input
                                                id="balance"
                                                v-model.number="form.balance"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                :class="{ 'border-red-500': form.errors.balance }"
                                            />
                                            <p v-if="form.errors.balance" class="text-sm text-red-500">
                                                {{ form.errors.balance }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Permissions Section -->
                                    <div class="space-y-4 pt-6 border-t">
                                        <h3 class="text-lg font-medium">权限设置</h3>

                                        <!-- Is Creator -->
                                        <div class="flex items-center space-x-2">
                                            <input
                                                id="is_creator"
                                                v-model="form.is_creator"
                                                type="checkbox"
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                            />
                                            <Label for="is_creator" class="cursor-pointer">
                                                创作者权限
                                            </Label>
                                        </div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 pl-6">
                                            可以发布和管理内容
                                        </p>

                                        <!-- Is Verified -->
                                        <div class="flex items-center space-x-2">
                                            <input
                                                id="is_verified"
                                                v-model="form.is_verified"
                                                type="checkbox"
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                            />
                                            <Label for="is_verified" class="cursor-pointer">
                                                已认证
                                            </Label>
                                        </div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 pl-6">
                                            显示认证标识，表示身份已验证
                                        </p>

                                        <!-- Is Top Creator -->
                                        <div class="flex items-center space-x-2">
                                            <input
                                                id="is_top_creator"
                                                v-model="form.is_top_creator"
                                                type="checkbox"
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                            />
                                            <Label for="is_top_creator" class="cursor-pointer">
                                                顶级创作者
                                            </Label>
                                        </div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 pl-6">
                                            显示特殊标识，表示优质创作者
                                        </p>
                                    </div>

                                    <!-- User Info -->
                                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                        <h3 class="text-sm font-medium mb-2">用户信息</h3>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                            <p>用户ID: <span class="font-semibold">{{ user.id }}</span></p>
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
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            @click="consolidateBalances"
                                            :disabled="consolidating"
                                        >
                                            <RefreshCw :class="{ 'animate-spin': consolidating }" class="mr-2 h-4 w-4" />
                                            {{ consolidating ? '计算中...' : '重新计算余额' }}
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
