<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import Textarea from '@/components/ui/Textarea.vue'
import { MessageSquare, Reply, Trash2, Edit } from 'lucide-vue-next'
import { getInitials } from '@/composables/useInitials'

interface User {
    id: number
    name: string
    login_name: string
    avatar?: string
}

interface Comment {
    id: number
    user_id: number
    content: string
    created_at: string
    user: User
    replies?: Comment[]
}

interface Props {
    comments: Comment[]
    commentableType: string
    commentableId: number
}

const props = defineProps<Props>()
const page = usePage()
const currentUser = computed(() => page.props.auth?.user as User | undefined)

const newCommentForm = useForm({
    commentable_type: props.commentableType,
    commentable_id: props.commentableId,
    parent_id: null as number | null,
    content: '',
})

const replyingTo = ref<number | null>(null)
const editingComment = ref<number | null>(null)
const editContent = ref('')

const submitComment = () => {
    newCommentForm.post('/comments', {
        preserveScroll: true,
        onSuccess: () => {
            newCommentForm.reset('content')
            newCommentForm.parent_id = null
            replyingTo.value = null
        },
    })
}

const startReply = (commentId: number) => {
    replyingTo.value = commentId
    newCommentForm.parent_id = commentId
    newCommentForm.content = ''
}

const cancelReply = () => {
    replyingTo.value = null
    newCommentForm.parent_id = null
    newCommentForm.content = ''
}

const startEdit = (comment: Comment) => {
    editingComment.value = comment.id
    editContent.value = comment.content
}

const cancelEdit = () => {
    editingComment.value = null
    editContent.value = ''
}

const updateComment = (commentId: number) => {
    useForm({
        content: editContent.value,
    }).put(`/comments/${commentId}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingComment.value = null
            editContent.value = ''
        },
    })
}

const deleteComment = (commentId: number) => {
    if (confirm('确定要删除这条评论吗？')) {
        useForm({}).delete(`/comments/${commentId}`, {
            preserveScroll: true,
        })
    }
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('zh-CN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const canEditOrDelete = (comment: Comment) => {
    return currentUser.value && (currentUser.value.id === comment.user_id || currentUser.value.is_admin)
}
</script>

<template>
    <div class="mt-8">
        <h3 class="text-xl font-semibold text-white mb-4 flex items-center gap-2">
            <MessageSquare class="h-5 w-5" />
            评论 ({{ comments.length }})
        </h3>

        <!-- New Comment Form -->
        <div v-if="currentUser" class="mb-6">
            <form @submit.prevent="submitComment" class="space-y-3">
                <Textarea
                    v-model="newCommentForm.content"
                    @focus="newCommentForm.parent_id = null; replyingTo = null"
                    placeholder="写下你的评论..."
                    rows="3"
                    class="bg-[#374151] border-[#4B5563] text-white"
                    required
                />
                <div class="flex justify-end">
                    <Button
                        type="submit"
                        :disabled="newCommentForm.processing || !newCommentForm.content.trim()"
                        class="bg-[#ff6e02] hover:bg-orange-600"
                    >
                        {{ newCommentForm.processing ? '发布中...' : '发布评论' }}
                    </Button>
                </div>
            </form>
        </div>

        <div v-else class="mb-6 p-4 bg-[#374151] rounded-lg text-center">
            <p class="text-[#999999]">
                <a href="/login" class="text-[#ff6e02] hover:underline">登录</a> 后发表评论
            </p>
        </div>

        <!-- Comments List -->
        <div class="space-y-4">
            <div v-for="comment in comments" :key="comment.id" class="bg-[#374151] rounded-lg p-4">
                <!-- Comment Header -->
                <div class="flex items-start gap-3">
                    <Avatar class="h-10 w-10">
                        <AvatarImage v-if="comment.user.avatar" :src="comment.user.avatar" :alt="comment.user.name" />
                        <AvatarFallback class="bg-[#ff6e02] text-white">
                            {{ getInitials(comment.user.name) }}
                        </AvatarFallback>
                    </Avatar>

                    <div class="flex-1">
                        <!-- User Info -->
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-white">{{ comment.user.name }}</span>
                            <span class="text-xs text-[#999999]">@{{ comment.user.login_name }}</span>
                            <span class="text-xs text-[#999999]">{{ formatDate(comment.created_at) }}</span>
                        </div>

                        <!-- Comment Content or Edit Form -->
                        <div v-if="editingComment === comment.id" class="space-y-2">
                            <Textarea
                                v-model="editContent"
                                rows="3"
                                class="bg-[#4B5563] border-[#6B7280] text-white"
                            />
                            <div class="flex gap-2">
                                <Button size="sm" @click="updateComment(comment.id)" class="bg-[#ff6e02] hover:bg-orange-600">
                                    保存
                                </Button>
                                <Button size="sm" variant="outline" @click="cancelEdit" class="border-[#4B5563] bg-[#374151] text-white">
                                    取消
                                </Button>
                            </div>
                        </div>
                        <div v-else>
                            <p class="text-white mb-2">{{ comment.content }}</p>

                            <!-- Comment Actions -->
                            <div class="flex items-center gap-3 text-sm">
                                <button
                                    v-if="currentUser"
                                    @click="startReply(comment.id)"
                                    class="text-[#999999] hover:text-[#ff6e02] flex items-center gap-1"
                                >
                                    <Reply class="h-4 w-4" />
                                    回复
                                </button>
                                <button
                                    v-if="canEditOrDelete(comment)"
                                    @click="startEdit(comment)"
                                    class="text-[#999999] hover:text-[#ff6e02] flex items-center gap-1"
                                >
                                    <Edit class="h-4 w-4" />
                                    编辑
                                </button>
                                <button
                                    v-if="canEditOrDelete(comment)"
                                    @click="deleteComment(comment.id)"
                                    class="text-[#999999] hover:text-red-500 flex items-center gap-1"
                                >
                                    <Trash2 class="h-4 w-4" />
                                    删除
                                </button>
                            </div>
                        </div>

                        <!-- Reply Form -->
                        <div v-if="replyingTo === comment.id" class="mt-3">
                            <form @submit.prevent="submitComment" class="space-y-2">
                                <Textarea
                                    v-model="newCommentForm.content"
                                    placeholder="写下你的回复..."
                                    rows="2"
                                    class="bg-[#4B5563] border-[#6B7280] text-white"
                                    required
                                />
                                <div class="flex gap-2">
                                    <Button
                                        type="submit"
                                        size="sm"
                                        :disabled="newCommentForm.processing"
                                        class="bg-[#ff6e02] hover:bg-orange-600"
                                    >
                                        {{ newCommentForm.processing ? '回复中...' : '回复' }}
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        @click="cancelReply"
                                        class="border-[#4B5563] bg-[#374151] text-white"
                                    >
                                        取消
                                    </Button>
                                </div>
                            </form>
                        </div>

                        <!-- Replies -->
                        <div v-if="comment.replies && comment.replies.length > 0" class="mt-3 space-y-3">
                            <div
                                v-for="reply in comment.replies"
                                :key="reply.id"
                                class="bg-[#2d3748] rounded-lg p-3 ml-4 border-l-2 border-[#ff6e02]"
                            >
                                <div class="flex items-start gap-2">
                                    <Avatar class="h-8 w-8">
                                        <AvatarImage v-if="reply.user.avatar" :src="reply.user.avatar" :alt="reply.user.name" />
                                        <AvatarFallback class="bg-[#ff6e02] text-white text-xs">
                                            {{ getInitials(reply.user.name) }}
                                        </AvatarFallback>
                                    </Avatar>

                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-semibold text-white text-sm">{{ reply.user.name }}</span>
                                            <span class="text-xs text-[#999999]">{{ formatDate(reply.created_at) }}</span>
                                        </div>
                                        <p class="text-white text-sm">{{ reply.content }}</p>

                                        <!-- Reply Actions -->
                                        <div class="flex items-center gap-3 text-xs mt-2">
                                            <button
                                                v-if="canEditOrDelete(reply)"
                                                @click="deleteComment(reply.id)"
                                                class="text-[#999999] hover:text-red-500 flex items-center gap-1"
                                            >
                                                <Trash2 class="h-3 w-3" />
                                                删除
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="comments.length === 0" class="text-center py-8 text-[#999999]">
                还没有评论，来发表第一条评论吧！
            </div>
        </div>
    </div>
</template>
