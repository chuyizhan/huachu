<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import Textarea from '@/components/ui/Textarea.vue'
import { Switch } from '@/components/ui/switch'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

interface RechargePackage {
    id: number
    name: string
    amount: string
    bonus: string
    description: string | null
    sort_order: number
    is_active: boolean
    is_popular: boolean
}

interface Props {
    package: RechargePackage
}

const props = defineProps<Props>()

const form = useForm({
    name: props.package.name,
    amount: props.package.amount,
    bonus: props.package.bonus,
    description: props.package.description || '',
    sort_order: props.package.sort_order,
    is_active: props.package.is_active,
    is_popular: props.package.is_popular,
})

const submit = () => {
    form.put(`/admin/recharge-packages/${props.package.id}`)
}
</script>

<template>
    <AppLayout>
        <Head title="编辑充值套餐" />

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <Card class="bg-[#374151] border-[#4B5563]">
                    <CardHeader>
                        <CardTitle class="text-white text-2xl">编辑充值套餐</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Name -->
                            <div>
                                <Label for="name" class="text-white">套餐名称</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="mt-1 bg-[#4B5563] border-[#6B7280] text-white"
                                    placeholder="例如: 超值套餐"
                                />
                                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                            </div>

                            <!-- Amount -->
                            <div>
                                <Label for="amount" class="text-white">充值金额 (¥)</Label>
                                <Input
                                    id="amount"
                                    v-model="form.amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    required
                                    class="mt-1 bg-[#4B5563] border-[#6B7280] text-white"
                                    placeholder="用户需要支付的金额"
                                />
                                <div v-if="form.errors.amount" class="text-red-500 text-sm mt-1">{{ form.errors.amount }}</div>
                            </div>

                            <!-- Bonus -->
                            <div>
                                <Label for="bonus" class="text-white">赠送金币</Label>
                                <Input
                                    id="bonus"
                                    v-model="form.bonus"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    required
                                    class="mt-1 bg-[#4B5563] border-[#6B7280] text-white"
                                    placeholder="额外赠送的金币数量"
                                />
                                <div v-if="form.errors.bonus" class="text-red-500 text-sm mt-1">{{ form.errors.bonus }}</div>
                                <p class="text-sm text-[#999999] mt-1">
                                    用户将获得: {{ (parseFloat(form.amount || 0) + parseFloat(form.bonus || 0)).toFixed(2) }} 金币
                                </p>
                            </div>

                            <!-- Description -->
                            <div>
                                <Label for="description" class="text-white">描述</Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    class="mt-1 bg-[#4B5563] border-[#6B7280] text-white"
                                    placeholder="套餐描述（可选）"
                                    rows="3"
                                />
                                <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
                            </div>

                            <!-- Sort Order -->
                            <div>
                                <Label for="sort_order" class="text-white">排序</Label>
                                <Input
                                    id="sort_order"
                                    v-model.number="form.sort_order"
                                    type="number"
                                    min="0"
                                    required
                                    class="mt-1 bg-[#4B5563] border-[#6B7280] text-white"
                                />
                                <p class="text-sm text-[#999999] mt-1">数字越小排序越靠前</p>
                                <div v-if="form.errors.sort_order" class="text-red-500 text-sm mt-1">{{ form.errors.sort_order }}</div>
                            </div>

                            <!-- Is Active -->
                            <div class="flex items-center space-x-2">
                                <Switch id="is_active" v-model:checked="form.is_active" />
                                <Label for="is_active" class="text-white">启用套餐</Label>
                            </div>

                            <!-- Is Popular -->
                            <div class="flex items-center space-x-2">
                                <Switch id="is_popular" v-model:checked="form.is_popular" />
                                <Label for="is_popular" class="text-white">标记为推荐</Label>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-4 pt-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="$inertia.visit('/admin/recharge-packages')"
                                    class="flex-1 border-[#4B5563] bg-[#374151] text-white hover:bg-[#4B5563]"
                                >
                                    取消
                                </Button>
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex-1 bg-[#ff6e02] hover:bg-orange-600 text-white"
                                >
                                    {{ form.processing ? '更新中...' : '更新套餐' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
