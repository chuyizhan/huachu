<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Link, router } from '@inertiajs/vue3';
import { Search, Star, Users, MapPin, ExternalLink, Instagram, Twitter, Youtube, ChefHat } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Creator {
    id: number;
    display_name: string;
    bio: string;
    specialty: string;
    location: string;
    experience_years: number;
    rating: number;
    follower_count: number;
    is_featured: boolean;
    verification_status: string;
    social_links: {
        instagram?: string;
        twitter?: string;
        youtube?: string;
        website?: string;
    };
    user: {
        id: number;
        name: string;
    };
    posts_count: number;
    avg_post_rating: number;
}

interface PaginatedCreators {
    data: Creator[];
    links: any[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

interface Props {
    creators: PaginatedCreators;
    filters: {
        specialty?: string;
        location?: string;
        search?: string;
        featured?: string;
        verified?: string;
    };
    specialties: string[];
    locations: string[];
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const selectedSpecialty = ref(props.filters.specialty || '');
const selectedLocation = ref(props.filters.location || '');
const selectedFeatured = ref(props.filters.featured || '');
const selectedVerified = ref(props.filters.verified || '');

const featuredOptions = [
    { value: '', label: 'All Creators' },
    { value: '1', label: 'Featured Only' },
];

const verifiedOptions = [
    { value: '', label: 'All Status' },
    { value: 'verified', label: 'Verified Only' },
    { value: 'pending', label: 'Pending' },
];

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

const applyFilters = () => {
    const params = new URLSearchParams();

    if (searchQuery.value) params.set('search', searchQuery.value);
    if (selectedSpecialty.value) params.set('specialty', selectedSpecialty.value);
    if (selectedLocation.value) params.set('location', selectedLocation.value);
    if (selectedFeatured.value) params.set('featured', selectedFeatured.value);
    if (selectedVerified.value) params.set('verified', selectedVerified.value);

    const queryString = params.toString();
    const url = queryString ? `/community/creators?${queryString}` : '/community/creators';

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedSpecialty.value = '';
    selectedLocation.value = '';
    selectedFeatured.value = '';
    selectedVerified.value = '';
    router.visit('/community/creators', {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

watch([searchQuery, selectedSpecialty, selectedLocation, selectedFeatured, selectedVerified], () => {
    applyFilters();
}, { debounce: 300 });
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="text-3xl">👨‍🍳</div>
                        <h1 class="text-3xl font-bold">Master Chefs</h1>
                    </div>
                    <p class="text-muted-foreground">
                        Discover talented culinary artists and connect with professional chefs
                    </p>
                </div>
                <Button class="bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600" as-child>
                    <Link href="/creators/apply">
                        <ChefHat class="w-4 h-4 mr-2" />
                        Join as Chef
                    </Link>
                </Button>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="p-6">
                    <div class="flex flex-col lg:flex-row gap-4">
                        <!-- Search -->
                        <div class="flex-1">
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                                <Input
                                    v-model="searchQuery"
                                    placeholder="Search chefs..."
                                    class="pl-10"
                                />
                            </div>
                        </div>

                        <!-- Specialty Filter -->
                        <Select v-model="selectedSpecialty">
                            <SelectTrigger class="w-full lg:w-[200px]">
                                <SelectValue placeholder="All Specialties" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Specialties</SelectItem>
                                <SelectItem v-for="specialty in specialties" :key="specialty" :value="specialty">
                                    {{ specialty }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <!-- Location Filter -->
                        <Select v-model="selectedLocation">
                            <SelectTrigger class="w-full lg:w-[200px]">
                                <SelectValue placeholder="All Locations" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">All Locations</SelectItem>
                                <SelectItem v-for="location in locations" :key="location" :value="location">
                                    {{ location }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <!-- Featured Filter -->
                        <Select v-model="selectedFeatured">
                            <SelectTrigger class="w-full lg:w-[150px]">
                                <SelectValue placeholder="All Creators" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="option in featuredOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <!-- Verified Filter -->
                        <Select v-model="selectedVerified">
                            <SelectTrigger class="w-full lg:w-[150px]">
                                <SelectValue placeholder="All Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="option in verifiedOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <!-- Clear Filters -->
                        <Button
                            variant="outline"
                            @click="clearFilters"
                            :disabled="!searchQuery && !selectedSpecialty && !selectedLocation && !selectedFeatured && !selectedVerified"
                        >
                            Clear
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Results Count -->
            <div class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    Showing {{ creators.meta.total }} {{ creators.meta.total === 1 ? 'chef' : 'chefs' }}
                </p>
                <div class="text-sm text-muted-foreground">
                    Page {{ creators.meta.current_page }} of {{ creators.meta.last_page }}
                </div>
            </div>

            <!-- Creators Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Card v-for="creator in creators.data" :key="creator.id" class="hover:shadow-lg transition-all duration-200">
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

                        <div class="flex items-center justify-center gap-4 mt-3 text-sm text-muted-foreground">
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
                            <div class="text-sm text-muted-foreground">
                                {{ creator.posts_count }} {{ creator.posts_count === 1 ? 'post' : 'posts' }}
                            </div>
                        </div>

                        <!-- Social Links -->
                        <div v-if="Object.keys(creator.social_links).length > 0" class="flex items-center gap-2">
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
            </div>

            <!-- Empty State -->
            <div v-if="creators.data.length === 0" class="text-center py-12">
                <div class="text-6xl mb-4">👨‍🍳</div>
                <h3 class="text-lg font-semibold mb-2">No chefs found</h3>
                <p class="text-muted-foreground mb-4">
                    Try adjusting your filters or be the first to join!
                </p>
                <Button class="bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600" as-child>
                    <Link href="/creators/apply">Join as Chef</Link>
                </Button>
            </div>

            <!-- Pagination -->
            <div v-if="creators.meta.last_page > 1" class="flex items-center justify-center gap-2">
                <Button
                    v-for="link in creators.links"
                    :key="link.label"
                    :variant="link.active ? 'default' : 'outline'"
                    :disabled="!link.url"
                    size="sm"
                    @click="link.url && router.visit(link.url, { preserveState: true })"
                    v-html="link.label"
                />
            </div>
        </div>
    </AppLayout>
</template>