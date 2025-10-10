<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import WebLayout from '@/layouts/WebLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <WebLayout>
        <Head title="个人资料设置" />

        <SettingsLayout>
            <Card class="bg-[#374151] border-[#4B5563]">
                <CardHeader>
                    <CardTitle class="text-white">个人信息</CardTitle>
                    <CardDescription class="text-[#999999]">更新你的姓名和邮箱地址</CardDescription>
                </CardHeader>
                <CardContent>
                    <Form
                        v-bind="ProfileController.update.form()"
                        class="space-y-6"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                        <div class="grid gap-2">
                            <Label for="name" class="text-white">姓名</Label>
                            <Input
                                id="name"
                                class="bg-[#1c1c1c] border-[#4B5563] text-white placeholder:text-[#999999]"
                                name="name"
                                :default-value="user.name"
                                required
                                autocomplete="name"
                                placeholder="输入你的姓名"
                            />
                            <InputError class="mt-2" :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email" class="text-white">邮箱地址</Label>
                            <Input
                                id="email"
                                type="email"
                                class="bg-[#1c1c1c] border-[#4B5563] text-white placeholder:text-[#999999]"
                                name="email"
                                :default-value="user.email"
                                required
                                autocomplete="username"
                                placeholder="输入你的邮箱地址"
                            />
                            <InputError class="mt-2" :message="errors.email" />
                        </div>

                        <div v-if="mustVerifyEmail && !user.email_verified_at">
                            <p class="-mt-4 text-sm text-[#999999]">
                                你的邮箱地址尚未验证。
                                <Link
                                    :href="send()"
                                    as="button"
                                    class="text-[#ff6e02] underline underline-offset-4 hover:text-[#e55a00]"
                                >
                                    点击此处重新发送验证邮件。
                                </Link>
                            </p>

                            <div
                                v-if="status === 'verification-link-sent'"
                                class="mt-2 text-sm font-medium text-green-500"
                            >
                                新的验证链接已发送到你的邮箱地址。
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <Button
                                :disabled="processing"
                                data-test="update-profile-button"
                                class="bg-[#ff6e02] hover:bg-[#e55a00] text-white"
                            >保存</Button>

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

            <DeleteUser />
        </SettingsLayout>
    </WebLayout>
</template>
