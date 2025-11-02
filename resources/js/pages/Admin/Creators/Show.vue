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
import { ArrowLeft, Check, X, Star, StarOff, Trash2, Eye, Heart, FileText, Users, DollarSign, Plus, Edit as EditIcon } from 'lucide-vue-next';
import axios from 'axios';

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
    platform_commission_rate: number;
}

interface SubscriptionPlan {
    id: number;
    creator_id: number;
    name: string;
    description: string | null;
    duration_days: number;
    price: number;
    is_active: boolean;
    sort_order: number;
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
const subscriptionPlans = ref<SubscriptionPlan[]>([]);
const showPlanForm = ref(false);
const editingPlan = ref<SubscriptionPlan | null>(null);

const planForm = ref({
    name: '',
    description: '',
    duration_days: 30,
    price: 0,
    sort_order: 0,
});

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
        platform_commission_rate: props.creator.creator_profile?.platform_commission_rate || 30.00,
    }
});

// Load subscription plans on mount
const loadSubscriptionPlans = async () => {
    try {
        const response = await axios.get(`/admin/creators/${props.creator.id}/subscription-plans`);
        subscriptionPlans.value = response.data;
    } catch (error) {
        console.error('Failed to load subscription plans:', error);
    }
};

loadSubscriptionPlans();

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

const addNewPlan = () => {
    if (subscriptionPlans.value.filter(p => p.is_active).length >= 3) {
        alert('最多只能创建3个订阅计划');
        return;
    }
    editingPlan.value = null;
    planForm.value = {
        name: '',
        description: '',
        duration_days: 30,
        price: 0,
        sort_order: subscriptionPlans.value.length,
    };
    showPlanForm.value = true;
};

const editPlan = (plan: SubscriptionPlan) => {
    editingPlan.value = plan;
    planForm.value = {
        name: plan.name,
        description: plan.description || '',
        duration_days: plan.duration_days,
        price: plan.price,
        sort_order: plan.sort_order,
    };
    showPlanForm.value = true;
};

const savePlan = async () => {
    try {
        if (editingPlan.value) {
            // Update existing plan
            await axios.put(`/admin/creator-subscription-plans/${editingPlan.value.id}`, planForm.value);
        } else {
            // Create new plan
            await axios.post(`/admin/creators/${props.creator.id}/subscription-plans`, planForm.value);
        }
        await loadSubscriptionPlans();
        showPlanForm.value = false;
    } catch (error: any) {
        alert(error.response?.data?.message || '保存失败');
    }
};

const deletePlan = async (plan: SubscriptionPlan) => {
    if (!confirm(`确定要删除订阅计划"${plan.name}"吗？`)) return;

    try {
        await axios.delete(`/admin/creator-subscription-plans/${plan.id}`);
        await loadSubscriptionPlans();
    } catch (error) {
        alert('删除失败');
    }
};

const getDurationLabel = (days: number) => {
    if (days === 7) return '1周';
    if (days === 30) return '1个月';
    if (days === 90) return '3个月';
    if (days === 365) return '1年';
    return `${days}天`;
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

                            <!-- Platform Commission Rate -->
                            <div>
                                <Label>平台佣金比例 (%)</Label>
                                <Input
                                    v-if="editMode"
                                    v-model.number="form.profile.platform_commission_rate"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    class="mt-1"
                                />
                                <p v-else class="mt-1 text-sm">{{ creator.creator_profile?.platform_commission_rate || 30 }}%</p>
                                <p class="mt-1 text-xs text-gray-500">默认30%，平台从订阅收入中抽取的比例</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Subscription Plans -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle>订阅计划</CardTitle>
                                <Button
                                    @click="addNewPlan"
                                    size="sm"
                                    variant="outline"
                                    :disabled="subscriptionPlans.filter(p => p.is_active).length >= 3"
                                >
                                    <Plus class="h-4 w-4 mr-1" />
                                    添加计划
                                </Button>
                            </div>
                            <CardDescription>最多可创建3个订阅计划</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div
                                v-for="plan in subscriptionPlans.filter(p => p.is_active)"
                                :key="plan.id"
                                class="p-3 border rounded-lg"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-medium">{{ plan.name }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ getDurationLabel(plan.duration_days) }} - {{ plan.price }} 金币
                                        </p>
                                        <p v-if="plan.description" class="text-xs text-gray-500 mt-1">
                                            {{ plan.description }}
                                        </p>
                                    </div>
                                    <div class="flex gap-1">
                                        <Button
                                            @click="editPlan(plan)"
                                            size="sm"
                                            variant="ghost"
                                        >
                                            <EditIcon class="h-3 w-3" />
                                        </Button>
                                        <Button
                                            @click="deletePlan(plan)"
                                            size="sm"
                                            variant="ghost"
                                            class="text-red-600 hover:text-red-700"
                                        >
                                            <Trash2 class="h-3 w-3" />
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <p v-if="subscriptionPlans.filter(p => p.is_active).length === 0" class="text-sm text-gray-500 text-center py-4">
                                暂无订阅计划
                            </p>

                            <!-- Plan Form Modal -->
                            <div v-if="showPlanForm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
                                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                                    <h3 class="text-lg font-semibold mb-4">
                                        {{ editingPlan ? '编辑订阅计划' : '添加订阅计划' }}
                                    </h3>
                                    <div class="space-y-4">
                                        <div>
                                            <Label>计划名称</Label>
                                            <Input v-model="planForm.name" placeholder="例如：月度订阅" class="mt-1" />
                                        </div>
                                        <div>
                                            <Label>描述（可选）</Label>
                                            <Textarea v-model="planForm.description" rows="2" class="mt-1" />
                                        </div>
                                        <div>
                                            <Label>时长（天）</Label>
                                            <Input v-model.number="planForm.duration_days" type="number" min="1" class="mt-1" />
                                            <p class="text-xs text-gray-500 mt-1">常用：7天、30天、90天、365天</p>
                                        </div>
                                        <div>
                                            <Label>价格（金币）</Label>
                                            <Input v-model.number="planForm.price" type="number" min="0" step="0.01" class="mt-1" />
                                        </div>
                                    </div>
                                    <div class="flex gap-2 mt-6">
                                        <Button @click="savePlan" class="flex-1">保存</Button>
                                        <Button @click="showPlanForm = false" variant="outline" class="flex-1">取消</Button>
                                    </div>
                                </div>
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
