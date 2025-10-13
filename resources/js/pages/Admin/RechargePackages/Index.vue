<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Plus, Pencil, Trash2, ArrowUp, ArrowDown } from 'lucide-vue-next'

interface RechargePackage {
    id: number
    name: string
    amount: string
    bonus: string
    description: string | null
    sort_order: number
    is_active: boolean
    is_popular: boolean
    created_at: string
    updated_at: string
}

interface Props {
    packages: RechargePackage[]
}

const props = defineProps<Props>()

const deletePackage = (id: number) => {
    if (confirm('确定要删除这个充值套餐吗？')) {
        router.delete(`/admin/recharge-packages/${id}`)
    }
}

const getTotal = (amount: string, bonus: string) => {
    return (parseFloat(amount) + parseFloat(bonus)).toFixed(2)
}
</script>

<template>
    <AppLayout>
        <Head title="充值预设" />

        <div class="py-12">
            <div class="w-full sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-semibold">充值预设</h1>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    管理充值套餐的金额和赠送额度
                                </p>
                            </div>
                            <Button as-child class="bg-[#ff6e02] hover:bg-orange-600">
                                <Link href="/admin/recharge-packages/create">
                                    <Plus class="mr-2 h-4 w-4" />
                                    新增套餐
                                </Link>
                            </Button>
                        </div>

                        <!-- Packages Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <Card
                                v-for="pkg in packages"
                                :key="pkg.id"
                                :class="[
                                    'relative',
                                    pkg.is_active ? 'bg-[#374151] border-[#4B5563]' : 'bg-[#2d3748] border-[#4B5563] opacity-60'
                                ]"
                            >
                                <div v-if="pkg.is_popular" class="absolute top-2 right-2 bg-[#ff6e02] text-white text-xs px-2 py-1 rounded">
                                    推荐
                                </div>
                                <div v-if="!pkg.is_active" class="absolute top-2 left-2 bg-gray-600 text-white text-xs px-2 py-1 rounded">
                                    已停用
                                </div>

                                <CardHeader>
                                    <CardTitle class="text-white">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <div class="text-lg">{{ pkg.name }}</div>
                                                <div class="text-sm text-[#999999] mt-1">排序: {{ pkg.sort_order }}</div>
                                            </div>
                                        </div>
                                    </CardTitle>
                                </CardHeader>

                                <CardContent>
                                    <div class="space-y-3">
                                        <div>
                                            <div class="text-sm text-[#999999]">充值金额</div>
                                            <div class="text-2xl font-bold text-white">¥{{ pkg.amount }}</div>
                                        </div>
                                        <div v-if="parseFloat(pkg.bonus) > 0">
                                            <div class="text-sm text-[#999999]">赠送金币</div>
                                            <div class="text-xl font-semibold text-[#ff6e02]">+{{ pkg.bonus }}</div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-[#999999]">用户获得</div>
                                            <div class="text-xl font-semibold text-green-500">{{ getTotal(pkg.amount, pkg.bonus) }} 金币</div>
                                        </div>
                                        <div v-if="pkg.description" class="pt-2 border-t border-[#4B5563]">
                                            <div class="text-sm text-[#999999]">{{ pkg.description }}</div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex gap-2 pt-3">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                as-child
                                                class="flex-1 border-[#4B5563] bg-[#374151] text-white hover:bg-[#4B5563]"
                                            >
                                                <Link :href="`/admin/recharge-packages/${pkg.id}/edit`">
                                                    <Pencil class="mr-2 h-3 w-3" />
                                                    编辑
                                                </Link>
                                            </Button>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                @click="deletePackage(pkg.id)"
                                                class="border-red-600 bg-red-950 text-red-400 hover:bg-red-900"
                                            >
                                                <Trash2 class="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <div v-if="packages.length === 0" class="text-center py-12">
                            <p class="text-[#999999] mb-4">还没有充值套餐</p>
                            <Button as-child class="bg-[#ff6e02] hover:bg-orange-600">
                                <Link href="/admin/recharge-packages/create">
                                    <Plus class="mr-2 h-4 w-4" />
                                    创建第一个套餐
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
