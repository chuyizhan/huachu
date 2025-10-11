<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { ArrowLeft, Edit, Trash2, UserCheck, UserX, Shield, Crown, BadgeCheck } from 'lucide-vue-next'

interface User {
    id: number
    name: string
    email: string
    login_name: string
    avatar: string | null
    description: string | null
    sex: number | null
    date_birth: string | null
    phone: string | null
    phone_verified_at: string | null
    is_admin: boolean
    is_creator: boolean
    is_verified: boolean
    is_top_creator: boolean
    type: number
    status: number
    xp: number
    points: number
    credits: number
    balance: number
    followers_count: number
    following_count: number
    last_login_at: string | null
    last_login_ip: string | null
    last_login_user_agent: string | null
    email_verified_at: string | null
    created_at: string
    updated_at: string
    posts_count: number
    favorites_count: number
    creator_profile?: {
        id: number
        bio: string | null
        social_links: any
    }
}

interface Props {
    user: User
}

const props = defineProps<Props>()

const goBack = () => {
    router.visit('/admin/users')
}

const deleteUser = () => {
    if (confirm(`确定要删除用户 ${props.user.name} 吗？此操作无法撤销。`)) {
        router.delete(`/admin/users/${props.user.id}`, {
            onSuccess: () => {
                router.visit('/admin/users')
            },
        })
    }
}

const formatDate = (dateString: string | null) => {
    if (!dateString) return '未设置'
    return new Date(dateString).toLocaleString('zh-CN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const formatDateOnly = (dateString: string | null) => {
    if (!dateString) return '未设置'
    return new Date(dateString).toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    })
}

const getUserType = (type: number) => {
    const types: Record<number, string> = {
        1: '普通用户',
        2: '高级用户',
    }
    return types[type] || '未知'
}

const getUserStatus = (status: number) => {
    const statuses: Record<number, string> = {
        1: '正常',
        2: '暂停',
        3: '禁止',
    }
    return statuses[status] || '未知'
}

const getSex = (sex: number | null) => {
    if (sex === null) return '未设置'
    const sexes: Record<number, string> = {
        0: '未知',
        1: '男',
        2: '女',
    }
    return sexes[sex] || '未设置'
}
</script>

<template>
    <AppLayout>
        <Head :title="`用户详情 - ${user.name}`" />

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
                                <h1 class="text-2xl font-semibold">用户详情</h1>
                            </div>
                            <div class="flex gap-2">
                                <Link :href="`/admin/users/${user.id}/edit`">
                                    <Button variant="outline">
                                        <Edit class="mr-2 h-4 w-4" />
                                        编辑
                                    </Button>
                                </Link>
                                <Button
                                    v-if="!user.is_admin"
                                    variant="destructive"
                                    @click="deleteUser"
                                >
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    删除
                                </Button>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <!-- Basic Info -->
                            <Card>
                                <CardHeader>
                                    <CardTitle>基本信息</CardTitle>
                                    <CardDescription>用户的基本资料</CardDescription>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div v-if="user.avatar" class="flex justify-center">
                                        <img
                                            :src="user.avatar"
                                            :alt="user.name"
                                            class="w-24 h-24 rounded-full object-cover"
                                        />
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">用户ID</div>
                                        <div class="mt-1">{{ user.id }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">姓名</div>
                                        <div class="mt-1 flex items-center gap-2">
                                            {{ user.name }}
                                            <Shield v-if="user.is_admin" class="h-4 w-4 text-red-500" title="管理员" />
                                            <Crown v-if="user.is_top_creator" class="h-4 w-4 text-yellow-500" title="顶级创作者" />
                                            <BadgeCheck v-if="user.is_verified" class="h-4 w-4 text-blue-500" title="已认证" />
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">用户名</div>
                                        <div class="mt-1">{{ user.login_name }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">邮箱</div>
                                        <div class="mt-1">{{ user.email }}</div>
                                        <div v-if="user.email_verified_at" class="text-xs text-green-600 dark:text-green-400">
                                            已验证
                                        </div>
                                        <div v-else class="text-xs text-gray-500 dark:text-gray-400">
                                            未验证
                                        </div>
                                    </div>
                                    <div v-if="user.phone">
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">手机号</div>
                                        <div class="mt-1">{{ user.phone }}</div>
                                        <div v-if="user.phone_verified_at" class="text-xs text-green-600 dark:text-green-400">
                                            已验证
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">性别</div>
                                        <div class="mt-1">{{ getSex(user.sex) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">出生日期</div>
                                        <div class="mt-1">{{ formatDateOnly(user.date_birth) }}</div>
                                    </div>
                                    <div v-if="user.description">
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">个人简介</div>
                                        <div class="mt-1 text-sm">{{ user.description }}</div>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Account Status -->
                            <Card>
                                <CardHeader>
                                    <CardTitle>账户状态</CardTitle>
                                    <CardDescription>用户的权限和状态信息</CardDescription>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">用户类型</div>
                                        <div class="mt-1">{{ getUserType(user.type) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">账户状态</div>
                                        <div class="mt-1">
                                            <span
                                                v-if="user.status === 1"
                                                class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-200"
                                            >
                                                {{ getUserStatus(user.status) }}
                                            </span>
                                            <span
                                                v-else
                                                class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-200"
                                            >
                                                {{ getUserStatus(user.status) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <UserCheck v-if="user.is_admin" class="h-5 w-5 text-red-500" />
                                        <UserX v-else class="h-5 w-5 text-gray-400" />
                                        <div>
                                            <div class="text-sm font-medium">管理员</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ user.is_admin ? '是' : '否' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <UserCheck v-if="user.is_creator" class="h-5 w-5 text-blue-500" />
                                        <UserX v-else class="h-5 w-5 text-gray-400" />
                                        <div>
                                            <div class="text-sm font-medium">创作者</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ user.is_creator ? '是' : '否' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <BadgeCheck v-if="user.is_verified" class="h-5 w-5 text-blue-500" />
                                        <UserX v-else class="h-5 w-5 text-gray-400" />
                                        <div>
                                            <div class="text-sm font-medium">已认证</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ user.is_verified ? '是' : '否' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Crown v-if="user.is_top_creator" class="h-5 w-5 text-yellow-500" />
                                        <UserX v-else class="h-5 w-5 text-gray-400" />
                                        <div>
                                            <div class="text-sm font-medium">顶级创作者</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ user.is_top_creator ? '是' : '否' }}
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Statistics -->
                            <Card>
                                <CardHeader>
                                    <CardTitle>数据统计</CardTitle>
                                    <CardDescription>用户的活动数据</CardDescription>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">粉丝数</div>
                                            <div class="mt-1 text-2xl font-bold">{{ user.followers_count }}</div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">关注数</div>
                                            <div class="mt-1 text-2xl font-bold">{{ user.following_count }}</div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">帖子数</div>
                                            <div class="mt-1 text-2xl font-bold">{{ user.posts_count }}</div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">收藏数</div>
                                            <div class="mt-1 text-2xl font-bold">{{ user.favorites_count }}</div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">经验值</div>
                                        <div class="mt-1 text-xl font-bold">{{ user.xp }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">积分</div>
                                        <div class="mt-1 text-xl font-bold">{{ user.points }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">积分余额</div>
                                        <div class="mt-1 text-xl font-bold">{{ user.credits }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">账户余额</div>
                                        <div class="mt-1 text-xl font-bold">¥{{ user.balance }}</div>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Login Info -->
                            <Card>
                                <CardHeader>
                                    <CardTitle>登录信息</CardTitle>
                                    <CardDescription>最近的登录记录</CardDescription>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">最后登录时间</div>
                                        <div class="mt-1">{{ formatDate(user.last_login_at) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">最后登录IP</div>
                                        <div class="mt-1">{{ user.last_login_ip || '未记录' }}</div>
                                    </div>
                                    <div v-if="user.last_login_user_agent">
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">用户代理</div>
                                        <div class="mt-1 text-sm break-all">{{ user.last_login_user_agent }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">注册时间</div>
                                        <div class="mt-1">{{ formatDate(user.created_at) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">最后更新</div>
                                        <div class="mt-1">{{ formatDate(user.updated_at) }}</div>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Creator Profile (if applicable) -->
                            <Card v-if="user.is_creator && user.creator_profile" class="md:col-span-2">
                                <CardHeader>
                                    <CardTitle>创作者信息</CardTitle>
                                    <CardDescription>创作者的详细资料</CardDescription>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div v-if="user.creator_profile.bio">
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">个人简介</div>
                                        <div class="mt-1">{{ user.creator_profile.bio }}</div>
                                    </div>
                                    <div v-if="user.creator_profile.social_links">
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">社交链接</div>
                                        <div class="mt-1 text-sm">
                                            <pre class="p-2 bg-gray-100 dark:bg-gray-900 rounded overflow-x-auto">{{ JSON.stringify(user.creator_profile.social_links, null, 2) }}</pre>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
