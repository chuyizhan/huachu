<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import WebLayout from '@/layouts/WebLayout.vue';
</script>

<template>
    <Head title="隐私政策" />

    <WebLayout>
        <div class="max-w-4xl mx-auto u-p-25">
            <h1 class="text-3xl font-bold colorfff u-m-b-30">隐私政策</h1>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">1. 信息收集</h2>
                <p class="font24 color999 u-m-b-15">
                    我们收集您在使用美味平台时提供的信息，包括但不限于：注册信息、个人资料、发布的内容、评论等。
                </p>
                <p class="font24 color999">
                    我们会自动收集某些信息，如IP地址、设备信息、浏览器类型、访问时间等，以改善我们的服务。
                </p>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">2. 信息使用</h2>
                <p class="font24 color999 u-m-b-15">
                    我们使用收集的信息来提供、维护和改进我们的服务，包括：
                </p>
                <ul class="list-disc list-inside font24 color999 space-y-2">
                    <li>提供个性化内容和推荐</li>
                    <li>处理您的交易和订单</li>
                    <li>发送通知和更新</li>
                    <li>改善用户体验</li>
                    <li>防止欺诈和滥用</li>
                </ul>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">3. 信息共享</h2>
                <p class="font24 color999">
                    我们不会向第三方出售您的个人信息。我们可能会在以下情况下共享您的信息：征得您的同意、遵守法律要求、保护我们的权利和安全。
                </p>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">4. 数据安全</h2>
                <p class="font24 color999">
                    我们采取合理的安全措施保护您的个人信息，包括加密、访问控制等。但请注意，没有任何互联网传输或存储方式是100%安全的。
                </p>
            </div>

            <div class="listclass">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">5. 您的权利</h2>
                <p class="font24 color999">
                    您有权访问、更正、删除您的个人信息，或限制其处理。如需行使这些权利，请通过联系我们页面与我们联系。
                </p>
            </div>
        </div>
    </WebLayout>
</template>
