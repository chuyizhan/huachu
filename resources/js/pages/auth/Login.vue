<script setup lang="ts">
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import WebAuthLayout from '@/layouts/WebAuthLayout.vue';
import { register } from '@/routes';
import { request } from '@/routes/password';
import { Form, Head, Link } from '@inertiajs/vue3';
import { LoaderCircle, User, Lock } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <WebAuthLayout
        title="登录账户"
        :description="`输入用户名和密码登录${$page.props.name}`"
    >
        <Head title="登录" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-500"
        >
            {{ status }}
        </div>

        <Form
            v-bind="AuthenticatedSessionController.store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="space-y-4"
        >
            <div class="space-y-4">
                <div class="space-y-2">
                    <Label for="login_name" class="text-white text-sm font-medium">用户名</Label>
                    <div class="relative">
                        <User class="absolute left-3 top-1/2 transform -translate-y-1/2 text-[#999999] w-4 h-4" />
                        <Input
                            id="login_name"
                            type="text"
                            name="login_name"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="username"
                            placeholder="请输入用户名"
                            class="pl-10 bg-[#1f2937] border-[#1f2937] text-white placeholder:text-[#999999] focus:border-[#ff6e02] focus:ring-[#ff6e02]"
                        />
                    </div>
                    <InputError :message="errors.login_name" />
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="text-white text-sm font-medium">密码</Label>
                        <Link
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm text-[#ff6e02] hover:text-[#e55a00] transition-colors"
                            :tabindex="5"
                        >
                            忘记密码？
                        </Link>
                    </div>
                    <div class="relative">
                        <Lock class="absolute left-3 top-1/2 transform -translate-y-1/2 text-[#999999] w-4 h-4" />
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            placeholder="请输入密码"
                            class="pl-10 bg-[#1f2937] border-[#1f2937] text-white placeholder:text-[#999999] focus:border-[#ff6e02] focus:ring-[#ff6e02]"
                        />
                    </div>
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-2 text-sm text-white cursor-pointer">
                        <Checkbox
                            id="remember"
                            name="remember"
                            :tabindex="3"
                            class="border-[#999999] data-[state=checked]:bg-[#ff6e02] data-[state=checked]:border-[#ff6e02]"
                        />
                        <span>记住我</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="w-full bg-[#ff6e02] text-white hover:bg-[#e55a00] transition-colors font-medium py-2.5"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <LoaderCircle
                        v-if="processing"
                        class="h-4 w-4 animate-spin mr-2"
                    />
                    登录
                </Button>
            </div>

            <div class="text-center text-sm text-[#999999] pt-4 border-t border-[#1f2937]">
                还没有账户？
                <Link :href="register()" class="text-[#ff6e02] hover:text-[#e55a00] transition-colors font-medium" :tabindex="5">
                    立即注册
                </Link>
            </div>
        </Form>
    </WebAuthLayout>
</template>
