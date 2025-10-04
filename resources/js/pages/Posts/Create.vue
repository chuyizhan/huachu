<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import InputError from '@/components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { PlusCircle, FileText, Image, Video, Tag, Save, Send, X } from 'lucide-vue-next';
import { ref, computed } from 'vue';

// FilePond imports
import vueFilePond from 'vue-filepond';
import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';
import '@/../../resources/css/filepond-custom.css';

import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginImageExifOrientation from 'filepond-plugin-image-exif-orientation';
import FilePondPluginImageResize from 'filepond-plugin-image-resize';
import FilePondPluginImageTransform from 'filepond-plugin-image-transform';

const FilePond = vueFilePond(
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize,
    FilePondPluginImagePreview,
    FilePondPluginImageExifOrientation,
    FilePondPluginImageResize,
    FilePondPluginImageTransform
);

interface Category {
    id: number;
    name: string;
    slug: string;
    color?: string;
    icon?: string;
}

interface Props {
    categories: Category[];
}

const props = defineProps<Props>();

const form = useForm({
    title: '',
    content: '',
    post_category_id: '',
    type: 'discussion',
    excerpt: '',
    images: [] as File[],
    videos: [] as string[],
    tags: [] as string[],
    is_premium: false,
    status: 'draft'
});

const newTag = ref('');
const videoUrl = ref('');
const pond = ref(null);

const postTypes = [
    { value: 'discussion', label: '讨论', icon: '💬', description: '分享想法和观点' },
    { value: 'tutorial', label: '教程', icon: '📖', description: '分享知识和技能' },
    { value: 'showcase', label: '展示', icon: '🎨', description: '展示你的作品' },
    { value: 'question', label: '问题', icon: '❓', description: '寻求帮助和建议' }
];

const selectedCategory = computed(() => {
    return props.categories.find(cat => cat.id.toString() === form.post_category_id);
});

const selectedPostType = computed(() => {
    return postTypes.find(type => type.value === form.type);
});

function addTag() {
    const tag = newTag.value.trim();
    if (tag && !form.tags.includes(tag)) {
        form.tags.push(tag);
        newTag.value = '';
    }
}

function removeTag(index: number) {
    form.tags.splice(index, 1);
}

function handleFilePondUpdate() {
    if (pond.value) {
        // @ts-ignore - FilePond types
        const files = pond.value.getFiles();
        form.images = files.map((fileItem: any) => fileItem.file);
    }
}

function addVideo() {
    const url = videoUrl.value.trim();
    if (url && !form.videos.includes(url)) {
        form.videos.push(url);
        videoUrl.value = '';
    }
}

function removeVideo(index: number) {
    form.videos.splice(index, 1);
}

function saveDraft() {
    form.status = 'draft';
    form.post('/posts', {
        onSuccess: () => {
            // Handle success
        }
    });
}

function publishPost() {
    form.status = 'published';
    form.post('/posts', {
        onSuccess: () => {
            // Handle success
        }
    });
}
</script>

<template>
    <WebLayout>
        <Head title="创建新帖子" />

        <div class="min-h-screen py-8">
            <div class="max-w-4xl mx-auto px-4">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-white mb-2">创建新帖子</h1>
                    <p class="text-[#999999]">分享你的想法、经验或问题给{{ $page.props.name }}</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Form -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Info Card -->
                        <Card class="bg-[#374151] border-[#4B5563]">
                            <CardHeader>
                                <CardTitle class="text-white flex items-center gap-2">
                                    <FileText class="h-5 w-5" />
                                    基本信息
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <!-- Title -->
                                <div>
                                    <Label for="title" class="text-white">标题 *</Label>
                                    <Input
                                        id="title"
                                        v-model="form.title"
                                        placeholder="输入帖子标题..."
                                        class="mt-1 bg-[#1c1c1c] border-[#4B5563] text-white placeholder:text-[#999999]"
                                        :class="{ 'border-red-500': form.errors.title }"
                                    />
                                    <InputError :message="form.errors.title" class="mt-1" />
                                </div>

                                <!-- Category -->
                                <div>
                                    <Label class="text-white">分类 *</Label>
                                    <select
                                        v-model="form.post_category_id"
                                        class="mt-1 w-full bg-[#1c1c1c] border border-[#4B5563] text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#ff6e02] focus:border-[#ff6e02]"
                                        :class="{ 'border-red-500': form.errors.post_category_id }"
                                    >
                                        <option value="" class="text-[#999999]">选择分类</option>
                                        <option
                                            v-for="category in categories"
                                            :key="category.id"
                                            :value="category.id.toString()"
                                            class="bg-[#1c1c1c] text-white"
                                        >
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.post_category_id" class="mt-1" />
                                </div>

                                <!-- Post Type -->
                                <div>
                                    <Label class="text-white">帖子类型 *</Label>
                                    <div class="mt-2 grid grid-cols-2 gap-2">
                                        <button
                                            v-for="type in postTypes"
                                            :key="type.value"
                                            type="button"
                                            @click="form.type = type.value"
                                            class="p-3 rounded-lg border text-left transition-colors"
                                            :class="form.type === type.value
                                                ? 'border-[#ff6e02] bg-[#ff6e02]/10 text-[#ff6e02]'
                                                : 'border-[#4B5563] bg-[#1c1c1c] text-white hover:border-[#6B7280]'"
                                        >
                                            <div class="flex items-center gap-2">
                                                <span class="text-lg">{{ type.icon }}</span>
                                                <div>
                                                    <div class="font-medium">{{ type.label }}</div>
                                                    <div class="text-xs opacity-75">{{ type.description }}</div>
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                    <InputError :message="form.errors.type" class="mt-1" />
                                </div>

                                <!-- Content -->
                                <div>
                                    <Label for="content" class="text-white">内容 *</Label>
                                    <textarea
                                        id="content"
                                        v-model="form.content"
                                        placeholder="写下你的内容..."
                                        rows="12"
                                        class="mt-1 w-full bg-[#1c1c1c] border border-[#4B5563] text-white placeholder:text-[#999999] rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#ff6e02] focus:border-[#ff6e02] resize-y"
                                        :class="{ 'border-red-500': form.errors.content }"
                                    ></textarea>
                                    <InputError :message="form.errors.content" class="mt-1" />
                                </div>

                                <!-- Excerpt -->
                                <div>
                                    <Label for="excerpt" class="text-white">摘要</Label>
                                    <textarea
                                        id="excerpt"
                                        v-model="form.excerpt"
                                        placeholder="简短描述你的帖子内容... (可选)"
                                        rows="3"
                                        class="mt-1 w-full bg-[#1c1c1c] border border-[#4B5563] text-white placeholder:text-[#999999] rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#ff6e02] focus:border-[#ff6e02] resize-y"
                                    ></textarea>
                                    <p class="text-xs text-[#999999] mt-1">用于搜索结果和社交分享的预览</p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Media Card -->
                        <Card class="bg-[#374151] border-[#4B5563]">
                            <CardHeader>
                                <CardTitle class="text-white flex items-center gap-2">
                                    <Image class="h-5 w-5" />
                                    媒体内容
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <!-- Images -->
                                <div>
                                    <Label class="text-white">图片 (最多4张)</Label>
                                    <div class="mt-2">
                                        <FilePond
                                            ref="pond"
                                            name="images"
                                            :allow-multiple="true"
                                            :max-files="4"
                                            accepted-file-types="image/jpeg, image/jpg, image/png, image/gif, image/webp"
                                            :max-file-size="'5MB'"
                                            label-idle="拖放图片或 <span class='filepond--label-action'>浏览</span>"
                                            label-file-loading="加载中"
                                            label-file-processing="上传中"
                                            label-tap-to-cancel="点击取消"
                                            label-tap-to-retry="点击重试"
                                            label-tap-to-undo="点击撤销"
                                            :image-resize-target-width="1200"
                                            :image-resize-target-height="900"
                                            image-resize-mode="contain"
                                            image-resize-upscale="false"
                                            :image-transform-output-quality="85"
                                            :image-transform-output-quality-mode="'always'"
                                            @updatefiles="handleFilePondUpdate"
                                            :style-panel-layout="'compact'"
                                            :style-load-indicator-position="'center bottom'"
                                            :style-progress-indicator-position="'right bottom'"
                                            :style-button-remove-item-position="'left bottom'"
                                            :style-button-process-item-position="'right bottom'"
                                        />
                                    </div>
                                    <p class="text-xs text-[#999999] mt-2">支持 JPG, PNG, GIF, WebP，单张最大5MB。图片将自动优化。</p>
                                    <InputError :message="form.errors.images" class="mt-1" />
                                </div>

                                <!-- Videos -->
                                <div>
                                    <Label class="text-white">视频</Label>
                                    <div class="mt-2 flex gap-2">
                                        <Input
                                            v-model="videoUrl"
                                            placeholder="输入视频URL..."
                                            class="bg-[#1c1c1c] border-[#4B5563] text-white placeholder:text-[#999999]"
                                            @keyup.enter="addVideo"
                                        />
                                        <Button
                                            type="button"
                                            @click="addVideo"
                                            class="bg-[#ff6e02] hover:bg-[#e55a00] text-white"
                                        >
                                            <Video class="h-4 w-4" />
                                        </Button>
                                    </div>
                                    <div v-if="form.videos.length > 0" class="mt-2 space-y-1">
                                        <div
                                            v-for="(video, index) in form.videos"
                                            :key="index"
                                            class="flex items-center gap-2 p-2 bg-[#1c1c1c] rounded"
                                        >
                                            <Video class="h-5 w-5 text-[#ff6e02]" />
                                            <span class="flex-1 text-white text-sm truncate">{{ video }}</span>
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="ghost"
                                                @click="removeVideo(index)"
                                                class="text-red-400 hover:text-red-300 hover:bg-red-900/20"
                                            >
                                                <X class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Tags Card -->
                        <Card class="bg-[#374151] border-[#4B5563]">
                            <CardHeader>
                                <CardTitle class="text-white flex items-center gap-2">
                                    <Tag class="h-5 w-5" />
                                    标签
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="flex gap-2 mb-3">
                                    <Input
                                        v-model="newTag"
                                        placeholder="输入标签..."
                                        class="bg-[#1c1c1c] border-[#4B5563] text-white placeholder:text-[#999999]"
                                        @keyup.enter="addTag"
                                    />
                                    <Button
                                        type="button"
                                        @click="addTag"
                                        class="bg-[#ff6e02] hover:bg-[#e55a00] text-white"
                                    >
                                        添加
                                    </Button>
                                </div>
                                <div v-if="form.tags.length > 0" class="flex flex-wrap gap-2">
                                    <Badge
                                        v-for="(tag, index) in form.tags"
                                        :key="index"
                                        class="bg-[#ff6e02]/20 text-[#ff6e02] border-[#ff6e02]/30"
                                    >
                                        {{ tag }}
                                        <button
                                            type="button"
                                            @click="removeTag(index)"
                                            class="ml-1 hover:text-red-300"
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </Badge>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Publish Card -->
                        <Card class="bg-[#374151] border-[#4B5563]">
                            <CardHeader>
                                <CardTitle class="text-white">发布设置</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <!-- Premium Toggle -->
                                <div class="flex items-center justify-between">
                                    <div>
                                        <Label class="text-white">高级内容</Label>
                                        <p class="text-xs text-[#999999]">仅VIP会员可见</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            v-model="form.is_premium"
                                            class="sr-only peer"
                                        />
                                        <div class="w-11 h-6 bg-[#4B5563] peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#ff6e02] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#ff6e02]"></div>
                                    </label>
                                </div>

                                <!-- Action Buttons -->
                                <div class="space-y-2 pt-4 border-t border-[#4B5563]">
                                    <Button
                                        type="button"
                                        @click="saveDraft"
                                        :disabled="form.processing"
                                        class="w-full bg-[#4B5563] hover:bg-[#6B7280] text-white"
                                    >
                                        <Save class="h-4 w-4 mr-2" />
                                        {{ form.processing ? '保存中...' : '保存草稿' }}
                                    </Button>
                                    <Button
                                        type="button"
                                        @click="publishPost"
                                        :disabled="form.processing"
                                        class="w-full bg-[#ff6e02] hover:bg-[#e55a00] text-white"
                                    >
                                        <Send class="h-4 w-4 mr-2" />
                                        {{ form.processing ? '发布中...' : '立即发布' }}
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Preview Card -->
                        <Card class="bg-[#374151] border-[#4B5563]" v-if="form.title || selectedCategory || selectedPostType">
                            <CardHeader>
                                <CardTitle class="text-white">预览</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div v-if="selectedPostType" class="flex items-center gap-2">
                                    <span class="text-lg">{{ selectedPostType.icon }}</span>
                                    <Badge class="bg-[#ff6e02]/20 text-[#ff6e02] border-[#ff6e02]/30">
                                        {{ selectedPostType.label }}
                                    </Badge>
                                </div>

                                <h3 v-if="form.title" class="text-white font-medium">
                                    {{ form.title }}
                                </h3>

                                <div v-if="selectedCategory" class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-[#ff6e02]"></div>
                                    <span class="text-sm text-[#999999]">{{ selectedCategory.name }}</span>
                                </div>

                                <p v-if="form.excerpt" class="text-sm text-[#999999] line-clamp-3">
                                    {{ form.excerpt }}
                                </p>

                                <div v-if="form.tags.length > 0" class="flex flex-wrap gap-1">
                                    <Badge
                                        v-for="tag in form.tags.slice(0, 3)"
                                        :key="tag"
                                        class="text-xs bg-[#1c1c1c] text-[#999999]"
                                    >
                                        #{{ tag }}
                                    </Badge>
                                    <span v-if="form.tags.length > 3" class="text-xs text-[#999999]">
                                        +{{ form.tags.length - 3 }} more
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </WebLayout>
</template>