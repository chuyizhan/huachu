<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import WebLayout from '@/layouts/WebLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { CheckCircle2, Wallet, ArrowRight } from 'lucide-vue-next'

interface Order {
    id: number
    order_number: string
    amount: string
    status: string
    payment_method: string
    paid_at: string
    created_at: string
}

interface Props {
    order: Order
    userCredits: number
}

const props = defineProps<Props>()

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('zh-CN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    })
}
</script>

<template>
    <Head title="充值成功" />

    <WebLayout>
        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Success Icon -->
                        <div class="flex flex-col items-center mb-8">
                            <CheckCircle2 class="h-20 w-20 text-green-500 mb-4" />
                            <h1 class="text-3xl font-bold text-white mb-2">充值成功！</h1>
                            <p class="text-[#999999]">您的金币已到账</p>
                        </div>

                        <!-- Order Details -->
                        <Card class="bg-[#374151] border-[#4B5563] mb-6">
                            <CardHeader>
                                <CardTitle class="text-white">订单详情</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-[#999999]">订单号</span>
                                    <span class="text-white font-mono">{{ order.order_number }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[#999999]">充值金额</span>
                                    <span class="text-2xl font-bold text-[#ff6e02]">¥{{ order.amount }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[#999999]">支付方式</span>
                                    <span class="text-white">
                                        {{ order.payment_method === 'fake' ? '模拟支付' : order.payment_method }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[#999999]">支付时间</span>
                                    <span class="text-white">{{ formatDate(order.paid_at || order.created_at) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-[#999999]">订单状态</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-200">
                                        已完成
                                    </span>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Current Balance -->
                        <Card class="bg-gradient-to-r from-[#ff6e02] to-orange-600 border-0 mb-6">
                            <CardHeader>
                                <CardTitle class="text-white flex items-center gap-2">
                                    <Wallet class="h-5 w-5" />
                                    当前余额
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="text-4xl font-bold text-white">
                                    {{ userCredits.toFixed(2) }} 金币
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Action Buttons -->
                        <div class="flex gap-4">
                            <Button
                                variant="outline"
                                as-child
                                class="flex-1 border-[#4B5563] bg-[#374151] text-white hover:bg-[#4B5563]"
                            >
                                <Link href="/recharge">
                                    继续充值
                                </Link>
                            </Button>
                            <Button
                                as-child
                                class="flex-1 bg-[#ff6e02] hover:bg-orange-600 text-white"
                            >
                                <Link href="/">
                                    返回首页
                                    <ArrowRight class="ml-2 h-4 w-4" />
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </WebLayout>
</template>
