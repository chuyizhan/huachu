<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { Star, Users, MapPin, ExternalLink, Instagram, Twitter, Youtube, ChefHat } from 'lucide-vue-next';

interface Creator {
    id: number;
    display_name: string;
    bio: string;
    specialty: string;
    location?: string;
    experience_years: number;
    rating: number;
    follower_count: number;
    is_featured: boolean;
    verification_status: string;
    social_links?: {
        instagram?: string;
        twitter?: string;
        youtube?: string;
        website?: string;
    };
    user: {
        id: number;
        name: string;
    };
    posts_count?: number;
    avg_post_rating?: number;
}

interface Props {
    creator: Creator;
    variant?: 'default' | 'compact' | 'featured';
    showSocials?: boolean;
    showStats?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'default',
    showSocials: true,
    showStats: true,
});

const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const getVerificationColor = (status: string) => {
    const colors = {
        verified: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const getVerificationText = (status: string) => {
    const texts = {
        verified: '✓ Verified',
        pending: '⏳ Pending',
        rejected: '✗ Rejected',
    };
    return texts[status as keyof typeof texts] || status;
};

const getSocialIcon = (platform: string) => {
    const icons = {
        instagram: Instagram,
        twitter: Twitter,
        youtube: Youtube,
        website: ExternalLink,
    };
    return icons[platform as keyof typeof icons] || ExternalLink;
};
</script>

<template>
    <!-- Featured Variant -->
    <div v-if="variant === 'featured'" class="listclass hover:shadow-lg transition-all duration-200">
        <div class="text-center pb-3">
            <div class="flex justify-center mb-4">
                <Avatar class="h-20 w-20">
                    <AvatarFallback class="text-lg">
                        {{ getInitials(creator.display_name) }}
                    </AvatarFallback>
                </Avatar>
            </div>

            <div class="flex items-center justify-center gap-2 mb-2">
                <Badge v-if="creator.is_featured" variant="default" class="bg-amber-500">
                    <Star class="w-3 h-3 mr-1" />
                    Featured
                </Badge>
                <Badge :class="getVerificationColor(creator.verification_status)" variant="secondary">
                    {{ getVerificationText(creator.verification_status) }}
                </Badge>
            </div>

            <CardTitle class="text-xl">
                <Link :href="`/creators/${creator.id}`" class="hover:text-primary">
                    {{ creator.display_name }}
                </Link>
            </CardTitle>

            <CardDescription>
                {{ creator.specialty }}
            </CardDescription>

            <div v-if="showStats" class="flex items-center justify-center gap-4 mt-3 text-sm text-muted-foreground">
                <div class="flex items-center gap-1">
                    <Star class="w-4 h-4 fill-current text-yellow-500" />
                    {{ creator.rating }}/5
                </div>
                <div class="flex items-center gap-1">
                    <Users class="w-4 h-4" />
                    {{ creator.follower_count }}
                </div>
            </div>
        </div>

        <div class="space-y-4 u-m-t-20">
            <p class="text-sm text-muted-foreground line-clamp-3 text-center">
                {{ creator.bio }}
            </p>

            <div class="space-y-2 text-center">
                <div v-if="creator.location" class="flex items-center justify-center gap-2 text-sm text-muted-foreground">
                    <MapPin class="w-4 h-4" />
                    {{ creator.location }}
                </div>
                <div class="text-sm text-muted-foreground">
                    {{ creator.experience_years }} years experience
                </div>
                <div v-if="creator.posts_count" class="text-sm text-muted-foreground">
                    {{ creator.posts_count }} {{ creator.posts_count === 1 ? 'post' : 'posts' }}
                </div>
            </div>

            <!-- Social Links -->
            <div v-if="showSocials && creator.social_links && Object.keys(creator.social_links).length > 0" class="flex items-center justify-center gap-2">
                <template v-for="(url, platform) in creator.social_links" :key="platform">
                    <a
                        v-if="url"
                        :href="url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="p-2 rounded-lg hover:bg-muted transition-colors"
                    >
                        <component :is="getSocialIcon(platform)" class="w-4 h-4" />
                    </a>
                </template>
            </div>

            <Link
                :href="`/creators/${creator.id}`"
                class="bg-[#ff6e02] text-white px-6 py-2 rounded-40 font24 hover:bg-[#e55a00] transition-colors inline-block w-full text-center"
            >
                View Profile
            </Link>
        </div>
    </div>

    <!-- Compact Variant -->
    <div v-else-if="variant === 'compact'" class="listclass">
        <div class="p-4">
            <Link
                :href="`/creators/${creator.id}`"
                class="flex items-center gap-3 hover:bg-muted transition-colors rounded-lg p-2 -m-2"
            >
                <Avatar>
                    <AvatarFallback>
                        {{ getInitials(creator.display_name) }}
                    </AvatarFallback>
                </Avatar>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-sm">{{ creator.display_name }}</span>
                        <Badge v-if="creator.is_featured" variant="secondary" class="text-xs">
                            ⭐ Featured
                        </Badge>
                    </div>
                    <p class="text-xs text-muted-foreground line-clamp-1">
                        {{ creator.specialty }}
                    </p>
                    <div v-if="showStats" class="flex items-center gap-2 mt-1">
                        <span class="text-xs text-muted-foreground">
                            {{ creator.follower_count }} followers
                        </span>
                        <span class="text-xs">⭐ {{ creator.rating }}/5</span>
                    </div>
                </div>
            </Link>
        </div>
    </div>

    <!-- Default Variant - UniApp Style -->
    <div v-else class="listclass hover:shadow-lg transition-all duration-200">
        <div class="flex-y text-center">
            <div class="flex-xy-center u-m-b-20">
                <Avatar class="h-16 w-16">
                    <AvatarFallback class="text-lg bg-[#ff6e02] text-white">
                        {{ getInitials(creator.display_name) }}
                    </AvatarFallback>
                </Avatar>
            </div>

            <div class="flex-x-center justify-center gap-2 u-m-b-15">
                <Badge v-if="creator.is_featured" class="bg-[#ff6e02] text-white text-xs rounded-40">
                    <Star class="w-3 h-3 mr-1" />
                    博主
                </Badge>
                <Badge v-if="creator.verification_status === 'verified'" class="bg-[#ff6e02] text-white text-xs rounded-40">
                    ✓ 认证
                </Badge>
            </div>

            <h3 class="font32 colorfff u-m-b-10">
                <Link :href="`/creators/${creator.id}`" class="hover:text-primary">
                    {{ creator.display_name }}
                </Link>
            </h3>

            <p class="font24 color999 u-m-b-15">
                {{ creator.specialty }}
            </p>

            <div v-if="showStats" class="flex-x-center justify-center gap-4 u-m-b-15 font24 color999">
                <div class="flex-x-center gap-1">
                    <Star class="w-4 h-4 fill-current text-[#ff6e02]" />
                    <span>{{ creator.rating }}/5</span>
                </div>
                <div class="flex-x-center gap-1">
                    <Users class="w-4 h-4" />
                    <span>{{ creator.follower_count }}</span>
                </div>
            </div>

            <p class="font24 color999 line-clamp-2 u-m-b-15">
                {{ creator.bio }}
            </p>

            <div class="flex-y gap-2 u-m-b-15">
                <div v-if="creator.location" class="flex-x-center justify-center gap-2 font24 color999">
                    <MapPin class="w-4 h-4" />
                    <span>{{ creator.location }}</span>
                </div>
                <div class="font24 color999">
                    {{ creator.experience_years }} 年经验
                </div>
                <div v-if="creator.posts_count" class="font24 color999">
                    {{ creator.posts_count }} 个菜谱
                </div>
            </div>

            <!-- Social Links -->
            <div v-if="showSocials && creator.social_links && Object.keys(creator.social_links).length > 0" class="flex-x-center justify-center gap-2 u-m-b-15">
                <template v-for="(url, platform) in creator.social_links" :key="platform">
                    <a
                        v-if="url"
                        :href="url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="p-2 rounded-lg hover:bg-[#333333] transition-colors"
                    >
                        <component :is="getSocialIcon(platform)" class="w-4 h-4 text-[#ff6e02]" />
                    </a>
                </template>
            </div>

            <Link
                :href="`/creators/${creator.id}`"
                class="bg-[#ff6e02] text-white px-6 py-2 rounded-40 font24 hover:bg-[#e55a00] transition-colors inline-block"
            >
                查看主页
            </Link>
        </div>
    </div>
</template>