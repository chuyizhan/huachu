<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import WebLayout from '@/layouts/WebLayout.vue';
</script>

<template>
    <Head title="服务条款" />

    <WebLayout>
        <div class="max-w-4xl mx-auto u-p-25">
            <h1 class="text-3xl font-bold colorfff u-m-b-30">服务条款</h1>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">1. 接受条款</h2>
                <p class="font24 color999">
                    欢迎使用美味平台。通过访问或使用我们的服务，您同意受这些服务条款的约束。如果您不同意这些条款，请不要使用我们的服务。
                </p>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">2. 用户账户</h2>
                <p class="font24 color999 u-m-b-15">
                    您需要创建账户才能使用某些功能。您同意：
                </p>
                <ul class="list-disc list-inside font24 color999 space-y-2">
                    <li>提供准确、完整的注册信息</li>
                    <li>保护您的账户密码安全</li>
                    <li>对您账户下的所有活动负责</li>
                    <li>立即通知我们任何未经授权的使用</li>
                </ul>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">3. 用户内容</h2>
                <p class="font24 color999 u-m-b-15">
                    您对自己发布的内容负责。您同意不发布以下内容：
                </p>
                <ul class="list-disc list-inside font24 color999 space-y-2">
                    <li>非法、有害或冒犯性内容</li>
                    <li>侵犯他人知识产权的内容</li>
                    <li>虚假或误导性信息</li>
                    <li>垃圾邮件或广告</li>
                </ul>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">4. 知识产权</h2>
                <p class="font24 color999">
                    平台上的所有内容，包括文本、图形、标志、图像等，均受版权法保护。未经授权，您不得复制、修改、分发这些内容。
                </p>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">5. 付费服务</h2>
                <p class="font24 color999">
                    某些功能可能需要付费。所有费用均以人民币计价。一旦购买，除法律规定外，不可退款。
                </p>
            </div>

            <div class="listclass">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">6. 服务变更和终止</h2>
                <p class="font24 color999">
                    我们保留随时修改或终止服务的权利，无需事先通知。我们也可能因违反条款而暂停或终止您的账户。
                </p>
            </div>
        </div>
    </WebLayout>
</template>
