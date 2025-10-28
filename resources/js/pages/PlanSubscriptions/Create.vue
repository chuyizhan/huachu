<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Crown, ArrowLeft, Check, Zap, Shield, Star, Sparkles } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ref } from 'vue';

interface Plan {
    id: number;
    name: string;
    slug: string;
    level: number;
    price: string;
    period_days: number;
    description: string | null;
    features: string[] | null;
    badge_color: string;
    badge_text_color: string;
    is_active: boolean;
}

interface Props {
    plans: Plan[];
}

const props = defineProps<Props>();

const selectedPlanId = ref<number | null>(null);
const paymentMethod = ref('alipay');

const form = useForm({
    plan_id: null as number | null,
    payment_method: 'alipay',
    auto_renew: false,
});

const selectPlan = (planId: number) => {
    selectedPlanId.value = planId;
    form.plan_id = planId;
};

const getSelectedPlan = () => {
    return props.plans.find(p => p.id === selectedPlanId.value);
};

const submit = () => {
    console.log('Submit function called');
    console.log('Form data:', {
        plan_id: form.plan_id,
        payment_method: form.payment_method,
        auto_renew: form.auto_renew
    });

    if (!form.plan_id) {
        console.log('No plan selected');
        alert('请选择一个套餐');
        return;
    }

    console.log('Submitting to /plan-subscriptions');
    form.post('/plan-subscriptions', {
        preserveScroll: false,
        onError: (errors) => {
            console.error('Subscription error:', errors);
            if (errors.subscription) {
                alert(errors.subscription);
            } else if (errors.payment) {
                alert(errors.payment);
            }
        },
        onSuccess: () => {
            console.log('Subscription request successful');
        },
        onStart: () => {
            console.log('Form submission started');
        },
        onFinish: () => {
            console.log('Form submission finished');
        }
    });
};

const getLevelIcon = (level: number) => {
    if (level >= 4) return Crown;
    if (level >= 3) return Sparkles;
    if (level >= 2) return Star;
    return Shield;
};

const getPeriodText = (days: number) => {
    if (days >= 365) {
        const years = Math.floor(days / 365);
        return `${years}年`;
    }
    if (days >= 30) {
        const months = Math.floor(days / 30);
        return `${months}个月`;
    }
    return `${days}天`;
};
</script>

<template>
    <WebLayout>
        <Head title="开通VIP会员" />

        <div class="min-h-screen bg-[#1c1c1c] pb-20">
            <!-- Header -->
            <div class="bg-[#1f2937] u-p-25 sticky top-0 z-10">
                <div class="flex items-center">
                    <Link href="/plan-subscriptions" class="u-p-r-25">
                        <ArrowLeft class="w-6 h-6 text-white" />
                    </Link>
                    <h1 class="colorz font32 font-w-600">开通VIP会员</h1>
                </div>
            </div>

            <!-- Error Messages -->
            <div v-if="form.errors.subscription || form.errors.payment" class="u-m-25">
                <div class="bg-red-500/20 border border-red-500 rounded-lg u-p-25">
                    <p class="font24 text-red-500">{{ form.errors.subscription || form.errors.payment }}</p>
                </div>
            </div>

            <!-- Plans -->
            <div class="u-m-25">
                <h3 class="font28 colorfff font-w-600 u-m-b-20 u-p-l-10">选择套餐</h3>

                <div class="space-y-4">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        @click="selectPlan(plan.id)"
                        class="listclass u-p-25 cursor-pointer transition-all"
                        :class="selectedPlanId === plan.id ? 'ring-2 ring-[#ff6e02]' : ''"
                    >
                        <div class="flex items-start justify-between u-m-b-15">
                            <div class="flex items-center">
                                <component
                                    :is="getLevelIcon(plan.level)"
                                    class="w-8 h-8 u-p-r-15"
                                    :class="selectedPlanId === plan.id ? 'text-[#ff6e02]' : 'text-white'"
                                />
                                <div>
                                    <div class="flex items-center u-m-b-5">
                                        <h3 class="font32 colorfff font-w-600 u-p-r-15">{{ plan.name }}</h3>
                                        <Badge
                                            :style="{
                                                backgroundColor: plan.badge_color,
                                                color: plan.badge_text_color
                                            }"
                                            class="font20"
                                        >
                                            LV{{ plan.level }}
                                        </Badge>
                                    </div>
                                    <p class="font22 color999">{{ getPeriodText(plan.period_days) }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font36 text-[#ff6e02] font-w-700">¥{{ parseFloat(plan.price).toFixed(2) }}</div>
                            </div>
                        </div>

                        <div v-if="plan.description" class="u-m-b-15">
                            <p class="text-sm lg:text-md colorz">{{ plan.description }}</p>
                        </div>

                        <div v-if="plan.features && plan.features.length > 0" class="space-y-2">
                            <div
                                v-for="(feature, index) in plan.features"
                                :key="index"
                                class="flex items-center"
                            >
                                <Check class="w-4 h-4 text-[#ff6e02] u-p-r-10 flex-shrink-0" />
                                <span class="font22 colorz">{{ feature }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div v-if="selectedPlanId" class="u-m-25">
                <h3 class="font28 colorfff font-w-600 u-m-b-20 u-p-l-10">支付方式</h3>

                <div class="space-y-3">
                    <div
                        @click="form.payment_method = 'alipay'"
                        class="listclass u-p-25 cursor-pointer transition-all"
                        :class="form.payment_method === 'alipay' ? 'ring-2 ring-[#ff6e02]' : ''"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-[#1677FF] rounded-lg flex items-center justify-center u-p-r-15">
                                    <span class="text-white font-w-700 font24">支</span>
                                </div>
                                <span class="font28 colorfff">支付宝</span>
                            </div>
                            <div
                                class="w-6 h-6 rounded-full border-2 flex items-center justify-center"
                                :class="form.payment_method === 'alipay' ? 'border-[#ff6e02] bg-[#ff6e02]' : 'border-[#999999]'"
                            >
                                <Check v-if="form.payment_method === 'alipay'" class="w-4 h-4 text-white" />
                            </div>
                        </div>
                    </div>

                    <div
                        @click="form.payment_method = 'wechat'"
                        class="listclass u-p-25 cursor-pointer transition-all"
                        :class="form.payment_method === 'wechat' ? 'ring-2 ring-[#ff6e02]' : ''"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-[#07C160] rounded-lg flex items-center justify-center u-p-r-15">
                                    <span class="text-white font-w-700 font24">微</span>
                                </div>
                                <span class="font28 colorfff">微信支付</span>
                            </div>
                            <div
                                class="w-6 h-6 rounded-full border-2 flex items-center justify-center"
                                :class="form.payment_method === 'wechat' ? 'border-[#ff6e02] bg-[#ff6e02]' : 'border-[#999999]'"
                            >
                                <Check v-if="form.payment_method === 'wechat'" class="w-4 h-4 text-white" />
                            </div>
                        </div>
                    </div>

                    <div
                        @click="form.payment_method = 'fake'"
                        class="listclass u-p-25 cursor-pointer transition-all"
                        :class="form.payment_method === 'fake' ? 'ring-2 ring-[#ff6e02]' : ''"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-[#666666] rounded-lg flex items-center justify-center u-p-r-15">
                                    <span class="text-white font-w-700 font24">测</span>
                                </div>
                                <span class="font28 colorfff">测试支付</span>
                            </div>
                            <div
                                class="w-6 h-6 rounded-full border-2 flex items-center justify-center"
                                :class="form.payment_method === 'fake' ? 'border-[#ff6e02] bg-[#ff6e02]' : 'border-[#999999]'"
                            >
                                <Check v-if="form.payment_method === 'fake'" class="w-4 h-4 text-white" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Order Summary -->
            <div v-if="selectedPlanId && getSelectedPlan()" class="u-m-25">
                <div class="listclass u-p-25">
                    <h3 class="font28 colorfff font-w-600 u-m-b-20">订单信息</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font24 color999">套餐</span>
                            <span class="font24 colorfff">{{ getSelectedPlan()?.name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font24 color999">时长</span>
                            <span class="font24 colorfff">{{ getPeriodText(getSelectedPlan()?.period_days || 0) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font24 color999">支付方式</span>
                            <span class="font24 colorfff">{{ form.payment_method === 'alipay' ? '支付宝' : form.payment_method === 'wechat' ? '微信支付' : '测试支付' }}</span>
                        </div>
                        <div class="border-t border-[#374151] u-p-t-15 u-m-t-15"></div>
                        <div class="flex items-center justify-between">
                            <span class="font28 colorfff font-w-600">应付金额</span>
                            <span class="font40 text-[#ff6e02] font-w-700">¥{{ parseFloat(getSelectedPlan()?.price || '0').toFixed(2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div v-if="selectedPlanId" class="u-m-25">
                <Button
                    @click="submit"
                    :disabled="form.processing"
                    class="w-full bg-[#ff6e02] hover:bg-[#ff8534] text-white font30 u-p-25 h-auto rounded-lg"
                >
                    <Zap class="w-6 h-6 u-p-r-10" />
                    {{ form.processing ? '处理中...' : '立即开通' }}
                </Button>
            </div>

            <!-- Tips -->
            <div class="u-m-25">
                <div class="u-p-25">
                    <h4 class="font24 color999 u-m-b-15">温馨提示</h4>
                    <ul class="space-y-2">
                        <li class="font22 color999 flex items-start">
                            <span class="u-p-r-5">•</span>
                            <span>支付成功后会员权益立即生效</span>
                        </li>
                        <li class="font22 color999 flex items-start">
                            <span class="u-p-r-5">•</span>
                            <span>开启自动续费可享受不间断服务</span>
                        </li>
                        <li class="font22 color999 flex items-start">
                            <span class="u-p-r-5">•</span>
                            <span>可在个人中心随时管理订阅</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </WebLayout>
</template>
