<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Link, useForm } from '@inertiajs/vue3';
import { Check, Crown, Star, Users, Zap, Shield, Sparkles } from 'lucide-vue-next';

interface VipTier {
    id: number;
    name: string;
    description: string;
    price: number;
    duration_days: number;
    features: string[];
    badge_color: string;
    max_subscriptions: number | null;
    is_popular: boolean;
    is_available: boolean;
}

interface UserSubscription {
    id: number;
    vip_tier: VipTier;
    starts_at: string;
    expires_at: string;
    is_active: boolean;
    auto_renew: boolean;
}

interface Props {
    vipTiers: VipTier[];
    currentSubscription?: UserSubscription;
    isAuthenticated: boolean;
}

const props = defineProps<Props>();

const subscribeForm = useForm({
    vip_tier_id: null as number | null,
});

const subscribe = (tierId: number) => {
    subscribeForm.vip_tier_id = tierId;
    subscribeForm.post(`/vip/subscribe`, {
        preserveState: true,
    });
};

const formatPrice = (price: number) => {
    return `$${price.toFixed(2)}`;
};

const formatDuration = (days: number) => {
    if (days < 30) return `${days} days`;
    const months = Math.floor(days / 30);
    return `${months} month${months > 1 ? 's' : ''}`;
};

const getTierIcon = (tierName: string) => {
    const icons = {
        'Community Plus': Users,
        'Creator': Star,
        'Pro Creator': Crown,
    };
    return icons[tierName as keyof typeof icons] || Crown;
};

const getBadgeColor = (color: string) => {
    const colorMap = {
        silver: 'bg-gray-400',
        gold: 'bg-yellow-500',
        diamond: 'bg-blue-500',
    };
    return colorMap[color as keyof typeof colorMap] || 'bg-primary';
};

const isCurrentTier = (tierId: number) => {
    return props.currentSubscription?.vip_tier.id === tierId && props.currentSubscription?.is_active;
};

const canUpgrade = (tierId: number) => {
    if (!props.currentSubscription) return true;
    return tierId > props.currentSubscription.vip_tier.id;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
};
</script>

<template>
    <AppLayout>
        <div class="space-y-8">
            <!-- Hero Section -->
            <div class="text-center space-y-4">
                <div class="flex justify-center">
                    <Badge class="bg-gradient-to-r from-purple-500 to-pink-500 text-white px-4 py-2">
                        <Crown class="w-4 h-4 mr-2" />
                        VIP Membership
                    </Badge>
                </div>
                <h1 class="text-4xl font-bold">Unlock Premium Features</h1>
                <p class="text-xl text-muted-foreground max-w-2xl mx-auto">
                    Join our VIP community and get access to exclusive content, premium features, and direct creator access.
                </p>
            </div>

            <!-- Current Subscription Status -->
            <Card v-if="currentSubscription && currentSubscription.is_active" class="border-2 border-primary">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-full bg-primary/10">
                                <component :is="getTierIcon(currentSubscription.vip_tier.name)" class="w-6 h-6 text-primary" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold flex items-center gap-2">
                                    {{ currentSubscription.vip_tier.name }} Member
                                    <Badge :class="getBadgeColor(currentSubscription.vip_tier.badge_color)" class="text-white">
                                        Active
                                    </Badge>
                                </h3>
                                <p class="text-sm text-muted-foreground">
                                    Expires on {{ formatDate(currentSubscription.expires_at) }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <Button variant="outline" as-child>
                                <Link href="/vip/subscriptions">
                                    Manage Subscription
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- VIP Benefits Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card class="text-center">
                    <CardContent class="p-6">
                        <div class="w-12 h-12 mx-auto mb-4 p-3 rounded-full bg-purple-100">
                            <Crown class="w-6 h-6 text-purple-600" />
                        </div>
                        <h3 class="font-semibold mb-2">Exclusive Content</h3>
                        <p class="text-sm text-muted-foreground">
                            Access premium posts, tutorials, and behind-the-scenes content from top creators.
                        </p>
                    </CardContent>
                </Card>
                <Card class="text-center">
                    <CardContent class="p-6">
                        <div class="w-12 h-12 mx-auto mb-4 p-3 rounded-full bg-blue-100">
                            <Zap class="w-6 h-6 text-blue-600" />
                        </div>
                        <h3 class="font-semibold mb-2">Priority Support</h3>
                        <p class="text-sm text-muted-foreground">
                            Get faster response times and priority assistance from our support team.
                        </p>
                    </CardContent>
                </Card>
                <Card class="text-center">
                    <CardContent class="p-6">
                        <div class="w-12 h-12 mx-auto mb-4 p-3 rounded-full bg-green-100">
                            <Shield class="w-6 h-6 text-green-600" />
                        </div>
                        <h3 class="font-semibold mb-2">Ad-Free Experience</h3>
                        <p class="text-sm text-muted-foreground">
                            Enjoy the platform without any advertisements for a cleaner experience.
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- VIP Tiers -->
            <div>
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold mb-2">Choose Your Membership</h2>
                    <p class="text-muted-foreground">Select the plan that best fits your needs</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card
                        v-for="tier in vipTiers"
                        :key="tier.id"
                        :class="[
                            'relative transition-all duration-200',
                            tier.is_popular ? 'border-2 border-primary shadow-lg scale-105' : 'hover:shadow-lg',
                            isCurrentTier(tier.id) ? 'ring-2 ring-primary ring-offset-2' : ''
                        ]"
                    >
                        <!-- Popular Badge -->
                        <div
                            v-if="tier.is_popular"
                            class="absolute -top-3 left-1/2 transform -translate-x-1/2"
                        >
                            <Badge class="bg-primary text-primary-foreground px-4 py-1">
                                <Sparkles class="w-3 h-3 mr-1" />
                                Most Popular
                            </Badge>
                        </div>

                        <!-- Current Tier Badge -->
                        <div
                            v-if="isCurrentTier(tier.id)"
                            class="absolute -top-3 right-4"
                        >
                            <Badge class="bg-green-500 text-white px-3 py-1">
                                Current Plan
                            </Badge>
                        </div>

                        <CardHeader class="text-center">
                            <div class="flex justify-center mb-4">
                                <div :class="`p-3 rounded-full ${getBadgeColor(tier.badge_color)}/10`">
                                    <component
                                        :is="getTierIcon(tier.name)"
                                        :class="`w-8 h-8 ${getBadgeColor(tier.badge_color).replace('bg-', 'text-')}`"
                                    />
                                </div>
                            </div>
                            <CardTitle class="text-2xl">{{ tier.name }}</CardTitle>
                            <CardDescription class="text-base">{{ tier.description }}</CardDescription>
                            <div class="mt-4">
                                <span class="text-3xl font-bold">{{ formatPrice(tier.price) }}</span>
                                <span class="text-muted-foreground">/ {{ formatDuration(tier.duration_days) }}</span>
                            </div>
                        </CardHeader>

                        <CardContent class="space-y-4">
                            <!-- Features List -->
                            <div class="space-y-3">
                                <div
                                    v-for="feature in tier.features"
                                    :key="feature"
                                    class="flex items-start gap-3"
                                >
                                    <Check class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" />
                                    <span class="text-sm">{{ feature }}</span>
                                </div>
                            </div>

                            <!-- Subscription Limit -->
                            <div v-if="tier.max_subscriptions" class="text-xs text-muted-foreground text-center border-t pt-3">
                                Limited to {{ tier.max_subscriptions }} subscribers
                            </div>

                            <!-- Action Button -->
                            <div class="pt-4">
                                <Button
                                    v-if="!isAuthenticated"
                                    class="w-full"
                                    variant="outline"
                                    as-child
                                >
                                    <Link href="/login">
                                        Login to Subscribe
                                    </Link>
                                </Button>
                                <Button
                                    v-else-if="isCurrentTier(tier.id)"
                                    class="w-full"
                                    variant="secondary"
                                    disabled
                                >
                                    Current Plan
                                </Button>
                                <Button
                                    v-else-if="!tier.is_available"
                                    class="w-full"
                                    variant="outline"
                                    disabled
                                >
                                    Not Available
                                </Button>
                                <Button
                                    v-else-if="canUpgrade(tier.id)"
                                    class="w-full"
                                    :variant="tier.is_popular ? 'default' : 'outline'"
                                    @click="subscribe(tier.id)"
                                    :disabled="subscribeForm.processing"
                                >
                                    <template v-if="subscribeForm.processing && subscribeForm.vip_tier_id === tier.id">
                                        Processing...
                                    </template>
                                    <template v-else-if="currentSubscription">
                                        Upgrade to {{ tier.name }}
                                    </template>
                                    <template v-else>
                                        Subscribe Now
                                    </template>
                                </Button>
                                <Button
                                    v-else
                                    class="w-full"
                                    variant="outline"
                                    disabled
                                >
                                    Lower Tier
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- FAQ Section -->
            <Card>
                <CardHeader>
                    <CardTitle>Frequently Asked Questions</CardTitle>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-semibold mb-2">Can I cancel my subscription anytime?</h4>
                            <p class="text-sm text-muted-foreground">
                                Yes, you can cancel your subscription at any time. Your access will continue until the end of your billing period.
                            </p>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-2">What happens when I upgrade?</h4>
                            <p class="text-sm text-muted-foreground">
                                When you upgrade, you'll get immediate access to the new tier's features and only pay the prorated difference.
                            </p>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-2">Are there any setup fees?</h4>
                            <p class="text-sm text-muted-foreground">
                                No setup fees. You only pay the subscription price shown above.
                            </p>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-2">How do I access premium content?</h4>
                            <p class="text-sm text-muted-foreground">
                                Once subscribed, premium content will be automatically unlocked and marked with a crown icon.
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Support Section -->
            <Card>
                <CardContent class="p-8 text-center">
                    <h3 class="text-xl font-semibold mb-2">Need Help Choosing?</h3>
                    <p class="text-muted-foreground mb-4">
                        Our support team is here to help you find the perfect plan for your needs.
                    </p>
                    <Button variant="outline" as-child>
                        <Link href="/support">
                            Contact Support
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>