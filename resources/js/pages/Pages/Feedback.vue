<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import WebLayout from '@/layouts/WebLayout.vue';
</script>

<template>
    <Head title="意见反馈" />

    <WebLayout>
        <div class="max-w-4xl mx-auto u-p-25">
            <h1 class="text-3xl font-bold colorfff u-m-b-30">意见反馈</h1>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">我们重视您的声音</h2>
                <p class="font24 color999">
                    感谢您使用美味平台！您的反馈对我们非常重要，帮助我们不断改进产品和服务。无论是功能建议、问题反馈还是使用体验分享，我们都期待听到您的声音。
                </p>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">反馈渠道</h2>
                <p class="font24 color999 u-m-b-15">
                    您可以通过以下方式向我们提交反馈：
                </p>
                <div class="space-y-3 font24 color999">
                    <p><span class="colorz">邮箱反馈：</span> feedback@meiwei.com</p>
                    <p><span class="colorz">在线客服：</span> 工作日 9:00-18:00</p>
                    <p><span class="colorz">热线电话：</span> 400-123-4567</p>
                    <p><span class="colorz">微信公众号：</span> 关注后回复"反馈"</p>
                </div>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">我们关注的问题</h2>
                <p class="font24 color999 u-m-b-15">
                    以下类型的反馈将帮助我们更好地为您服务：
                </p>
                <ul class="list-disc list-inside font24 color999 space-y-2">
                    <li>功能使用问题或建议</li>
                    <li>界面设计和用户体验</li>
                    <li>内容质量和推荐准确性</li>
                    <li>技术故障或错误报告</li>
                    <li>安全和隐私相关问题</li>
                    <li>其他任何改进建议</li>
                </ul>
            </div>

            <div class="listclass">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">反馈响应时间</h2>
                <p class="font24 color999">
                    我们会认真对待每一条反馈。一般情况下，我们会在1-3个工作日内回复您的反馈。紧急问题会优先处理。感谢您的耐心等待！
                </p>
            </div>
        </div>
    </WebLayout>
</template>
