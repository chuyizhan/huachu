<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import WebLayout from '@/layouts/WebLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Coins, Wallet, Zap, Check } from 'lucide-vue-next'

interface Package {
    id: number
    name: string
    amount: number
    bonus: number
    total: number
    description?: string
    is_popular: boolean
}

interface Props {
    packages: Package[]
    userCredits: number
}

const props = defineProps<Props>()

const selectedPackage = ref<number | null>(null)
const selectedPackageId = ref<number | null>(null)
const customAmount = ref<number | null>(null)
const paymentMethod = ref('fake')

const form = useForm({
    package_id: null as number | null,
    amount: 0,
    payment_method: 'fake',
})

const selectPackage = (pkg: Package) => {
    selectedPackage.value = pkg.id
    selectedPackageId.value = pkg.id
    customAmount.value = null
    form.package_id = pkg.id
    form.amount = pkg.amount
}

const useCustomAmount = () => {
    selectedPackage.value = null
    selectedPackageId.value = null
    form.package_id = null
    if (customAmount.value && customAmount.value > 0) {
        form.amount = customAmount.value
    }
}

const submitRecharge = () => {
    if (!form.amount || form.amount <= 0) {
        return
    }
    form.payment_method = paymentMethod.value
    form.post('/recharge/process')
}

const getPackageBonus = (bonus: number) => {
    if (bonus === 0) return ''
    return `+${bonus}赠送`
}
</script>

<template>
    <Head title="充值金币" />

    <WebLayout>
        <div class="py-12">
            <div class="w-full sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-8">
                            <h1 class="text-3xl font-bold text-white mb-2">充值金币</h1>
                            <p class="text-[#999999]">选择充值套餐或自定义金额</p>
                        </div>

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

                        <!-- Recharge Packages -->
                        <div class="mb-6">
                            <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
                                <Zap class="h-5 w-5 text-[#ff6e02]" />
                                推荐套餐
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <Card
                                    v-for="pkg in packages"
                                    :key="pkg.id"
                                    :class="[
                                        'cursor-pointer transition-all hover:shadow-lg relative',
                                        selectedPackage === pkg.id
                                            ? 'border-[#ff6e02] border-2 bg-orange-950'
                                            : 'bg-[#374151] border-[#4B5563]'
                                    ]"
                                    @click="selectPackage(pkg)"
                                >
                                    <div v-if="pkg.is_popular" class="absolute top-2 right-2 bg-[#ff6e02] text-white text-xs px-2 py-1 rounded">
                                        推荐
                                    </div>
                                    <CardHeader>
                                        <CardTitle class="flex items-center justify-between">
                                            <div>
                                                <div class="text-sm text-[#999999] mb-1">{{ pkg.name }}</div>
                                                <div class="text-2xl font-bold text-white">¥{{ pkg.amount }}</div>
                                            </div>
                                            <Check v-if="selectedPackage === pkg.id" class="h-6 w-6 text-[#ff6e02]" />
                                        </CardTitle>
                                        <CardDescription v-if="pkg.bonus > 0" class="text-[#ff6e02] font-semibold">
                                            {{ getPackageBonus(pkg.bonus) }}
                                        </CardDescription>
                                        <CardDescription v-if="pkg.description" class="text-[#999999] text-xs">
                                            {{ pkg.description }}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        <div class="flex items-center gap-2">
                                            <Coins class="h-5 w-5 text-[#ff6e02]" />
                                            <span class="text-xl font-semibold text-white">{{ pkg.total }} 金币</span>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>

                        <!-- Custom Amount -->
                        <Card class="bg-[#374151] border-[#4B5563] mb-6">
                            <CardHeader>
                                <CardTitle class="text-white">自定义金额</CardTitle>
                                <CardDescription class="text-[#999999]">输入您想要充值的金额</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="flex gap-4">
                                    <Input
                                        v-model.number="customAmount"
                                        type="number"
                                        placeholder="输入金额 (1-10000)"
                                        min="1"
                                        max="10000"
                                        class="bg-[#4B5563] border-[#6B7280] text-white"
                                        @input="useCustomAmount"
                                    />
                                    <span class="flex items-center text-white">元</span>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Payment Method -->
                        <Card class="bg-[#374151] border-[#4B5563] mb-6">
                            <CardHeader>
                                <CardTitle class="text-white">支付方式</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <RadioGroup v-model="paymentMethod" class="space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <RadioGroupItem id="fake" value="fake" />
                                        <Label for="fake" class="text-white cursor-pointer">模拟支付（测试）</Label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <RadioGroupItem id="alipay" value="alipay" disabled />
                                        <Label for="alipay" class="text-[#999999] cursor-not-allowed">支付宝（即将开放）</Label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <RadioGroupItem id="wechat" value="wechat" disabled />
                                        <Label for="wechat" class="text-[#999999] cursor-not-allowed">微信支付（即将开放）</Label>
                                    </div>
                                </RadioGroup>
                            </CardContent>
                        </Card>

                        <!-- Submit Button -->
                        <div class="flex justify-end gap-4">
                            <Button
                                variant="outline"
                                @click="$inertia.visit('/')"
                                class="border-[#4B5563] bg-[#374151] text-white hover:bg-[#4B5563]"
                            >
                                取消
                            </Button>
                            <Button
                                @click="submitRecharge"
                                :disabled="!form.amount || form.amount <= 0 || form.processing"
                                class="bg-[#ff6e02] hover:bg-orange-600 text-white"
                            >
                                <Coins class="mr-2 h-4 w-4" />
                                {{ form.processing ? '处理中...' : `充值 ¥${form.amount}` }}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </WebLayout>
</template>
