<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Switch } from '@/components/ui/switch';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Link, useForm } from '@inertiajs/vue3';
import {
    Crown,
    Calendar,
    CreditCard,
    RefreshCw,
    X,
    AlertTriangle,
    CheckCircle,
    Star,
    Users
} from 'lucide-vue-next';

interface VipTier {
    id: number;
    name: string;
    description: string;
    price: number;
    duration_days: number;
    badge_color: string;
}

interface UserSubscription {
    id: number;
    vip_tier: VipTier;
    starts_at: string;
    expires_at: string;
    is_active: boolean;
    auto_renew: boolean;
    status: string;
    created_at: string;
}

interface Props {
    subscriptions: UserSubscription[];
    currentSubscription?: UserSubscription;
}

const props = defineProps<Props>();

const autoRenewForm = useForm({
    subscription_id: null as number | null,
    auto_renew: false,
});

const cancelForm = useForm({
    subscription_id: null as number | null,
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
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

const getStatusColor = (status: string) => {
    const colors = {
        active: 'bg-green-100 text-green-800',
        expired: 'bg-gray-100 text-gray-800',
        cancelled: 'bg-red-100 text-red-800',
        pending: 'bg-yellow-100 text-yellow-800',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const daysUntilExpiry = (expiryDate: string) => {
    const today = new Date();
    const expiry = new Date(expiryDate);
    const diffTime = expiry.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};

const toggleAutoRenew = (subscriptionId: number, currentValue: boolean) => {
    autoRenewForm.subscription_id = subscriptionId;
    autoRenewForm.auto_renew = !currentValue;
    autoRenewForm.patch(`/vip/subscriptions/${subscriptionId}/auto-renew`, {
        preserveState: true,
    });
};

const cancelSubscription = (subscriptionId: number) => {
    if (confirm('Are you sure you want to cancel this subscription? You will lose access when it expires.')) {
        cancelForm.subscription_id = subscriptionId;
        cancelForm.delete(`/vip/subscriptions/${subscriptionId}/cancel`, {
            preserveState: true,
        });
    }
};

const isExpiringSoon = (expiryDate: string) => {
    return daysUntilExpiry(expiryDate) <= 7;
};
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">My VIP Subscriptions</h1>
                    <p class="text-muted-foreground">Manage your VIP memberships and billing</p>
                </div>
                <Button as-child>
                    <Link href="/vip">
                        <Crown class="w-4 h-4 mr-2" />
                        Explore VIP Plans
                    </Link>
                </Button>
            </div>

            <!-- Current Subscription Card -->
            <div v-if="currentSubscription">
                <h2 class="text-xl font-semibold mb-4">Active Subscription</h2>
                <Card class="border-2 border-primary">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-full bg-primary/10">
                                    <component :is="getTierIcon(currentSubscription.vip_tier.name)" class="w-6 h-6 text-primary" />
                                </div>
                                <div>
                                    <CardTitle class="flex items-center gap-2">
                                        {{ currentSubscription.vip_tier.name }}
                                        <Badge :class="getBadgeColor(currentSubscription.vip_tier.badge_color)" class="text-white">
                                            {{ currentSubscription.vip_tier.badge_color }}
                                        </Badge>
                                    </CardTitle>
                                    <CardDescription>{{ currentSubscription.vip_tier.description }}</CardDescription>
                                </div>
                            </div>
                            <Badge :class="getStatusColor(currentSubscription.status)" variant="secondary">
                                {{ currentSubscription.status }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Subscription Info -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex items-center gap-2">
                                <CreditCard class="w-4 h-4 text-muted-foreground" />
                                <div>
                                    <div class="text-sm font-medium">{{ formatPrice(currentSubscription.vip_tier.price) }}</div>
                                    <div class="text-xs text-muted-foreground">per {{ formatDuration(currentSubscription.vip_tier.duration_days) }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Calendar class="w-4 h-4 text-muted-foreground" />
                                <div>
                                    <div class="text-sm font-medium">{{ formatDate(currentSubscription.expires_at) }}</div>
                                    <div class="text-xs text-muted-foreground">Expires</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <RefreshCw class="w-4 h-4 text-muted-foreground" />
                                <div>
                                    <div class="text-sm font-medium">{{ currentSubscription.auto_renew ? 'Enabled' : 'Disabled' }}</div>
                                    <div class="text-xs text-muted-foreground">Auto-renewal</div>
                                </div>
                            </div>
                        </div>

                        <!-- Expiry Warning -->
                        <Alert v-if="isExpiringSoon(currentSubscription.expires_at) && !currentSubscription.auto_renew" class="border-yellow-200 bg-yellow-50">
                            <AlertTriangle class="h-4 w-4 text-yellow-600" />
                            <AlertDescription class="text-yellow-800">
                                Your subscription expires in {{ daysUntilExpiry(currentSubscription.expires_at) }} day(s).
                                Consider enabling auto-renewal to maintain uninterrupted access.
                            </AlertDescription>
                        </Alert>

                        <!-- Controls -->
                        <div class="flex items-center justify-between pt-4 border-t">
                            <div class="flex items-center gap-3">
                                <Switch
                                    :checked="currentSubscription.auto_renew"
                                    @update:checked="() => toggleAutoRenew(currentSubscription.id, currentSubscription.auto_renew)"
                                    :disabled="autoRenewForm.processing && autoRenewForm.subscription_id === currentSubscription.id"
                                />
                                <div>
                                    <div class="text-sm font-medium">Auto-renewal</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ currentSubscription.auto_renew ? 'Your subscription will renew automatically' : 'Enable to avoid service interruption' }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Button variant="outline" as-child>
                                    <Link href="/vip">Upgrade Plan</Link>
                                </Button>
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    @click="cancelSubscription(currentSubscription.id)"
                                    :disabled="cancelForm.processing && cancelForm.subscription_id === currentSubscription.id"
                                >
                                    <X class="w-4 h-4 mr-1" />
                                    Cancel
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- No Active Subscription -->
            <div v-if="!currentSubscription" class="text-center py-12">
                <Crown class="w-16 h-16 mx-auto text-muted-foreground mb-4" />
                <h3 class="text-lg font-semibold mb-2">No Active VIP Subscription</h3>
                <p class="text-muted-foreground mb-4">
                    Join our VIP community to unlock exclusive content and premium features.
                </p>
                <Button as-child>
                    <Link href="/vip">Browse VIP Plans</Link>
                </Button>
            </div>

            <!-- Subscription History -->
            <div v-if="subscriptions.length > 0">
                <h2 class="text-xl font-semibold mb-4">Subscription History</h2>
                <div class="space-y-3">
                    <Card v-for="subscription in subscriptions" :key="subscription.id">
                        <CardContent class="p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-full bg-muted">
                                        <component :is="getTierIcon(subscription.vip_tier.name)" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ subscription.vip_tier.name }}</div>
                                        <div class="text-sm text-muted-foreground">
                                            {{ formatDate(subscription.starts_at) }} - {{ formatDate(subscription.expires_at) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <div class="font-medium">{{ formatPrice(subscription.vip_tier.price) }}</div>
                                        <div class="text-xs text-muted-foreground">{{ formatDuration(subscription.vip_tier.duration_days) }}</div>
                                    </div>
                                    <Badge :class="getStatusColor(subscription.status)" variant="secondary">
                                        {{ subscription.status }}
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Billing Information -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <CreditCard class="w-5 h-5" />
                        Billing Information
                    </CardTitle>
                    <CardDescription>Manage your payment methods and billing details</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <Alert>
                        <CheckCircle class="h-4 w-4" />
                        <AlertDescription>
                            All payments are processed securely through our encrypted payment system.
                            We never store your full credit card information.
                        </AlertDescription>
                    </Alert>
                    <div class="flex gap-3">
                        <Button variant="outline" disabled>
                            <CreditCard class="w-4 h-4 mr-2" />
                            Manage Payment Methods
                        </Button>
                        <Button variant="outline" disabled>
                            Download Invoices
                        </Button>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Payment method management and invoice downloads will be available soon.
                    </p>
                </CardContent>
            </Card>

            <!-- Help Section -->
            <Card>
                <CardHeader>
                    <CardTitle>Need Help?</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-medium mb-2">Common Questions</h4>
                            <ul class="space-y-1 text-sm text-muted-foreground">
                                <li>• How to cancel my subscription</li>
                                <li>• Understanding auto-renewal</li>
                                <li>• Upgrading or downgrading plans</li>
                                <li>• Refund policy</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-medium mb-2">Get Support</h4>
                            <div class="space-y-2">
                                <Button variant="outline" size="sm" as-child>
                                    <Link href="/help/vip">VIP Help Center</Link>
                                </Button>
                                <Button variant="outline" size="sm" as-child>
                                    <Link href="/support">Contact Support</Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>