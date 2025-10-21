<script setup lang="ts">
import RegisteredUserController from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import WebAuthLayout from '@/layouts/WebAuthLayout.vue';
import { login } from '@/routes';
import { Form, Head, Link } from '@inertiajs/vue3';
import { LoaderCircle, User, Mail, Lock, UserPlus } from 'lucide-vue-next';
</script>

<template>
    <WebAuthLayout
        title="注册账户"
        :description="`填写信息加入${$page.props.name}`"
    >
        <Head title="注册" />

        <Form
            v-bind="RegisteredUserController.store.form()"
            :reset-on-success="['password', 'password_confirmation']"
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
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="username"
                            name="login_name"
                            placeholder="请设置用户名"
                            class="pl-10 bg-[#1f2937] border-[#1f2937] text-white placeholder:text-[#999999] focus:border-[#ff6e02] focus:ring-[#ff6e02]"
                        />
                    </div>
                    <InputError :message="errors.login_name" />
                </div>

                <div class="space-y-2">
                    <Label for="email" class="text-white text-sm font-medium">邮箱地址</Label>
                    <div class="relative">
                        <Mail class="absolute left-3 top-1/2 transform -translate-y-1/2 text-[#999999] w-4 h-4" />
                        <Input
                            id="email"
                            type="email"
                            required
                            :tabindex="2"
                            autocomplete="email"
                            name="email"
                            placeholder="请输入邮箱地址"
                            class="pl-10 bg-[#1f2937] border-[#1f2937] text-white placeholder:text-[#999999] focus:border-[#ff6e02] focus:ring-[#ff6e02]"
                        />
                    </div>
                    <InputError :message="errors.email" />
                </div>

                <div class="space-y-2">
                    <Label for="password" class="text-white text-sm font-medium">密码</Label>
                    <div class="relative">
                        <Lock class="absolute left-3 top-1/2 transform -translate-y-1/2 text-[#999999] w-4 h-4" />
                        <Input
                            id="password"
                            type="password"
                            required
                            :tabindex="3"
                            autocomplete="new-password"
                            name="password"
                            placeholder="请设置密码"
                            class="pl-10 bg-[#1f2937] border-[#1f2937] text-white placeholder:text-[#999999] focus:border-[#ff6e02] focus:ring-[#ff6e02]"
                        />
                    </div>
                    <InputError :message="errors.password" />
                </div>

                <div class="space-y-2">
                    <Label for="password_confirmation" class="text-white text-sm font-medium">确认密码</Label>
                    <div class="relative">
                        <Lock class="absolute left-3 top-1/2 transform -translate-y-1/2 text-[#999999] w-4 h-4" />
                        <Input
                            id="password_confirmation"
                            type="password"
                            required
                            :tabindex="4"
                            autocomplete="new-password"
                            name="password_confirmation"
                            placeholder="请再次输入密码"
                            class="pl-10 bg-[#1f2937] border-[#1f2937] text-white placeholder:text-[#999999] focus:border-[#ff6e02] focus:ring-[#ff6e02]"
                        />
                    </div>
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="w-full bg-[#ff6e02] text-white hover:bg-[#e55a00] transition-colors font-medium py-2.5"
                    tabindex="5"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <LoaderCircle
                        v-if="processing"
                        class="h-4 w-4 animate-spin mr-2"
                    />
                    <UserPlus v-else class="w-4 h-4 mr-2" />
                    创建账户
                </Button>
            </div>

            <div class="text-center text-sm text-[#999999] pt-4 border-t border-[#1f2937]">
                已有账户？
                <Link
                    :href="login()"
                    class="text-[#ff6e02] hover:text-[#e55a00] transition-colors font-medium"
                    :tabindex="6"
                >
                    立即登录
                </Link>
            </div>
        </Form>
    </WebAuthLayout>
</template>
