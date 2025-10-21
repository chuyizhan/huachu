<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import WebLayout from '@/layouts/WebLayout.vue';
</script>

<template>
    <Head title="联系我们" />

    <WebLayout>
        <div class="max-w-4xl mx-auto u-p-25">
            <h1 class="text-3xl font-bold colorfff u-m-b-30">联系我们</h1>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">客服支持</h2>
                <p class="font24 color999 u-m-b-10">
                    如果您有任何问题或需要帮助，请通过以下方式联系我们：
                </p>
                <div class="space-y-3 font24 color999">
                    <p><span class="colorz">电子邮件：</span> support@meiwei.com</p>
                    <p><span class="colorz">客服热线：</span> 400-123-4567</p>
                    <p><span class="colorz">工作时间：</span> 周一至周五 9:00-18:00</p>
                </div>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">商务合作</h2>
                <p class="font24 color999 u-m-b-10">
                    如果您对商务合作感兴趣，请联系：
                </p>
                <div class="space-y-3 font24 color999">
                    <p><span class="colorz">合作邮箱：</span> business@meiwei.com</p>
                    <p><span class="colorz">联系电话：</span> 010-12345678</p>
                </div>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">公司地址</h2>
                <p class="font24 color999">
                    北京市朝阳区某某街道123号<br>
                    美味科技有限公司<br>
                    邮编：100000
                </p>
            </div>

            <div class="listclass">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">社交媒体</h2>
                <div class="flex-x-center gap-4 u-m-t-15">
                    <a href="#" class="flex-x-center gap-2 bg-[#ff6e02] text-white px-4 py-2 rounded-40 hover:bg-[#e55a00] transition-colors">
                        <span>微信公众号</span>
                    </a>
                    <a href="#" class="flex-x-center gap-2 bg-[#ff6e02] text-white px-4 py-2 rounded-40 hover:bg-[#e55a00] transition-colors">
                        <span>微博</span>
                    </a>
                    <a href="#" class="flex-x-center gap-2 bg-[#ff6e02] text-white px-4 py-2 rounded-40 hover:bg-[#e55a00] transition-colors">
                        <span>抖音</span>
                    </a>
                </div>
            </div>
        </div>
    </WebLayout>
</template>
