<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Users, FolderTree, FileText, Coins, DollarSign, ShieldAlert, Package, TrendingUp, ShoppingCart, Wallet } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: '管理后台',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="管理后台" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">管理后台</h1>
                <p class="text-[#999999]">欢迎来到管理控制面板</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <!-- User Management Card -->
                <Link href="/admin/users" class="block">
                    <Card class="bg-[#374151] border-[#4B5563] hover:border-[#ff6e02] transition-colors cursor-pointer h-full">
                        <CardHeader>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-3 bg-[#ff6e02] rounded-lg">
                                    <Users class="w-6 h-6 text-white" />
                                </div>
                                <CardTitle class="text-white text-xl">用户管理</CardTitle>
                            </div>
                            <CardDescription class="text-[#999999]">
                                管理所有用户账户，查看用户信息，编辑用户权限
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="text-[#ff6e02] text-sm font-medium">
                                点击进入 →
                            </div>
                        </CardContent>
                    </Card>
                </Link>

                <!-- Category Management Card -->
                <Link href="/admin/categories" class="block">
                    <Card class="bg-[#374151] border-[#4B5563] hover:border-[#ff6e02] transition-colors cursor-pointer h-full">
                        <CardHeader>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-3 bg-[#ff6e02] rounded-lg">
                                    <FolderTree class="w-6 h-6 text-white" />
                                </div>
                                <CardTitle class="text-white text-xl">分类管理</CardTitle>
                            </div>
                            <CardDescription class="text-[#999999]">
                                管理所有分类，查看分类信息，编辑分类设置
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="text-[#ff6e02] text-sm font-medium">
                                点击进入 →
                            </div>
                        </CardContent>
                    </Card>
                </Link>

                <!-- Post Management Card -->
                <Link href="/admin/posts" class="block">
                    <Card class="bg-[#374151] border-[#4B5563] hover:border-[#ff6e02] transition-colors cursor-pointer h-full">
                        <CardHeader>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-3 bg-[#ff6e02] rounded-lg">
                                    <FileText class="w-6 h-6 text-white" />
                                </div>
                                <CardTitle class="text-white text-xl">帖子管理</CardTitle>
                            </div>
                            <CardDescription class="text-[#999999]">
                                管理所有帖子，查看帖子信息，编辑帖子内容
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="text-[#ff6e02] text-sm font-medium">
                                点击进入 →
                            </div>
                        </CardContent>
                    </Card>
                </Link>

                <!-- Point Transactions Management Card -->
                <Link href="/admin/point-transactions" class="block">
                    <Card class="bg-[#374151] border-[#4B5563] hover:border-[#ff6e02] transition-colors cursor-pointer h-full">
                        <CardHeader>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-3 bg-[#ff6e02] rounded-lg">
                                    <Coins class="w-6 h-6 text-white" />
                                </div>
                                <CardTitle class="text-white text-xl">积分交易</CardTitle>
                            </div>
                            <CardDescription class="text-[#999999]">
                                管理积分交易，查看用户积分获得和消费记录
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="text-[#ff6e02] text-sm font-medium">
                                点击进入 →
                            </div>
                        </CardContent>
                    </Card>
                </Link>

                <!-- Credit Transactions Management Card -->
                <Link href="/admin/credit-transactions" class="block">
                    <Card class="bg-[#374151] border-[#4B5563] hover:border-[#ff6e02] transition-colors cursor-pointer h-full">
                        <CardHeader>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-3 bg-[#ff6e02] rounded-lg">
                                    <DollarSign class="w-6 h-6 text-white" />
                                </div>
                                <CardTitle class="text-white text-xl">金币交易</CardTitle>
                            </div>
                            <CardDescription class="text-[#999999]">
                                管理金币交易，查看用户金币获得和消费记录
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="text-[#ff6e02] text-sm font-medium">
                                点击进入 →
                            </div>
                        </CardContent>
                    </Card>
                </Link>

                <!-- Plans Management Card -->
                <Link href="/admin/plans" class="block">
                    <Card class="bg-[#374151] border-[#4B5563] hover:border-[#ff6e02] transition-colors cursor-pointer h-full">
                        <CardHeader>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-3 bg-[#ff6e02] rounded-lg">
                                    <Package class="w-6 h-6 text-white" />
                                </div>
                                <CardTitle class="text-white text-xl">套餐管理</CardTitle>
                            </div>
                            <CardDescription class="text-[#999999]">
                                管理会员套餐，设置价格、权限和功能
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="text-[#ff6e02] text-sm font-medium">
                                点击进入 →
                            </div>
                        </CardContent>
                    </Card>
                </Link>

                <!-- Platform Earnings Card -->
                <Link href="/admin/platform-transactions" class="block">
                    <Card class="bg-[#374151] border-[#4B5563] hover:border-[#ff6e02] transition-colors cursor-pointer h-full">
                        <CardHeader>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-3 bg-[#ff6e02] rounded-lg">
                                    <TrendingUp class="w-6 h-6 text-white" />
                                </div>
                                <CardTitle class="text-white text-xl">平台收益</CardTitle>
                            </div>
                            <CardDescription class="text-[#999999]">
                                查看平台佣金收益和交易统计
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="text-[#ff6e02] text-sm font-medium">
                                点击进入 →
                            </div>
                        </CardContent>
                    </Card>
                </Link>

                <!-- Orders Management Card -->
                <Link href="/admin/orders" class="block">
                    <Card class="bg-[#374151] border-[#4B5563] hover:border-[#ff6e02] transition-colors cursor-pointer h-full">
                        <CardHeader>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-3 bg-[#ff6e02] rounded-lg">
                                    <ShoppingCart class="w-6 h-6 text-white" />
                                </div>
                                <CardTitle class="text-white text-xl">订单管理</CardTitle>
                            </div>
                            <CardDescription class="text-[#999999]">
                                查看和管理用户充值订单和交易记录
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="text-[#ff6e02] text-sm font-medium">
                                点击进入 →
                            </div>
                        </CardContent>
                    </Card>
                </Link>

                <!-- Recharge Packages Card -->
                <Link href="/admin/recharge-packages" class="block">
                    <Card class="bg-[#374151] border-[#4B5563] hover:border-[#ff6e02] transition-colors cursor-pointer h-full">
                        <CardHeader>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-3 bg-[#ff6e02] rounded-lg">
                                    <Wallet class="w-6 h-6 text-white" />
                                </div>
                                <CardTitle class="text-white text-xl">充值预设</CardTitle>
                            </div>
                            <CardDescription class="text-[#999999]">
                                管理充值套餐金额和赠送额度设置
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="text-[#ff6e02] text-sm font-medium">
                                点击进入 →
                            </div>
                        </CardContent>
                    </Card>
                </Link>

                <!-- More admin modules can be added here -->
                <Card class="bg-[#374151] border-[#4B5563] opacity-50">
                    <CardHeader>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-3 bg-[#4B5563] rounded-lg">
                                <ShieldAlert class="w-6 h-6 text-white" />
                            </div>
                            <CardTitle class="text-white text-xl">其他功能</CardTitle>
                        </div>
                        <CardDescription class="text-[#999999]">
                            即将推出更多管理功能
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-[#999999] text-sm">
                            敬请期待
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
