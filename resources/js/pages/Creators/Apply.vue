<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { useForm } from '@inertiajs/vue3';
import { CheckCircle, AlertCircle, Upload, ExternalLink } from 'lucide-vue-next';

interface Props {
    specialties: string[];
    hasExistingApplication?: boolean;
    existingApplication?: {
        display_name: string;
        bio: string;
        specialty: string;
        location: string;
        experience_years: number;
        portfolio_url: string;
        instagram_url: string;
        twitter_url: string;
        youtube_url: string;
        website_url: string;
        status: string;
    };
}

const props = defineProps<Props>();

const form = useForm({
    display_name: props.existingApplication?.display_name || '',
    bio: props.existingApplication?.bio || '',
    specialty: props.existingApplication?.specialty || '',
    location: props.existingApplication?.location || '',
    experience_years: props.existingApplication?.experience_years || 0,
    portfolio_url: props.existingApplication?.portfolio_url || '',
    instagram_url: props.existingApplication?.instagram_url || '',
    twitter_url: props.existingApplication?.twitter_url || '',
    youtube_url: props.existingApplication?.youtube_url || '',
    website_url: props.existingApplication?.website_url || '',
    portfolio_file: null as File | null,
    terms_accepted: false,
});

const submit = () => {
    form.post('/creators/apply', {
        preserveState: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.portfolio_file = target.files[0];
    }
};

const getStatusColor = (status: string) => {
    const colors = {
        pending: 'bg-yellow-100 border-yellow-200',
        approved: 'bg-green-100 border-green-200',
        rejected: 'bg-red-100 border-red-200',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 border-gray-200';
};

const getStatusIcon = (status: string) => {
    const icons = {
        pending: AlertCircle,
        approved: CheckCircle,
        rejected: AlertCircle,
    };
    return icons[status as keyof typeof icons] || AlertCircle;
};

const getStatusText = (status: string) => {
    const texts = {
        pending: 'Your creator application is currently under review. We\'ll notify you once a decision is made.',
        approved: 'Congratulations! Your creator application has been approved. You now have access to creator features.',
        rejected: 'Unfortunately, your creator application was not approved at this time. You can reapply after addressing the feedback.',
    };
    return texts[status as keyof typeof texts] || '';
};
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="text-center">
                <div class="flex justify-center mb-4">
                    <div class="text-6xl">👨‍🍳</div>
                </div>
                <h1 class="text-3xl font-bold mb-2">Apply to Become a Master Chef</h1>
                <p class="text-muted-foreground">
                    Join our community of verified culinary masters and share your culinary expertise with fellow food enthusiasts.
                </p>
            </div>

            <!-- Existing Application Status -->
            <Alert v-if="hasExistingApplication && existingApplication" :class="getStatusColor(existingApplication.status)">
                <component :is="getStatusIcon(existingApplication.status)" class="h-4 w-4" />
                <AlertDescription>
                    {{ getStatusText(existingApplication.status) }}
                </AlertDescription>
            </Alert>

            <!-- Benefits Section -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <div class="text-xl">✨</div>
                        Master Chef Benefits
                    </CardTitle>
                    <CardDescription>What you'll get as a verified culinary master</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-start gap-3">
                            <CheckCircle class="w-5 h-5 text-green-500 mt-0.5" />
                            <div>
                                <h4 class="font-semibold">Master Chef Badge</h4>
                                <p class="text-sm text-muted-foreground">Get a verified checkmark on your profile and recipes</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <CheckCircle class="w-5 h-5 text-green-500 mt-0.5" />
                            <div>
                                <h4 class="font-semibold">Premium Recipes</h4>
                                <p class="text-sm text-muted-foreground">Create and monetize exclusive culinary content</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <CheckCircle class="w-5 h-5 text-green-500 mt-0.5" />
                            <div>
                                <h4 class="font-semibold">Featured Chef</h4>
                                <p class="text-sm text-muted-foreground">Get featured on the community homepage</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <CheckCircle class="w-5 h-5 text-green-500 mt-0.5" />
                            <div>
                                <h4 class="font-semibold">Recipe Analytics</h4>
                                <p class="text-sm text-muted-foreground">Track your recipe performance and engagement</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Application Form -->
            <Card>
                <CardHeader>
                    <CardTitle>Application Form</CardTitle>
                    <CardDescription>Tell us about yourself and your expertise</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="display_name">Display Name *</Label>
                                <Input
                                    id="display_name"
                                    v-model="form.display_name"
                                    placeholder="Your professional name"
                                    :class="{ 'border-red-500': form.errors.display_name }"
                                />
                                <p v-if="form.errors.display_name" class="text-sm text-red-500">
                                    {{ form.errors.display_name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="specialty">Specialty *</Label>
                                <Select v-model="form.specialty">
                                    <SelectTrigger :class="{ 'border-red-500': form.errors.specialty }">
                                        <SelectValue placeholder="Select your specialty" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="specialty in specialties" :key="specialty" :value="specialty">
                                            {{ specialty }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.specialty" class="text-sm text-red-500">
                                    {{ form.errors.specialty }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="location">Location</Label>
                                <Input
                                    id="location"
                                    v-model="form.location"
                                    placeholder="City, Country"
                                    :class="{ 'border-red-500': form.errors.location }"
                                />
                                <p v-if="form.errors.location" class="text-sm text-red-500">
                                    {{ form.errors.location }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="experience_years">Years of Experience *</Label>
                                <Input
                                    id="experience_years"
                                    v-model="form.experience_years"
                                    type="number"
                                    min="0"
                                    max="50"
                                    placeholder="0"
                                    :class="{ 'border-red-500': form.errors.experience_years }"
                                />
                                <p v-if="form.errors.experience_years" class="text-sm text-red-500">
                                    {{ form.errors.experience_years }}
                                </p>
                            </div>
                        </div>

                        <!-- Bio -->
                        <div class="space-y-2">
                            <Label for="bio">Bio *</Label>
                            <Textarea
                                id="bio"
                                v-model="form.bio"
                                placeholder="Tell us about yourself, your background, and what makes you unique as a creator..."
                                class="min-h-[120px]"
                                :class="{ 'border-red-500': form.errors.bio }"
                            />
                            <p class="text-sm text-muted-foreground">
                                {{ form.bio.length }}/500 characters
                            </p>
                            <p v-if="form.errors.bio" class="text-sm text-red-500">
                                {{ form.errors.bio }}
                            </p>
                        </div>

                        <!-- Portfolio -->
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Portfolio & Work Samples</h3>
                                <p class="text-sm text-muted-foreground mb-4">
                                    Provide links to your work or upload a portfolio file to showcase your expertise.
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="portfolio_url">Portfolio URL</Label>
                                <Input
                                    id="portfolio_url"
                                    v-model="form.portfolio_url"
                                    type="url"
                                    placeholder="https://your-portfolio.com"
                                    :class="{ 'border-red-500': form.errors.portfolio_url }"
                                />
                                <p v-if="form.errors.portfolio_url" class="text-sm text-red-500">
                                    {{ form.errors.portfolio_url }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="portfolio_file">Portfolio File (Optional)</Label>
                                <div class="flex items-center gap-4">
                                    <Input
                                        id="portfolio_file"
                                        type="file"
                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                        @change="handleFileUpload"
                                        class="file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-primary-foreground hover:file:bg-primary/80"
                                    />
                                    <Upload class="w-4 h-4 text-muted-foreground" />
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    Accepted formats: PDF, DOC, DOCX, JPG, PNG. Max size: 10MB
                                </p>
                                <p v-if="form.errors.portfolio_file" class="text-sm text-red-500">
                                    {{ form.errors.portfolio_file }}
                                </p>
                            </div>
                        </div>

                        <!-- Social Media Links -->
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Social Media Presence</h3>
                                <p class="text-sm text-muted-foreground mb-4">
                                    Link your social media accounts to help us verify your online presence.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="instagram_url">Instagram</Label>
                                    <Input
                                        id="instagram_url"
                                        v-model="form.instagram_url"
                                        type="url"
                                        placeholder="https://instagram.com/username"
                                        :class="{ 'border-red-500': form.errors.instagram_url }"
                                    />
                                    <p v-if="form.errors.instagram_url" class="text-sm text-red-500">
                                        {{ form.errors.instagram_url }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="twitter_url">Twitter/X</Label>
                                    <Input
                                        id="twitter_url"
                                        v-model="form.twitter_url"
                                        type="url"
                                        placeholder="https://twitter.com/username"
                                        :class="{ 'border-red-500': form.errors.twitter_url }"
                                    />
                                    <p v-if="form.errors.twitter_url" class="text-sm text-red-500">
                                        {{ form.errors.twitter_url }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="youtube_url">YouTube</Label>
                                    <Input
                                        id="youtube_url"
                                        v-model="form.youtube_url"
                                        type="url"
                                        placeholder="https://youtube.com/channel/..."
                                        :class="{ 'border-red-500': form.errors.youtube_url }"
                                    />
                                    <p v-if="form.errors.youtube_url" class="text-sm text-red-500">
                                        {{ form.errors.youtube_url }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="website_url">Personal Website</Label>
                                    <Input
                                        id="website_url"
                                        v-model="form.website_url"
                                        type="url"
                                        placeholder="https://yourwebsite.com"
                                        :class="{ 'border-red-500': form.errors.website_url }"
                                    />
                                    <p v-if="form.errors.website_url" class="text-sm text-red-500">
                                        {{ form.errors.website_url }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="space-y-4">
                            <div class="flex items-start space-x-2">
                                <Checkbox
                                    id="terms"
                                    v-model:checked="form.terms_accepted"
                                    :class="{ 'border-red-500': form.errors.terms_accepted }"
                                />
                                <div class="grid gap-1.5 leading-none">
                                    <Label
                                        for="terms"
                                        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    >
                                        I agree to the Terms and Conditions *
                                    </Label>
                                    <p class="text-xs text-muted-foreground">
                                        By applying, you agree to our creator guidelines and community standards.
                                        <a href="/terms" target="_blank" class="text-primary hover:underline inline-flex items-center gap-1">
                                            Read Terms <ExternalLink class="w-3 h-3" />
                                        </a>
                                    </p>
                                </div>
                            </div>
                            <p v-if="form.errors.terms_accepted" class="text-sm text-red-500">
                                {{ form.errors.terms_accepted }}
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex gap-4 pt-4">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1"
                            >
                                <template v-if="form.processing">
                                    Submitting...
                                </template>
                                <template v-else>
                                    Submit Application
                                </template>
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                @click="form.reset()"
                                :disabled="form.processing"
                            >
                                Reset
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Application Process -->
            <Card>
                <CardHeader>
                    <CardTitle>What Happens Next?</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="flex items-center justify-center w-6 h-6 rounded-full bg-primary text-primary-foreground text-sm font-bold">
                                1
                            </div>
                            <div>
                                <h4 class="font-semibold">Review Process</h4>
                                <p class="text-sm text-muted-foreground">Our team will review your application and portfolio within 5-7 business days.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex items-center justify-center w-6 h-6 rounded-full bg-primary text-primary-foreground text-sm font-bold">
                                2
                            </div>
                            <div>
                                <h4 class="font-semibold">Verification</h4>
                                <p class="text-sm text-muted-foreground">We may reach out for additional information or samples of your work.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex items-center justify-center w-6 h-6 rounded-full bg-primary text-primary-foreground text-sm font-bold">
                                3
                            </div>
                            <div>
                                <h4 class="font-semibold">Decision</h4>
                                <p class="text-sm text-muted-foreground">You'll receive an email notification with our decision and next steps.</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>