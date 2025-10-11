<script setup lang="ts">
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import WebLayout from '@/layouts/WebLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { disable, enable } from '@/routes/two-factor';
import { Form, Head } from '@inertiajs/vue3';
import { ShieldBan, ShieldCheck } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';

interface Props {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
}

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <WebLayout>
        <Head title="双因素认证" />
        <SettingsLayout>
            <Card class="bg-[#374151] border-[#4B5563]">
                <CardHeader>
                    <CardTitle class="text-white">双因素认证</CardTitle>
                    <CardDescription class="text-[#999999]">管理你的双因素认证设置</CardDescription>
                </CardHeader>
                <CardContent>
                    <div
                        v-if="!twoFactorEnabled"
                        class="flex flex-col items-start justify-start space-y-4"
                    >
                        <Badge variant="destructive">已禁用</Badge>

                        <p class="text-[#999999]">
                            启用双因素认证后，登录时将需要输入一个安全的验证码。此验证码可以从手机上支持TOTP的应用程序中获取。
                        </p>

                        <div>
                            <Button
                                v-if="hasSetupData"
                                @click="showSetupModal = true"
                                class="bg-[#ff6e02] hover:bg-[#e55a00] text-white"
                            >
                                <ShieldCheck class="mr-2" />继续设置
                            </Button>
                            <Form
                                v-else
                                v-bind="enable.form()"
                                @success="showSetupModal = true"
                                #default="{ processing }"
                            >
                                <Button
                                    type="submit"
                                    :disabled="processing"
                                    class="bg-[#ff6e02] hover:bg-[#e55a00] text-white"
                                >
                                    <ShieldCheck class="mr-2" />启用双因素认证
                                </Button>
                            </Form>
                        </div>
                    </div>

                    <div
                        v-else
                        class="flex flex-col items-start justify-start space-y-4"
                    >
                        <Badge class="bg-green-600 text-white">已启用</Badge>

                        <p class="text-[#999999]">
                            启用双因素认证后，登录时将需要输入一个安全的随机验证码，你可以从手机上支持TOTP的应用程序中获取该验证码。
                        </p>

                        <TwoFactorRecoveryCodes />

                        <div class="relative inline">
                            <Form v-bind="disable.form()" #default="{ processing }">
                                <Button
                                    variant="destructive"
                                    type="submit"
                                    :disabled="processing"
                                >
                                    <ShieldBan class="mr-2" />
                                    禁用双因素认证
                                </Button>
                            </Form>
                        </div>
                    </div>

                    <TwoFactorSetupModal
                        v-model:isOpen="showSetupModal"
                        :requiresConfirmation="requiresConfirmation"
                        :twoFactorEnabled="twoFactorEnabled"
                    />
                </CardContent>
            </Card>
        </SettingsLayout>
    </WebLayout>
</template>
