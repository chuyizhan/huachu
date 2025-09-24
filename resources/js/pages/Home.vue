<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Link } from '@inertiajs/vue3';
import {
    ChefHat,
    Users,
    BookOpen,
    Star,
    ArrowRight,
    Crown,
    Heart,
    Eye,
    MessageSquare,
    Award,
    Utensils,
    Clock,
    TrendingUp
} from 'lucide-vue-next';
import PostCard from '@/components/PostCard.vue';
import CreatorCard from '@/components/CreatorCard.vue';

interface Post {
    id: number;
    title: string;
    excerpt: string;
    slug: string;
    user: {
        id: number;
        name: string;
        creator_profile?: {
            display_name: string;
            specialty: string;
            verification_status: string;
        };
    };
    category: {
        id: number;
        name: string;
        color: string;
    };
    type: string;
    view_count: number;
    like_count: number;
    is_premium: boolean;
    published_at: string;
}

interface Creator {
    id: number;
    display_name: string;
    bio: string;
    specialty: string;
    rating: number;
    follower_count: number;
    is_featured: boolean;
    verification_status: string;
    user: {
        id: number;
        name: string;
    };
}

interface Category {
    id: number;
    name: string;
    slug: string;
    color: string;
    icon: string;
    posts_count: number;
}

interface Testimonial {
    id: number;
    content: string;
    author: string;
    rating: number;
    specialty: string;
}

interface Stats {
    total_recipes: number;
    total_chefs: number;
    total_cuisines: number;
    total_members: number;
}

interface Props {
    featuredPosts: Post[];
    recentPosts: Post[];
    featuredCreators: Creator[];
    popularCategories: Category[];
    stats: Stats;
    testimonials: Testimonial[];
}

defineProps<Props>();

const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const features = [
    {
        icon: BookOpen,
        title: 'Discover Recipes',
        description: 'Explore thousands of recipes from professional chefs and home cooks worldwide.'
    },
    {
        icon: Users,
        title: 'Learn from Masters',
        description: 'Connect with verified culinary experts and learn their secret techniques.'
    },
    {
        icon: Crown,
        title: 'Premium Content',
        description: 'Access exclusive recipes and advanced cooking tutorials with VIP membership.'
    },
    {
        icon: Award,
        title: 'Build Your Reputation',
        description: 'Share your recipes, gain followers, and become a recognized chef in the community.'
    }
];
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-orange-50 to-white">
        <!-- Hero Section -->
        <section class="relative overflow-hidden bg-gradient-to-r from-orange-500 via-red-500 to-pink-500 text-white">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-8 right-8 text-8xl animate-pulse">🍳</div>
                <div class="absolute top-32 right-32 text-6xl animate-bounce">🥘</div>
                <div class="absolute bottom-8 left-8 text-7xl animate-pulse">👨‍🍳</div>
                <div class="absolute bottom-32 left-32 text-5xl animate-bounce">🍽️</div>
                <div class="absolute top-64 left-1/2 text-4xl animate-pulse">🔥</div>
            </div>

            <div class="relative z-10 px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
                <div class="max-w-7xl mx-auto text-center">
                    <div class="flex justify-center mb-6">
                        <div class="text-8xl animate-bounce">🍳</div>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-7xl font-bold mb-6 leading-tight">
                        Welcome to the<br>
                        <span class="text-yellow-300">Culinary Universe</span>
                    </h1>

                    <p class="text-xl sm:text-2xl mb-8 text-orange-100 max-w-3xl mx-auto leading-relaxed">
                        Discover amazing recipes, learn from master chefs, and share your culinary creations
                        with a passionate community of food lovers.
                    </p>

                    <!-- Hero Stats -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-12 max-w-4xl mx-auto">
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                            <div class="text-2xl font-bold">{{ stats.total_recipes.toLocaleString() }}+</div>
                            <div class="text-sm text-orange-200">Recipes</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                            <div class="text-2xl font-bold">{{ stats.total_chefs.toLocaleString() }}+</div>
                            <div class="text-sm text-orange-200">Master Chefs</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                            <div class="text-2xl font-bold">{{ stats.total_cuisines }}+</div>
                            <div class="text-sm text-orange-200">Cuisines</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                            <div class="text-2xl font-bold">{{ stats.total_members.toLocaleString() }}+</div>
                            <div class="text-sm text-orange-200">Members</div>
                        </div>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Button size="lg" class="bg-white text-orange-600 hover:bg-orange-50 text-lg px-8 py-4" as-child>
                            <Link href="/community">
                                <Utensils class="w-5 h-5 mr-2" />
                                Explore Recipes
                            </Link>
                        </Button>
                        <Button
                            size="lg"
                            variant="outline"
                            class="text-white border-white hover:bg-white hover:text-orange-600 text-lg px-8 py-4"
                            as-child
                        >
                            <Link href="/creators/apply">
                                <ChefHat class="w-5 h-5 mr-2" />
                                Join as Chef
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16">
                    <div class="text-4xl mb-4">✨</div>
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                        Why Join Our Culinary Community?
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Experience the perfect blend of tradition and innovation in our thriving culinary ecosystem.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <Card v-for="feature in features" :key="feature.title" class="text-center hover:shadow-lg transition-shadow">
                        <CardHeader>
                            <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-r from-orange-400 to-red-400 rounded-full flex items-center justify-center">
                                <component :is="feature.icon" class="w-8 h-8 text-white" />
                            </div>
                            <CardTitle class="text-xl">{{ feature.title }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <CardDescription class="text-gray-600">
                                {{ feature.description }}
                            </CardDescription>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- Featured Recipes Section -->
        <section v-if="featuredPosts.length > 0" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-12">
                    <div class="text-center flex-1">
                        <div class="text-4xl mb-4">🔥</div>
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                            Featured Recipes
                        </h2>
                        <p class="text-lg text-gray-600">
                            Discover the most popular and trending recipes from our community
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <PostCard
                        v-for="post in featuredPosts.slice(0, 6)"
                        :key="post.id"
                        :post="post"
                        variant="featured"
                    />
                </div>

                <div class="text-center mt-12">
                    <Button size="lg" variant="outline" as-child>
                        <Link href="/community/posts">
                            View All Recipes
                            <ArrowRight class="w-4 h-4 ml-2" />
                        </Link>
                    </Button>
                </div>
            </div>
        </section>

        <!-- Popular Cuisines Section -->
        <section v-if="popularCategories.length > 0" class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12">
                    <div class="text-4xl mb-4">🌍</div>
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                        Explore World Cuisines
                    </h2>
                    <p class="text-lg text-gray-600">
                        Journey through diverse culinary traditions from around the globe
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <Link
                        v-for="category in popularCategories"
                        :key="category.id"
                        :href="`/community/posts?category=${category.slug}`"
                        class="group"
                    >
                        <Card class="text-center hover:shadow-lg transition-all duration-200 group-hover:scale-105">
                            <CardContent class="p-6">
                                <div class="text-4xl mb-3">{{ category.icon || '🍽️' }}</div>
                                <h3 class="font-semibold text-lg mb-2">{{ category.name }}</h3>
                                <Badge
                                    :style="{ backgroundColor: category.color }"
                                    class="text-white text-xs"
                                >
                                    {{ category.posts_count }} recipes
                                </Badge>
                            </CardContent>
                        </Card>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Featured Chefs Section -->
        <section v-if="featuredCreators.length > 0" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12">
                    <div class="text-4xl mb-4">👨‍🍳</div>
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                        Meet Our Master Chefs
                    </h2>
                    <p class="text-lg text-gray-600">
                        Learn from verified culinary experts and industry professionals
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <CreatorCard
                        v-for="creator in featuredCreators"
                        :key="creator.id"
                        :creator="creator"
                        variant="featured"
                    />
                </div>

                <div class="text-center mt-12">
                    <Button size="lg" variant="outline" as-child>
                        <Link href="/community/creators">
                            Meet All Chefs
                            <ArrowRight class="w-4 h-4 ml-2" />
                        </Link>
                    </Button>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section v-if="testimonials.length > 0" class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12">
                    <div class="text-4xl mb-4">💬</div>
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                        What Our Community Says
                    </h2>
                    <p class="text-lg text-gray-600">
                        Real experiences from our passionate food community
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <Card v-for="testimonial in testimonials" :key="testimonial.id" class="text-center">
                        <CardContent class="p-8">
                            <div class="flex justify-center mb-4">
                                <div class="flex gap-1">
                                    <Star
                                        v-for="i in 5"
                                        :key="i"
                                        class="w-5 h-5"
                                        :class="i <= testimonial.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'"
                                    />
                                </div>
                            </div>
                            <p class="text-gray-600 mb-6 italic">
                                "{{ testimonial.content }}"
                            </p>
                            <div>
                                <div class="font-semibold text-gray-900">{{ testimonial.author }}</div>
                                <div class="text-sm text-gray-500">{{ testimonial.specialty }}</div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-orange-500 to-red-500 text-white">
            <div class="max-w-4xl mx-auto text-center">
                <div class="text-6xl mb-6">🚀</div>
                <h2 class="text-3xl sm:text-4xl font-bold mb-6">
                    Ready to Start Your Culinary Journey?
                </h2>
                <p class="text-xl mb-8 text-orange-100">
                    Join thousands of food enthusiasts and master chefs in our vibrant community.
                    Share recipes, learn techniques, and connect with fellow culinary artists.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Button size="lg" class="bg-white text-orange-600 hover:bg-orange-50 text-lg px-8 py-4" as-child>
                        <Link href="/register">
                            <Users class="w-5 h-5 mr-2" />
                            Join the Community
                        </Link>
                    </Button>
                    <Button
                        size="lg"
                        variant="outline"
                        class="text-white border-white hover:bg-white hover:text-orange-600 text-lg px-8 py-4"
                        as-child
                    >
                        <Link href="/vip">
                            <Crown class="w-5 h-5 mr-2" />
                            Go Premium
                        </Link>
                    </Button>
                </div>
            </div>
        </section>

        <!-- Recent Activity Section -->
        <section v-if="recentPosts.length > 0" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12">
                    <div class="text-4xl mb-4">🆕</div>
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                        Latest from the Kitchen
                    </h2>
                    <p class="text-lg text-gray-600">
                        Fresh recipes and culinary insights from our community
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <PostCard
                        v-for="post in recentPosts.slice(0, 8)"
                        :key="post.id"
                        :post="post"
                        variant="compact"
                    />
                </div>
            </div>
        </section>
    </div>
</template>