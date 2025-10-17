<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import WebLayout from '@/layouts/WebLayout.vue';
</script>

<template>
    <Head title="积分规则" />

    <WebLayout>
        <div class="max-w-4xl mx-auto u-p-25">
            <h1 class="text-3xl font-bold colorfff u-m-b-30">积分规则</h1>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">什么是积分？</h2>
                <p class="font24 color999">
                    积分是美味平台的奖励系统，用于鼓励用户的活跃参与和优质贡献。积分可用于兑换会员、购买付费内容、参与平台活动等。积分不可提现，不可转让。
                </p>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">获取积分</h2>
                <div class="space-y-3 font24 color999">
                    <p><span class="colorz">每日签到：</span> +10积分</p>
                    <p><span class="colorz">连续签到7天：</span> 额外+50积分</p>
                    <p><span class="colorz">发布帖子：</span> +20积分</p>
                    <p><span class="colorz">帖子获赞：</span> 每次+2积分</p>
                    <p><span class="colorz">帖子被收藏：</span> 每次+5积分</p>
                    <p><span class="colorz">评论互动：</span> +5积分</p>
                    <p><span class="colorz">完善个人资料：</span> 一次性+100积分</p>
                    <p><span class="colorz">实名认证：</span> 一次性+200积分</p>
                    <p><span class="colorz">邀请好友注册：</span> 每位+50积分</p>
                </div>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">使用积分</h2>
                <div class="space-y-3 font24 color999">
                    <p><span class="colorz">兑换会员：</span> 月度会员500积分，年度会员5000积分</p>
                    <p><span class="colorz">购买内容：</span> 可抵扣50%的购买金额（1积分=0.1元）</p>
                    <p><span class="colorz">参与抽奖：</span> 根据活动规则消耗积分</p>
                    <p><span class="colorz">兑换礼品：</span> 在积分商城兑换实物或虚拟礼品</p>
                    <p><span class="colorz">提升权重：</span> 使用积分提升内容曝光</p>
                </div>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">积分有效期</h2>
                <p class="font24 color999 u-m-b-15">
                    积分自获得之日起有效期为1年。过期积分将自动清零，请及时使用。
                </p>
                <ul class="list-disc list-inside font24 color999 space-y-2">
                    <li>会员用户的积分有效期延长至2年</li>
                    <li>活动赠送的积分有效期以活动规则为准</li>
                    <li>系统会在积分到期前30天通过站内信提醒</li>
                </ul>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">积分等级</h2>
                <div class="space-y-3 font24 color999">
                    <p><span class="colorz">青铜会员：</span> 0-999积分</p>
                    <p><span class="colorz">白银会员：</span> 1000-4999积分，享受95折优惠</p>
                    <p><span class="colorz">黄金会员：</span> 5000-9999积分，享受9折优惠</p>
                    <p><span class="colorz">铂金会员：</span> 10000-49999积分，享受85折优惠</p>
                    <p><span class="colorz">钻石会员：</span> 50000+积分，享受8折优惠</p>
                </div>
            </div>

            <div class="listclass">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">注意事项</h2>
                <ul class="list-disc list-inside font24 color999 space-y-2">
                    <li>违规获得的积分将被清零</li>
                    <li>积分不可转让、不可兑换现金</li>
                    <li>平台保留调整积分规则的权利</li>
                    <li>账户注销后积分自动清零</li>
                    <li>恶意刷积分将导致账户封禁</li>
                </ul>
            </div>
        </div>
    </WebLayout>
</template>
