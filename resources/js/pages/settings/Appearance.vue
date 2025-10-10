<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

import WebLayout from '@/layouts/WebLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
</script>

<template>
    <WebLayout>
        <Head title="外观设置" />

        <SettingsLayout>
            <Card class="bg-[#374151] border-[#4B5563]">
                <CardHeader>
                    <CardTitle class="text-white">外观设置</CardTitle>
                    <CardDescription class="text-[#999999]">更新你的账户外观设置</CardDescription>
                </CardHeader>
                <CardContent>
                    <AppearanceTabs />
                </CardContent>
            </Card>
        </SettingsLayout>
    </WebLayout>
</template>
