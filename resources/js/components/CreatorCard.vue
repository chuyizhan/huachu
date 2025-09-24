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
    <Card v-if="variant === 'featured'" class="hover:shadow-lg transition-all duration-200">
        <CardHeader class="text-center pb-3">
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
        </CardHeader>

        <CardContent class="space-y-4">
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

            <Button class="w-full" variant="outline" as-child>
                <Link :href="`/creators/${creator.id}`">
                    View Profile
                </Link>
            </Button>
        </CardContent>
    </Card>

    <!-- Compact Variant -->
    <Card v-else-if="variant === 'compact'">
        <CardContent class="p-4">
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
        </CardContent>
    </Card>

    <!-- Default Variant -->
    <Card v-else class="hover:shadow-lg transition-all duration-200">
        <CardHeader class="text-center pb-3">
            <div class="flex justify-center mb-4">
                <Avatar class="h-16 w-16">
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

            <CardTitle>
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
        </CardHeader>

        <CardContent class="space-y-4">
            <p class="text-sm text-muted-foreground line-clamp-3">
                {{ creator.bio }}
            </p>

            <div class="space-y-2">
                <div v-if="creator.location" class="flex items-center gap-2 text-sm text-muted-foreground">
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
            <div v-if="showSocials && creator.social_links && Object.keys(creator.social_links).length > 0" class="flex items-center gap-2">
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

            <Button class="w-full" variant="outline" as-child>
                <Link :href="`/creators/${creator.id}`">
                    View Profile
                </Link>
            </Button>
        </CardContent>
    </Card>
</template>