<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <!-- Footer -->
    <footer class="bg-[#1c1c1c] border-t border-[#374151] py-8 px-4">
        <div class="max-w-[1000px] mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <h3 class="text-white font-medium mb-3">关于我们</h3>
                    <ul class="space-y-2 text-sm text-[#999999]">
                        <li><Link href="/about" class="hover:text-[#ff6e02]">网站介绍</Link></li>
                        <li><Link href="/contact" class="hover:text-[#ff6e02]">联系我们</Link></li>
                        <li><Link href="/help" class="hover:text-[#ff6e02]">使用帮助</Link></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-medium mb-3">用户服务</h3>
                    <ul class="space-y-2 text-sm text-[#999999]">
                        <li><Link href="/creators/apply" class="hover:text-[#ff6e02]">申请认证</Link></li>
                        <li><Link href="/vip" class="hover:text-[#ff6e02]">VIP会员</Link></li>
                        <li><Link href="/rules" class="hover:text-[#ff6e02]">社区规则</Link></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-medium mb-3">热门分类</h3>
                    <ul class="space-y-2 text-sm text-[#999999]">
                        <li><Link href="/community/posts?category=sichuan" class="hover:text-[#ff6e02]">川菜</Link></li>
                        <li><Link href="/community/posts?category=cantonese" class="hover:text-[#ff6e02]">粤菜</Link></li>
                        <li><Link href="/community/posts?category=home-cooking" class="hover:text-[#ff6e02]">家常菜</Link></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-medium mb-3">联系方式</h3>
                    <div class="space-y-2 text-sm text-[#999999]">
                        <p>客服QQ：123456789</p>
                        <p>邮箱：contact@huachu.com</p>
                        <p>工作时间：9:00-18:00</p>
                    </div>
                </div>
            </div>
            <div class="border-t border-[#374151] mt-6 pt-4 text-center text-sm text-[#999999]">
                <p>&copy; 2024 华厨社区. 版权所有</p>
            </div>
        </div>
    </footer>
</template>