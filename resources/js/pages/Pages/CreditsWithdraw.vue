<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import WebLayout from '@/layouts/WebLayout.vue';
</script>

<template>
    <Head title="金币提现" />

    <WebLayout>
        <div class="max-w-4xl mx-auto u-p-25">
            <h1 class="text-3xl font-bold colorfff u-m-b-30">金币提现</h1>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">什么是金币？</h2>
                <p class="font24 color999">
                    金币是美味平台的创作收益货币。当您的内容被其他用户购买或获得打赏时，您将获得金币。金币可以提现为人民币，也可以用于平台内的消费。
                </p>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">提现规则</h2>
                <ul class="list-disc list-inside font24 color999 space-y-2">
                    <li><span class="colorz">最低提现金额：</span> 100金币（等值100元人民币）</li>
                    <li><span class="colorz">提现比例：</span> 1金币 = 1元人民币</li>
                    <li><span class="colorz">手续费：</span> 每笔提现收取2%手续费</li>
                    <li><span class="colorz">到账时间：</span> 1-3个工作日</li>
                    <li><span class="colorz">提现次数：</span> 每月最多提现4次</li>
                </ul>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">提现流程</h2>
                <div class="space-y-3 font24 color999">
                    <p><span class="colorz">1. 绑定账户：</span> 首次提现需绑定支付宝或银行卡</p>
                    <p><span class="colorz">2. 申请提现：</span> 在"我的"页面选择"金币提现"</p>
                    <p><span class="colorz">3. 填写信息：</span> 输入提现金额和收款账户</p>
                    <p><span class="colorz">4. 提交审核：</span> 系统自动审核，通常2小时内完成</p>
                    <p><span class="colorz">5. 等待到账：</span> 审核通过后1-3个工作日到账</p>
                </div>
            </div>

            <div class="listclass u-m-b-25">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">注意事项</h2>
                <ul class="list-disc list-inside font24 color999 space-y-2">
                    <li>提现账户必须为本人实名认证账户</li>
                    <li>提现金额将扣除相应的手续费和税费</li>
                    <li>违规获得的金币不可提现</li>
                    <li>提现申请提交后不可撤销</li>
                    <li>平台保留对异常提现的审核权利</li>
                </ul>
            </div>

            <div class="listclass">
                <h2 class="text-xl font-semibold colorfff u-m-b-15">常见问题</h2>
                <div class="space-y-3 font24 color999">
                    <p><span class="colorz">Q: 为什么提现失败？</span></p>
                    <p>A: 可能是账户信息不正确、金额不足或违反提现规则。请检查后重试或联系客服。</p>
                    <p class="u-m-t-15"><span class="colorz">Q: 提现多久到账？</span></p>
                    <p>A: 通常1-3个工作日，节假日可能延迟。</p>
                    <p class="u-m-t-15"><span class="colorz">Q: 手续费如何计算？</span></p>
                    <p>A: 手续费 = 提现金额 × 2%。例如提现1000元，手续费为20元。</p>
                </div>
            </div>
        </div>
    </WebLayout>
</template>
