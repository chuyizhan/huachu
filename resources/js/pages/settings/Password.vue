<script setup lang="ts">
import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import InputError from '@/components/InputError.vue';
import WebLayout from '@/layouts/WebLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);
</script>

<template>
    <WebLayout>
        <Head title="密码设置" />

        <SettingsLayout>
            <Card class="bg-[#374151] border-[#4B5563]">
                <CardHeader>
                    <CardTitle class="text-white">修改密码</CardTitle>
                    <CardDescription class="text-[#999999]">确保你的账户使用足够长且随机的密码来保证安全</CardDescription>
                </CardHeader>
                <CardContent>
                    <Form
                        v-bind="PasswordController.update.form()"
                        :options="{
                            preserveScroll: true,
                        }"
                        reset-on-success
                        :reset-on-error="[
                            'password',
                            'password_confirmation',
                            'current_password',
                        ]"
                        class="space-y-6"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                        <div class="grid gap-2">
                            <Label for="current_password" class="text-white">当前密码</Label>
                            <Input
                                id="current_password"
                                ref="currentPasswordInput"
                                name="current_password"
                                type="password"
                                class="bg-[#1c1c1c] border-[#4B5563] text-white placeholder:text-[#999999]"
                                autocomplete="current-password"
                                placeholder="输入当前密码"
                            />
                            <InputError :message="errors.current_password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password" class="text-white">新密码</Label>
                            <Input
                                id="password"
                                ref="passwordInput"
                                name="password"
                                type="password"
                                class="bg-[#1c1c1c] border-[#4B5563] text-white placeholder:text-[#999999]"
                                autocomplete="new-password"
                                placeholder="输入新密码"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password_confirmation" class="text-white">确认密码</Label>
                            <Input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                class="bg-[#1c1c1c] border-[#4B5563] text-white placeholder:text-[#999999]"
                                autocomplete="new-password"
                                placeholder="再次输入新密码"
                            />
                            <InputError :message="errors.password_confirmation" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button
                                :disabled="processing"
                                data-test="update-password-button"
                                class="bg-[#ff6e02] hover:bg-[#e55a00] text-white"
                            >保存密码</Button>

                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p
                                    v-show="recentlySuccessful"
                                    class="text-sm text-green-500"
                                >
                                    已保存
                                </p>
                            </Transition>
                        </div>
                    </Form>
                </CardContent>
            </Card>
        </SettingsLayout>
    </WebLayout>
</template>
