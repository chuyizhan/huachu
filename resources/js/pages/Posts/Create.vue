<script setup lang="ts">
import WebLayout from '@/layouts/WebLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import InputError from '@/components/InputError.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { PlusCircle, FileText, Image, Video, Tag, Save, Send, X, AlertCircle } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';

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
    useCloudForImages: boolean;
}

const props = defineProps<Props>();
const page = usePage();

const isCreator = computed(() => {
    return page.props.auth?.user?.is_creator || false;
});

const form = useForm({
    title: '',
    content: '',
    post_category_id: '',
    type: 'showcase',
    excerpt: '',
    images: [] as File[],
    image_temp_upload_ids: [] as number[],
    video_temp_upload_id: null as number | null,
    videos: [] as string[],
    tags: [] as string[],
    is_premium: isCreator.value, // Default to true for creators
    status: 'draft'
});

const newTag = ref('');
const videoUrl = ref('');
const pond = ref(null);
const videoInput = ref<HTMLInputElement | null>(null);
const videoPreview = ref<string | null>(null);
const showErrorDialog = ref(false);
const errorMessage = ref('');
const videoFileName = ref<string>('');
const videoFileSize = ref<number>(0);
const isUploadingVideo = ref(false);
const videoUploadProgress = ref(0);
const uploadError = ref<string | null>(null);

// Image upload state (for cloud storage)
const uploadedImages = ref<Array<{
    tempUploadId: number;
    preview: string;
    name: string;
    size: number;
    uploading: boolean;
    progress: number;
    error: string | null;
}>>([]);
const imageInput = ref<HTMLInputElement | null>(null);

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

async function handleVideoUpload(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        // Validate file size (max 1GB)
        if (file.size > 1024 * 1024 * 1024) {
            alert('视频文件太大！最大支持1GB');
            return;
        }

        // Validate file type
        if (!file.type.startsWith('video/')) {
            alert('请上传有效的视频文件');
            return;
        }

        // Store file info for display
        videoFileName.value = file.name;
        videoFileSize.value = file.size;

        // Create preview URL
        if (videoPreview.value) {
            URL.revokeObjectURL(videoPreview.value);
        }
        videoPreview.value = URL.createObjectURL(file);

        // Upload video immediately
        await uploadVideoToServer(file);
    }
}

async function uploadVideoToServer(file: File) {
    isUploadingVideo.value = true;
    uploadError.value = null;
    videoUploadProgress.value = 0;

    try {
        // Step 1: Get presigned URL from backend
        const presignedResponse = await fetch('/api/v1/media/presigned-url', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                file_name: file.name,
                file_type: file.type,
                file_size: file.size,
                type: 'video',
            }),
        });

        if (!presignedResponse.ok) {
            throw new Error('获取上传链接失败');
        }

        const { presigned_url, temp_upload_id, s3_path } = await presignedResponse.json();

        // Step 2: Upload directly to S3 using presigned URL
        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                videoUploadProgress.value = Math.round((e.loaded / e.total) * 95); // Reserve 5% for confirmation
            }
        });

        await new Promise((resolve, reject) => {
            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    resolve(xhr.response);
                } else {
                    reject(new Error('S3上传失败'));
                }
            });

            xhr.addEventListener('error', () => reject(new Error('网络错误')));
            xhr.addEventListener('abort', () => reject(new Error('上传已取消')));

            xhr.open('PUT', presigned_url);
            xhr.setRequestHeader('Content-Type', file.type);
            xhr.send(file);
        });

        videoUploadProgress.value = 95;

        // Step 3: Confirm upload with backend
        const confirmResponse = await fetch(`/api/v1/media/confirm-upload/${temp_upload_id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ s3_path }),
        });

        if (!confirmResponse.ok) {
            throw new Error('确认上传失败');
        }

        form.video_temp_upload_id = temp_upload_id;
        videoUploadProgress.value = 100;
    } catch (error) {
        console.error('Video upload failed:', error);
        uploadError.value = error instanceof Error ? error.message : '视频上传失败，请重试';
        removeVideoFile();
    } finally {
        isUploadingVideo.value = false;
    }
}

function removeVideoFile() {
    form.video_temp_upload_id = null;
    if (videoPreview.value) {
        URL.revokeObjectURL(videoPreview.value);
        videoPreview.value = null;
    }
    if (videoInput.value) {
        videoInput.value.value = '';
    }
    videoFileName.value = '';
    videoFileSize.value = 0;
    uploadError.value = null;
}

// Image upload functions (for cloud storage)
async function handleImageSelect(event: Event) {
    const target = event.target as HTMLInputElement;
    const files = target.files;

    if (files && files.length > 0) {
        // Limit to 12 images total
        const remainingSlots = 12 - uploadedImages.value.length;
        const filesToUpload = Array.from(files).slice(0, remainingSlots);

        for (const file of filesToUpload) {
            // Validate file
            if (!file.type.startsWith('image/')) {
                alert('请上传有效的图片文件');
                continue;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert(`图片 ${file.name} 太大！最大支持5MB`);
                continue;
            }

            // Add to uploaded images array
            const preview = URL.createObjectURL(file);
            const imageEntry = {
                tempUploadId: 0, // Will be set after upload
                preview,
                name: file.name,
                size: file.size,
                uploading: true,
                progress: 0,
                error: null,
            };
            uploadedImages.value.push(imageEntry);

            // Get the index of the newly added image
            const imageIndex = uploadedImages.value.length - 1;

            // Upload the image, passing the index
            await uploadImageToCloud(file, imageIndex);
        }

        // Clear the input
        if (imageInput.value) {
            imageInput.value.value = '';
        }
    }
}

async function uploadImageToCloud(file: File, imageIndex: number) {
    try {
        // Step 1: Get presigned URL
        const presignedResponse = await fetch('/api/v1/media/presigned-url', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                file_name: file.name,
                file_type: file.type,
                file_size: file.size,
                type: 'image',
            }),
        });

        if (!presignedResponse.ok) {
            throw new Error('获取上传链接失败');
        }

        const { presigned_url, temp_upload_id, s3_path } = await presignedResponse.json();

        // Step 2: Upload to cloud
        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                // Update the array element directly for reactivity
                uploadedImages.value[imageIndex].progress = Math.round((e.loaded / e.total) * 95);
            }
        });

        await new Promise((resolve, reject) => {
            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    resolve(xhr.response);
                } else {
                    reject(new Error('上传失败'));
                }
            });

            xhr.addEventListener('error', () => reject(new Error('网络错误')));
            xhr.addEventListener('abort', () => reject(new Error('上传已取消')));

            xhr.open('PUT', presigned_url);
            xhr.setRequestHeader('Content-Type', file.type);
            xhr.send(file);
        });

        uploadedImages.value[imageIndex].progress = 95;

        // Step 3: Confirm upload
        const confirmResponse = await fetch(`/api/v1/media/confirm-upload/${temp_upload_id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ s3_path }),
        });

        if (!confirmResponse.ok) {
            throw new Error('确认上传失败');
        }

        // Success! Update the array element directly
        uploadedImages.value[imageIndex].tempUploadId = temp_upload_id;
        uploadedImages.value[imageIndex].progress = 100;
        uploadedImages.value[imageIndex].uploading = false;

        // Add to form data
        form.image_temp_upload_ids.push(temp_upload_id);

    } catch (error) {
        console.error('Image upload failed:', error);
        // Update the array element directly
        uploadedImages.value[imageIndex].error = error instanceof Error ? error.message : '上传失败';
        uploadedImages.value[imageIndex].uploading = false;
    }
}

function removeImage(index: number) {
    const image = uploadedImages.value[index];

    // Remove from form data
    const tempIdIndex = form.image_temp_upload_ids.indexOf(image.tempUploadId);
    if (tempIdIndex > -1) {
        form.image_temp_upload_ids.splice(tempIdIndex, 1);
    }

    // Revoke preview URL
    URL.revokeObjectURL(image.preview);

    // Remove from array
    uploadedImages.value.splice(index, 1);
}

function validateContent() {
    // Check if post has content, images, or video
    const hasContent = form.content && form.content.trim().length > 0;

    // Check for cloud-uploaded images
    const hasCloudImages = uploadedImages.value.length > 0 || form.image_temp_upload_ids.length > 0;

    // Check for FilePond images (direct upload)
    let hasFilePondImages = false;
    if (pond.value) {
        // @ts-ignore - FilePond types
        const files = pond.value.getFiles();
        hasFilePondImages = files.length > 0;
    }

    const hasImages = hasCloudImages || hasFilePondImages;
    const hasVideo = form.video_temp_upload_id !== null;

    if (!hasContent && !hasImages && !hasVideo) {
        errorMessage.value = '帖子至少要有文字内容，图片或者视频任意一种。';
        showErrorDialog.value = true;
        return false;
    }
    return true;
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
    if (!validateContent()) {
        return;
    }

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

                                <!-- Content -->
                                <div>
                                    <Label for="content" class="text-white">内容</Label>
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
                                    <Label class="text-white">图片 (最多12张)</Label>

                                    <!-- Cloud Upload (Wasabi/S3) -->
                                    <div v-if="props.useCloudForImages" class="mt-2">
                                        <!-- Upload Button -->
                                        <div v-if="uploadedImages.length < 12">
                                            <label
                                                class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-[#4B5563] rounded-lg cursor-pointer hover:border-[#ff6e02] transition-colors bg-[#1c1c1c]"
                                            >
                                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                    <Image class="w-10 h-10 mb-3 text-[#999999]" />
                                                    <p class="mb-2 text-sm text-[#999999]">
                                                        <span class="font-semibold text-[#ff6e02]">点击上传图片</span>
                                                    </p>
                                                    <p class="text-xs text-[#999999]">支持 JPG, PNG, GIF, WebP (最大5MB) - 已上传 {{ uploadedImages.length }}/12</p>
                                                </div>
                                                <input
                                                    ref="imageInput"
                                                    type="file"
                                                    class="hidden"
                                                    accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                                                    multiple
                                                    @change="handleImageSelect"
                                                />
                                            </label>
                                        </div>

                                        <!-- Image Preview Grid -->
                                        <div v-if="uploadedImages.length > 0" class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-4">
                                            <div
                                                v-for="(image, index) in uploadedImages"
                                                :key="index"
                                                class="relative bg-[#1c1c1c] rounded-lg overflow-hidden aspect-square"
                                            >
                                                <!-- Image Preview -->
                                                <img
                                                    :src="image.preview"
                                                    :alt="image.name"
                                                    class="w-full h-full object-cover"
                                                />

                                                <!-- Remove Button -->
                                                <Button
                                                    v-if="!image.uploading"
                                                    type="button"
                                                    size="sm"
                                                    variant="ghost"
                                                    @click="removeImage(index)"
                                                    class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white p-1 h-auto"
                                                >
                                                    <X class="h-4 w-4" />
                                                </Button>

                                                <!-- Upload Progress Overlay -->
                                                <div
                                                    v-if="image.uploading"
                                                    class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center"
                                                >
                                                    <div class="w-3/4">
                                                        <div class="flex items-center justify-between text-xs text-white mb-1">
                                                            <span>上传中...</span>
                                                            <span>{{ image.progress }}%</span>
                                                        </div>
                                                        <div class="w-full bg-[#4B5563] rounded-full h-2">
                                                            <div
                                                                class="bg-[#ff6e02] h-2 rounded-full transition-all duration-300"
                                                                :style="{ width: `${image.progress}%` }"
                                                            ></div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Success Badge -->
                                                <div
                                                    v-if="!image.uploading && !image.error && image.tempUploadId"
                                                    class="absolute top-2 left-2 bg-green-500 text-white text-xs px-2 py-1 rounded"
                                                >
                                                    ✓ 已上传
                                                </div>

                                                <!-- Error Overlay -->
                                                <div
                                                    v-if="image.error"
                                                    class="absolute inset-0 bg-red-500/90 flex items-center justify-center p-2"
                                                >
                                                    <p class="text-xs text-white text-center">{{ image.error }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <p class="text-xs text-[#999999] mt-2">支持 JPG, PNG, GIF, WebP，单张最大5MB。</p>
                                    </div>

                                    <!-- Local Upload (FilePond) -->
                                    <div v-else class="mt-2">
                                        <FilePond
                                            ref="pond"
                                            name="images"
                                            :allow-multiple="true"
                                            :max-files="12"
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
                                        <p class="text-xs text-[#999999] mt-2">支持 JPG, PNG, GIF, WebP，单张最大5MB。图片将自动优化。</p>
                                    </div>

                                    <InputError :message="form.errors.images" class="mt-1" />
                                </div>

                                <!-- Video Upload -->
                                <div>
                                    <Label class="text-white">视频 (可选)</Label>

                                    <div v-if="!videoPreview" class="mt-2">
                                        <label
                                            class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-[#4B5563] rounded-lg cursor-pointer hover:border-[#ff6e02] transition-colors bg-[#1c1c1c]"
                                        >
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <Video class="w-10 h-10 mb-3 text-[#999999]" />
                                                <p class="mb-2 text-sm text-[#999999]">
                                                    <span class="font-semibold text-[#ff6e02]">点击上传视频</span>
                                                </p>
                                                <p class="text-xs text-[#999999]">支持 MP4, WebM, MOV (最大1GB)</p>
                                            </div>
                                            <input
                                                ref="videoInput"
                                                type="file"
                                                class="hidden"
                                                accept="video/*"
                                                @change="handleVideoUpload"
                                            />
                                        </label>
                                    </div>

                                    <!-- Video Preview -->
                                    <div v-if="videoPreview" class="mt-2">
                                        <div class="relative bg-[#1c1c1c] rounded-lg overflow-hidden">
                                            <video
                                                :src="videoPreview"
                                                controls
                                                class="w-full max-h-64 object-contain"
                                            ></video>
                                            <Button
                                                v-if="!isUploadingVideo"
                                                type="button"
                                                size="sm"
                                                variant="ghost"
                                                @click="removeVideoFile"
                                                class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white"
                                            >
                                                <X class="h-4 w-4" />
                                            </Button>
                                        </div>

                                        <!-- Upload Progress -->
                                        <div v-if="isUploadingVideo" class="mt-2">
                                            <div class="flex items-center justify-between text-xs text-[#999999] mb-1">
                                                <span>上传中...</span>
                                                <span>{{ videoUploadProgress }}%</span>
                                            </div>
                                            <div class="w-full bg-[#4B5563] rounded-full h-2">
                                                <div
                                                    class="bg-[#ff6e02] h-2 rounded-full transition-all duration-300"
                                                    :style="{ width: `${videoUploadProgress}%` }"
                                                ></div>
                                            </div>
                                        </div>

                                        <!-- File Info -->
                                        <p v-if="!isUploadingVideo && videoFileName" class="text-xs text-[#999999] mt-1">
                                            {{ videoFileName }} ({{ (videoFileSize / 1024 / 1024).toFixed(2) }}MB)
                                            <span v-if="form.video_temp_upload_id" class="text-green-400 ml-2">✓ 已上传</span>
                                        </p>

                                        <!-- Error Message -->
                                        <p v-if="uploadError" class="text-xs text-red-400 mt-1">
                                            {{ uploadError }}
                                        </p>
                                    </div>

                                    <InputError :message="form.errors.video" class="mt-1" />
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
                                <!-- Premium Content Toggle (Creators Only) -->
                                <div v-if="isCreator" class="space-y-4">
                                    <div class="flex items-start space-x-3 p-3 rounded-lg bg-[#1c1c1c] border border-[#4B5563]">
                                        <input
                                            id="is_premium"
                                            v-model="form.is_premium"
                                            type="checkbox"
                                            class="mt-1 w-4 h-4 text-[#ff6e02] bg-[#374151] border-[#4B5563] rounded focus:ring-[#ff6e02] focus:ring-2"
                                        />
                                        <div class="flex-1">
                                            <Label for="is_premium" class="text-white font-medium cursor-pointer">
                                                🔒 会员专享内容
                                            </Label>
                                            <p class="text-xs text-[#999999] mt-1">
                                                开启后，仅订阅你的用户可以查看此内容
                                            </p>
                                        </div>
                                    </div>
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

        <!-- Error Dialog -->
        <Dialog v-model:open="showErrorDialog">
            <DialogContent class="bg-[#1c1c1c] border-[#4B5563] text-white">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-red-400">
                        <AlertCircle class="h-5 w-5" />
                        错误
                    </DialogTitle>
                    <DialogDescription class="text-[#999999]">
                        {{ errorMessage }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button
                        @click="showErrorDialog = false"
                        class="bg-[#ff6e02] hover:bg-[#e55a00] text-white"
                    >
                        确定
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </WebLayout>
</template>