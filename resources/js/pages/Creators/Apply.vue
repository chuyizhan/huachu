<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import WebLayout from '@/layouts/WebLayout.vue';
</script>

<template>
    <Head title="申请成为认证博主" />

    <WebLayout>
        <div class="max-w-4xl mx-auto u-p-25">
            <h1 class="text-3xl font-bold colorfff u-m-b-30">申请成为认证博主</h1>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">什么是认证博主？</h2>
                <p class="font24 color999">
                    认证博主是平台认证的优质内容创作者，拥有丰富的创作经验和专业知识。通过认证后，您将获得专属徽章、更高的曝光度以及更多变现机会。
                </p>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">认证条件</h2>
                <div class="space-y-3 font24 color999">
                    <p><span class="colorz">创作经验：</span> 具有一定的内容创作经验，持续输出优质内容</p>
                    <p><span class="colorz">内容质量：</span> 能够持续创作高质量的原创内容</p>
                    <p><span class="colorz">专业领域：</span> 在某一领域有专长或独特见解</p>
                    <p><span class="colorz">作品展示：</span> 能够提供个人作品集或社交媒体账号作为参考</p>
                    <p><span class="colorz">遵守规则：</span> 同意平台创作者守则，保持良好形象</p>
                </div>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">认证权益</h2>
                <div class="space-y-3 font24 color999">
                    <p><span class="colorz">认证徽章：</span> 个人主页和发布内容显示专属认证标识</p>
                    <p><span class="colorz">流量扶持：</span> 内容获得更多推荐曝光机会</p>
                    <p><span class="colorz">付费内容：</span> 可以发布付费内容，获得收益分成</p>
                    <p><span class="colorz">数据分析：</span> 查看详细的内容数据和粉丝分析</p>
                    <p><span class="colorz">优先推荐：</span> 优先参与平台活动和合作机会</p>
                    <p><span class="colorz">专属客服：</span> 享受博主专属客服支持</p>
                </div>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">如何申请？</h2>
                <p class="font24 color999 u-m-b-15">
                    请联系平台客服提交认证申请，我们会在5-7个工作日内完成审核。
                </p>
                <div class="space-y-3 font24 color999">
                    <p><span class="colorz">联系方式：</span> 请通过站内客服或发送邮件至 creator@example.com</p>
                    <p><span class="colorz">准备材料：</span> 个人简介、专业证书/作品集、社交媒体账号等</p>
                    <p><span class="colorz">审核时间：</span> 5-7个工作日</p>
                    <p><span class="colorz">结果通知：</span> 审核结果将通过站内消息和邮件通知</p>
                </div>
            </div>

            <div class="listclass">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">注意事项</h2>
                <ul class="list-disc list-inside font24 color999 space-y-2">
                    <li>提交的材料需真实有效，虚假信息将导致认证失败</li>
                    <li>认证通过后需保持内容质量，违规将取消认证资格</li>
                    <li>认证资格不可转让</li>
                    <li>平台保留最终解释权</li>
                </ul>
            </div>
        </div>
    </WebLayout>
</template>
