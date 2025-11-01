<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Check, X, Star, StarOff, Trash2, Eye, Heart, FileText, Users, DollarSign } from 'lucide-vue-next';

interface CreatorProfile {
    id: number;
    display_name: string;
    bio: string | null;
    specialty: string;
    experience_years: number;
    location: string | null;
    website: string | null;
    portfolio_url: string | null;
    verification_status: string;
    verified_at: string | null;
    verification_notes: string | null;
    is_featured: boolean;
    follower_count: number;
    rating: number;
    review_count: number;
}

interface Creator {
    id: number;
    name: string;
    email: string;
    is_creator: boolean;
    is_top_creator: boolean;
    creator_profile: CreatorProfile;
    created_at: string;
}

interface Stats {
    total_posts: number;
    total_views: number;
    total_likes: number;
    total_followers: number;
    total_revenue: number;
}

interface Props {
    creator: Creator;
    stats: Stats;
}

const props = defineProps<Props>();

const editMode = ref(false);
const verificationNotes = ref('');

const form = useForm({
    is_top_creator: props.creator.is_top_creator,
    profile: {
        display_name: props.creator.creator_profile?.display_name || '',
        bio: props.creator.creator_profile?.bio || '',
        specialty: props.creator.creator_profile?.specialty || '',
        experience_years: props.creator.creator_profile?.experience_years || 0,
        location: props.creator.creator_profile?.location || '',
        website: props.creator.creator_profile?.website || '',
        portfolio_url: props.creator.creator_profile?.portfolio_url || '',
        verification_status: props.creator.creator_profile?.verification_status || 'pending',
        verification_notes: props.creator.creator_profile?.verification_notes || '',
        is_featured: props.creator.creator_profile?.is_featured || false,
    }
});

const saveChanges = () => {
    form.put(`/admin/creators/${props.creator.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editMode.value = false;
        }
    });
};

const cancelEdit = () => {
    editMode.value = false;
    form.reset();
};

const verifyCreator = () => {
    if (confirm('确定要验证此创作者吗？')) {
        router.post(`/admin/creators/${props.creator.id}/verify`, {
            verification_notes: verificationNotes.value
        }, {
            preserveScroll: true,
        });
    }
};

const rejectCreator = () => {
    const notes = prompt('请输入拒绝原因：');
    if (notes) {
        router.post(`/admin/creators/${props.creator.id}/reject`, {
            verification_notes: notes
        }, {
            preserveScroll: true,
        });
    }
};

const toggleFeatured = () => {
    router.post(`/admin/creators/${props.creator.id}/toggle-featured`, {}, {
        preserveScroll: true,
    });
};

const removeCreatorStatus = () => {
    if (confirm('确定要移除此用户的创作者身份吗？这将软删除其创作者资料。')) {
        router.delete(`/admin/creators/${props.creator.id}`, {
            onSuccess: () => {
                router.visit('/admin/creators');
            }
        });
    }
};

const getStatusBadgeClass = (status: string) => {
    const statusMap = {
        'pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        'verified': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'rejected': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return statusMap[status as keyof typeof statusMap] || 'bg-gray-100 text-gray-800';
};

const formatDate = (dateString: string | null) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('zh-CN', {
        style: 'currency',
        currency: 'CNY'
    }).format(amount);
};
</script>

<template>
    <AppLayout>
        <Head :title="`创作者详情 - ${creator.creator_profile?.display_name || creator.name}`" />

        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center gap-4 mb-4">
                    <Button
                        variant="ghost"
                        @click="router.visit('/admin/creators')"
                        class="flex items-center gap-2"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        返回列表
                    </Button>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ creator.creator_profile?.display_name || creator.name }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ creator.email }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Badge
                            :class="getStatusBadgeClass(creator.creator_profile?.verification_status)"
                            class="text-sm"
                        >
                            {{ creator.creator_profile?.verification_status === 'pending' ? '待审核' : '' }}
                            {{ creator.creator_profile?.verification_status === 'verified' ? '已验证' : '' }}
                            {{ creator.creator_profile?.verification_status === 'rejected' ? '已拒绝' : '' }}
                        </Badge>
                        <Badge v-if="creator.is_top_creator" class="bg-purple-100 text-purple-800">
                            顶级创作者
                        </Badge>
                        <Badge v-if="creator.creator_profile?.is_featured" class="bg-blue-100 text-blue-800">
                            精选
                        </Badge>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            帖子总数
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-2">
                            <FileText class="h-4 w-4 text-gray-400" />
                            <p class="text-2xl font-bold">{{ stats.total_posts }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            总浏览量
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-2">
                            <Eye class="h-4 w-4 text-gray-400" />
                            <p class="text-2xl font-bold">{{ stats.total_views.toLocaleString() }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            总点赞数
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-2">
                            <Heart class="h-4 w-4 text-gray-400" />
                            <p class="text-2xl font-bold">{{ stats.total_likes.toLocaleString() }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            粉丝数
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-2">
                            <Users class="h-4 w-4 text-gray-400" />
                            <p class="text-2xl font-bold">{{ stats.total_followers.toLocaleString() }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            总收入
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-2">
                            <DollarSign class="h-4 w-4 text-gray-400" />
                            <p class="text-2xl font-bold">{{ formatCurrency(stats.total_revenue) }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Creator Profile -->
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle>创作者资料</CardTitle>
                                <div class="flex gap-2">
                                    <Button
                                        v-if="!editMode"
                                        @click="editMode = true"
                                        variant="outline"
                                        size="sm"
                                    >
                                        编辑
                                    </Button>
                                    <template v-else>
                                        <Button
                                            @click="saveChanges"
                                            :disabled="form.processing"
                                            size="sm"
                                        >
                                            保存
                                        </Button>
                                        <Button
                                            @click="cancelEdit"
                                            variant="outline"
                                            size="sm"
                                        >
                                            取消
                                        </Button>
                                    </template>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Display Name -->
                            <div>
                                <Label>显示名称</Label>
                                <Input
                                    v-if="editMode"
                                    v-model="form.profile.display_name"
                                    class="mt-1"
                                />
                                <p v-else class="mt-1 text-sm">{{ creator.creator_profile?.display_name }}</p>
                            </div>

                            <!-- Bio -->
                            <div>
                                <Label>简介</Label>
                                <Textarea
                                    v-if="editMode"
                                    v-model="form.profile.bio"
                                    rows="3"
                                    class="mt-1"
                                />
                                <p v-else class="mt-1 text-sm">{{ creator.creator_profile?.bio || 'N/A' }}</p>
                            </div>

                            <!-- Specialty -->
                            <div>
                                <Label>专长</Label>
                                <Input
                                    v-if="editMode"
                                    v-model="form.profile.specialty"
                                    class="mt-1"
                                />
                                <p v-else class="mt-1 text-sm">{{ creator.creator_profile?.specialty }}</p>
                            </div>

                            <!-- Experience Years -->
                            <div>
                                <Label>经验年限</Label>
                                <Input
                                    v-if="editMode"
                                    v-model.number="form.profile.experience_years"
                                    type="number"
                                    min="0"
                                    class="mt-1"
                                />
                                <p v-else class="mt-1 text-sm">{{ creator.creator_profile?.experience_years }} 年</p>
                            </div>

                            <!-- Location -->
                            <div>
                                <Label>位置</Label>
                                <Input
                                    v-if="editMode"
                                    v-model="form.profile.location"
                                    class="mt-1"
                                />
                                <p v-else class="mt-1 text-sm">{{ creator.creator_profile?.location || 'N/A' }}</p>
                            </div>

                            <!-- Website -->
                            <div>
                                <Label>网站</Label>
                                <Input
                                    v-if="editMode"
                                    v-model="form.profile.website"
                                    type="url"
                                    class="mt-1"
                                />
                                <a
                                    v-else-if="creator.creator_profile?.website"
                                    :href="creator.creator_profile.website"
                                    target="_blank"
                                    class="mt-1 text-sm text-blue-600 hover:underline"
                                >
                                    {{ creator.creator_profile.website }}
                                </a>
                                <p v-else class="mt-1 text-sm">N/A</p>
                            </div>

                            <!-- Portfolio URL -->
                            <div>
                                <Label>作品集</Label>
                                <Input
                                    v-if="editMode"
                                    v-model="form.profile.portfolio_url"
                                    type="url"
                                    class="mt-1"
                                />
                                <a
                                    v-else-if="creator.creator_profile?.portfolio_url"
                                    :href="creator.creator_profile.portfolio_url"
                                    target="_blank"
                                    class="mt-1 text-sm text-blue-600 hover:underline"
                                >
                                    {{ creator.creator_profile.portfolio_url }}
                                </a>
                                <p v-else class="mt-1 text-sm">N/A</p>
                            </div>

                            <!-- Top Creator Toggle -->
                            <div class="flex items-center gap-2">
                                <input
                                    v-if="editMode"
                                    type="checkbox"
                                    v-model="form.is_top_creator"
                                    id="is_top_creator"
                                    class="rounded"
                                />
                                <Label :for="editMode ? 'is_top_creator' : undefined">
                                    顶级创作者
                                </Label>
                                <span v-if="!editMode" class="text-sm">
                                    {{ creator.is_top_creator ? '是' : '否' }}
                                </span>
                            </div>

                            <!-- Featured Toggle -->
                            <div class="flex items-center gap-2">
                                <input
                                    v-if="editMode"
                                    type="checkbox"
                                    v-model="form.profile.is_featured"
                                    id="is_featured"
                                    class="rounded"
                                />
                                <Label :for="editMode ? 'is_featured' : undefined">
                                    精选创作者
                                </Label>
                                <span v-if="!editMode" class="text-sm">
                                    {{ creator.creator_profile?.is_featured ? '是' : '否' }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Actions & Info -->
                <div class="space-y-6">
                    <!-- Verification Actions -->
                    <Card v-if="creator.creator_profile?.verification_status === 'pending'">
                        <CardHeader>
                            <CardTitle>验证操作</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label>验证备注</Label>
                                <Textarea
                                    v-model="verificationNotes"
                                    rows="3"
                                    placeholder="添加验证备注..."
                                    class="mt-1"
                                />
                            </div>
                            <div class="flex flex-col gap-2">
                                <Button
                                    @click="verifyCreator"
                                    class="w-full bg-green-600 hover:bg-green-700"
                                >
                                    <Check class="h-4 w-4 mr-2" />
                                    验证通过
                                </Button>
                                <Button
                                    @click="rejectCreator"
                                    variant="destructive"
                                    class="w-full"
                                >
                                    <X class="h-4 w-4 mr-2" />
                                    拒绝验证
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Verification Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>验证信息</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <Label class="text-sm text-gray-600 dark:text-gray-400">验证状态</Label>
                                <Badge :class="getStatusBadgeClass(creator.creator_profile?.verification_status)" class="mt-1">
                                    {{ creator.creator_profile?.verification_status === 'pending' ? '待审核' : '' }}
                                    {{ creator.creator_profile?.verification_status === 'verified' ? '已验证' : '' }}
                                    {{ creator.creator_profile?.verification_status === 'rejected' ? '已拒绝' : '' }}
                                </Badge>
                            </div>
                            <div v-if="creator.creator_profile?.verified_at">
                                <Label class="text-sm text-gray-600 dark:text-gray-400">验证时间</Label>
                                <p class="mt-1 text-sm">{{ formatDate(creator.creator_profile.verified_at) }}</p>
                            </div>
                            <div v-if="creator.creator_profile?.verification_notes">
                                <Label class="text-sm text-gray-600 dark:text-gray-400">验证备注</Label>
                                <p class="mt-1 text-sm">{{ creator.creator_profile.verification_notes }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>快速操作</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <Button
                                @click="toggleFeatured"
                                variant="outline"
                                class="w-full"
                            >
                                <Star v-if="!creator.creator_profile?.is_featured" class="h-4 w-4 mr-2" />
                                <StarOff v-else class="h-4 w-4 mr-2" />
                                {{ creator.creator_profile?.is_featured ? '取消精选' : '设为精选' }}
                            </Button>
                            <Button
                                @click="removeCreatorStatus"
                                variant="destructive"
                                class="w-full"
                            >
                                <Trash2 class="h-4 w-4 mr-2" />
                                移除创作者身份
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Account Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>账号信息</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <Label class="text-sm text-gray-600 dark:text-gray-400">用户ID</Label>
                                <p class="mt-1 text-sm">{{ creator.id }}</p>
                            </div>
                            <div>
                                <Label class="text-sm text-gray-600 dark:text-gray-400">邮箱</Label>
                                <p class="mt-1 text-sm">{{ creator.email }}</p>
                            </div>
                            <div>
                                <Label class="text-sm text-gray-600 dark:text-gray-400">创建时间</Label>
                                <p class="mt-1 text-sm">{{ formatDate(creator.created_at) }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
