# Project Updates

## 2025-10-22

### Points System - Category-Specific Rewards

#### Summary
Implemented a category-specific points reward system where users earn points for publishing posts with a minimum number of images. Points awarded and image requirements vary by category.

#### Changes Made

**1. Database Migration**
- Created migration: `2025_10_22_041112_add_points_settings_to_post_categories_table.php`
- Added columns to `post_categories` table:
  - `points_reward` (integer, default: 0) - Points awarded for posts in this category
  - `minimum_images` (integer, default: 0) - Minimum images required to earn points

**2. Models Updated**

**PostCategory Model** (`app/Models/PostCategory.php`)
- Added `points_reward` and `minimum_images` to fillable fields
- Added proper integer casting for new fields

**User Model** (`app/Models/User.php`)
- Added `awardPoints()` method at line 331
- Method parameters:
  - `int $points` - Amount of points to award
  - `string $reason` - Reason for awarding points
  - `Model|null $related` - Related model (e.g., Post)
  - `array $metadata` - Additional metadata
- Automatically creates PointTransaction record with full audit trail

**3. Configuration**

**Config File** (`config/points.php`) - NEW
```php
'post_creation' => [
    'enabled' => env('POINTS_POST_CREATION_ENABLED', true),
    'default_points' => env('POINTS_POST_CREATION_DEFAULT', 10),
    'default_minimum_images' => env('POINTS_POST_CREATION_DEFAULT_MIN_IMAGES', 5),
]
```

**4. Controller Logic**

**PostController** (`app/Http/Controllers/PostController.php:213-240`)
- Added points awarding logic in `store()` method
- Checks category-specific settings first, falls back to config defaults
- Only awards points if:
  - Post status is 'published'
  - Points system is enabled in config
  - Category has `points_reward > 0`
  - Post meets minimum image requirement
- Transaction metadata includes:
  - `image_count` - Number of images in the post
  - `minimum_required` - Minimum images required
  - `category_id` - Category ID
  - `category_name` - Category name

#### How It Works

1. Admin configures `points_reward` and `minimum_images` for each category in database
2. When user publishes a post:
   - System checks if post category has points enabled (`points_reward > 0`)
   - Counts uploaded images via Media Library
   - If images >= minimum requirement, awards points to user
   - Creates PointTransaction record with full details
   - Updates user's points balance

#### Environment Variables

```env
POINTS_POST_CREATION_ENABLED=true
POINTS_POST_CREATION_DEFAULT=10
POINTS_POST_CREATION_DEFAULT_MIN_IMAGES=5
```

#### Example Usage

```php
// Set category points (in admin panel or seeder)
$category->update([
    'points_reward' => 20,
    'minimum_images' => 3,
]);

// Disable points for a category
$category->update(['points_reward' => 0]);

// Check user's points
$user->points; // Returns total points

// View point transactions
$user->pointTransactions()->latest()->get();
```

---

### UI Improvements - Post Image Display

#### Summary
Fixed carousel image display to show full image content instead of cropping.

#### Changes Made

**Posts/Show.vue** (`resources/js/pages/Posts/Show.vue:325-350`)
- Changed image `object-cover` to `object-contain` for both single images and carousel
- Added `bg-black` background to fill empty space around images
- Images now maintain original aspect ratio without cropping

**Before:**
```vue
class="w-full h-full object-cover"
```

**After:**
```vue
class="w-full h-full object-contain"
<div class="w-full h-[350px] bg-black">
```

---

### Profile Page - VIP Badge Display

#### Summary
Added "普通会员" badge for non-VIP users in profile page.

#### Changes Made

**Profile/Index.vue** (`resources/js/pages/Profile/Index.vue:147-149`)
- Added `v-else` badge when user has no active plan subscription
- Shows "普通会员" in gray badge for regular users
- VIP users still see their plan badge with custom colors
