<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Users, FolderTree, FileText, ShieldAlert } from 'lucide-vue-next';

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
