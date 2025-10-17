<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import WebLayout from '@/layouts/WebLayout.vue';
</script>

<template>
    <Head title="关于我们" />

    <WebLayout>
        <div class="max-w-4xl mx-auto u-p-25">
            <h1 class="text-3xl font-bold colorfff u-m-b-30">关于我们</h1>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">美味的故事</h2>
                <p class="font24 color999 u-m-b-15">
                    美味平台成立于2023年，是一个专注于优质内容创作与分享的社区平台。我们致力于为创作者提供一个自由表达、公平收益的创作环境，为用户提供高质量、有价值的内容体验。
                </p>
                <p class="font24 color999">
                    我们相信，每个人都有独特的故事和见解值得被听见。通过技术创新和用户至上的理念，美味正在成为中国领先的内容创作平台。
                </p>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">我们的使命</h2>
                <p class="font24 color999 u-m-b-15">
                    让优质内容创作者获得应有的回报，让每一位用户都能找到有价值的内容。
                </p>
                <ul class="list-disc list-inside font24 color999 space-y-2">
                    <li>为创作者提供公平透明的收益机制</li>
                    <li>为用户提供个性化的内容推荐</li>
                    <li>构建积极向上的创作者社区</li>
                    <li>推动内容创作行业的健康发展</li>
                </ul>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">平台特色</h2>
                <div class="space-y-3 font24 color999">
                    <p><span class="colorz">创作者优先：</span> 70%的内容收益直接分配给创作者</p>
                    <p><span class="colorz">双币体系：</span> 积分奖励活跃用户，金币保障创作者收益</p>
                    <p><span class="colorz">会员订阅：</span> 支持创作者建立稳定的粉丝群体</p>
                    <p><span class="colorz">智能推荐：</span> AI驱动的个性化内容分发</p>
                    <p><span class="colorz">社区互动：</span> 评论、点赞、收藏，构建活跃社区</p>
                </div>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">发展历程</h2>
                <div class="space-y-3 font24 color999">
                    <p><span class="colorz">2023年1月：</span> 美味平台正式上线</p>
                    <p><span class="colorz">2023年6月：</span> 注册用户突破10万，创作者社区初具规模</p>
                    <p><span class="colorz">2023年12月：</span> 推出会员订阅功能，上线移动端应用</p>
                    <p><span class="colorz">2024年3月：</span> 完成A轮融资，用户数突破50万</p>
                    <p><span class="colorz">2024年至今：</span> 持续优化产品体验，拓展内容品类</p>
                </div>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">团队介绍</h2>
                <p class="font24 color999">
                    美味团队由来自互联网、媒体、技术等领域的专业人士组成。我们热爱内容创作，相信技术的力量，致力于打造最好的创作者平台。团队成员曾服务于知名互联网公司，拥有丰富的产品、技术和运营经验。
                </p>
            </div>

            <div class="listclass">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">联系方式</h2>
                <div class="space-y-3 font24 color999">
                    <p><span class="colorz">公司全称：</span> 美味科技有限公司</p>
                    <p><span class="colorz">公司地址：</span> 北京市朝阳区某某街道123号</p>
                    <p><span class="colorz">商务合作：</span> business@meiwei.com</p>
                    <p><span class="colorz">客服邮箱：</span> support@meiwei.com</p>
                    <p><span class="colorz">联系电话：</span> 400-123-4567</p>
                </div>
            </div>
        </div>
    </WebLayout>
</template>
