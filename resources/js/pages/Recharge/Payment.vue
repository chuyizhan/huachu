<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import WebLayout from '@/layouts/WebLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Loader2 } from 'lucide-vue-next';

interface Props {
    paymentUrl: string;
    paymentParams: Record<string, any>;
    orderNumber: string;
}

const props = defineProps<Props>();

onMounted(() => {
    // Auto-submit form after a short delay
    setTimeout(() => {
        const form = document.getElementById('payment-form') as HTMLFormElement;
        if (form) {
            form.submit();
        }
    }, 1500);
});

const submitForm = () => {
    const form = document.getElementById('payment-form') as HTMLFormElement;
    if (form) {
        form.submit();
    }
};
</script>

<template>
    <Head title="跳转到支付" />

    <WebLayout>
        <!-- Hidden form for POST submission -->
        <form id="payment-form" :action="paymentUrl" method="POST" style="display: none;">
            <input
                v-for="(value, key) in paymentParams"
                :key="key"
                type="hidden"
                :name="key"
                :value="value"
            />
        </form>

        <div class="min-h-screen flex items-center justify-center py-12">
            <div class="w-full max-w-md px-4">
                <Card class="bg-[#374151] border-[#4B5563]">
                    <CardHeader>
                        <CardTitle class="text-white text-center text-2xl">正在跳转到支付页面</CardTitle>
                    </CardHeader>
                    <CardContent class="text-center space-y-6">
                        <div class="flex justify-center">
                            <Loader2 class="w-16 h-16 text-[#ff6e02] animate-spin" />
                        </div>

                        <div class="space-y-2">
                            <p class="text-white text-lg">订单号：{{ orderNumber }}</p>
                            <p class="text-[#999999]">即将跳转到支付网关...</p>
                        </div>

                        <div class="pt-4">
                            <button
                                @click="submitForm"
                                class="inline-block bg-[#ff6e02] hover:bg-orange-600 text-white px-6 py-3 rounded-lg transition-colors"
                            >
                                如未自动跳转，请点击这里
                            </button>
                        </div>

                        <div class="text-xs text-[#999999] space-y-1">
                            <p>• 请在新页面完成支付</p>
                            <p>• 支付完成后会自动返回</p>
                            <p>• 请勿关闭此页面</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </WebLayout>
</template>
