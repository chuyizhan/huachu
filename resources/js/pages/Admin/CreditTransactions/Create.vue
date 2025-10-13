<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import Textarea from '@/components/ui/Textarea.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { ArrowLeft } from 'lucide-vue-next'

interface User {
    id: number
    name: string
    login_name: string
    email: string
}

interface Props {
    users: User[]
}

const props = defineProps<Props>()

const form = useForm({
    user_id: '',
    type: 'earned',
    credits: 0,
    reason: '',
    metadata: {} as Record<string, any>,
})

const submit = () => {
    form.post('/admin/credit-transactions', {
        preserveScroll: true,
    })
}

const goBack = () => {
    router.visit('/admin/credit-transactions')
}
</script>

<template>
    <AppLayout>
        <Head title="创建金币交易" />

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
                                <h1 class="text-2xl font-semibold">创建金币交易</h1>
                            </div>
                        </div>

                        <Card>
                            <CardHeader>
                                <CardTitle>交易信息</CardTitle>
                                <CardDescription>
                                    手动创建金币交易，为用户添加或扣除金币
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form @submit.prevent="submit" class="space-y-6">
                                    <!-- User Selection -->
                                    <div class="space-y-2">
                                        <Label for="user_id">用户 *</Label>
                                        <select
                                            id="user_id"
                                            v-model="form.user_id"
                                            required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{ 'border-red-500': form.errors.user_id }"
                                        >
                                            <option value="">选择用户</option>
                                            <option v-for="user in users" :key="user.id" :value="user.id">
                                                {{ user.name }} (@{{ user.login_name }}) - {{ user.email }}
                                            </option>
                                        </select>
                                        <p v-if="form.errors.user_id" class="text-sm text-red-500">
                                            {{ form.errors.user_id }}
                                        </p>
                                    </div>

                                    <!-- Transaction Type -->
                                    <div class="space-y-2">
                                        <Label for="type">交易类型 *</Label>
                                        <select
                                            id="type"
                                            v-model="form.type"
                                            required
                                            class="w-full px-4 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{ 'border-red-500': form.errors.type }"
                                        >
                                            <option value="earned">获得 (增加金币)</option>
                                            <option value="spent">消费 (使用金币)</option>
                                            <option value="deducted">扣除 (管理员扣除)</option>
                                            <option value="refunded">退款 (退还金币)</option>
                                        </select>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            获得会增加用户金币，消费和扣除会减少用户金币
                                        </p>
                                        <p v-if="form.errors.type" class="text-sm text-red-500">
                                            {{ form.errors.type }}
                                        </p>
                                    </div>

                                    <!-- Credits -->
                                    <div class="space-y-2">
                                        <Label for="credits">金币数量 *</Label>
                                        <Input
                                            id="credits"
                                            v-model.number="form.credits"
                                            type="number"
                                            required
                                            placeholder="输入金币数量（正数）"
                                            :class="{ 'border-red-500': form.errors.credits }"
                                        />
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            请输入正数，系统会根据交易类型自动处理正负号
                                        </p>
                                        <p v-if="form.errors.credits" class="text-sm text-red-500">
                                            {{ form.errors.credits }}
                                        </p>
                                    </div>

                                    <!-- Reason -->
                                    <div class="space-y-2">
                                        <Label for="reason">原因 *</Label>
                                        <Input
                                            id="reason"
                                            v-model="form.reason"
                                            type="text"
                                            required
                                            placeholder="例如：manual_adjustment, daily_bonus, admin_reward"
                                            :class="{ 'border-red-500': form.errors.reason }"
                                        />
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            简短描述此交易的原因（建议使用英文下划线格式）
                                        </p>
                                        <p v-if="form.errors.reason" class="text-sm text-red-500">
                                            {{ form.errors.reason }}
                                        </p>
                                    </div>

                                    <!-- Common Reasons -->
                                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                        <h4 class="text-sm font-medium mb-2 text-blue-900 dark:text-blue-100">常用原因建议</h4>
                                        <div class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                                            <p><strong>获得：</strong> daily_login, post_created, comment_created, manual_bonus, referral_reward</p>
                                            <p><strong>消费：</strong> post_purchase, premium_upgrade, gift_sent</p>
                                            <p><strong>扣除：</strong> violation_penalty, spam_deduction, manual_adjustment</p>
                                        </div>
                                    </div>

                                    <!-- Preview -->
                                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                        <h3 class="text-sm font-medium mb-2">预览</h3>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                            <p v-if="form.user_id">
                                                用户：<span class="font-semibold">{{ users.find(u => u.id === parseInt(form.user_id))?.name }}</span>
                                            </p>
                                            <p>
                                                类型：<span
                                                    class="font-semibold"
                                                    :class="{
                                                        'text-green-600': form.type === 'earned',
                                                        'text-blue-600': form.type === 'spent',
                                                        'text-red-600': form.type === 'deducted',
                                                    }"
                                                >
                                                    {{ form.type === 'earned' ? '获得' : form.type === 'spent' ? '消费' : '扣除' }}
                                                </span>
                                            </p>
                                            <p>
                                                金币变化：
                                                <span
                                                    class="font-semibold text-lg"
                                                    :class="{
                                                        'text-green-600': form.type === 'earned',
                                                        'text-red-600': form.type === 'spent' || form.type === 'deducted',
                                                    }"
                                                >
                                                    {{ form.type === 'earned' ? '+' : '-' }}{{ Math.abs(form.credits) }}
                                                </span>
                                            </p>
                                            <p v-if="form.reason">原因：<span class="font-semibold">{{ form.reason }}</span></p>
                                        </div>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="flex gap-4">
                                        <Button
                                            type="submit"
                                            :disabled="form.processing"
                                        >
                                            {{ form.processing ? '创建中...' : '创建交易' }}
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
