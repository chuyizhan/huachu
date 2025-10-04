<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdditionalPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all categories and users
        $categories = PostCategory::all();
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No users found. Please run the user seeder first.');
            return;
        }

        // More川菜 posts to test pagination
        $sichuanCategory = $categories->where('slug', 'sichuan')->first();
        if ($sichuanCategory) {
            $sichuanPosts = [
                [
                    'title' => '口水鸡的秘制做法',
                    'excerpt' => '正宗口水鸡，麻辣鲜香，口水直流的经典川菜凉菜。',
                    'content' => '口水鸡是川菜中的经典凉菜，选用优质土鸡，配以特制的麻辣调料，制作出来的鸡肉嫩滑，调料香辣，让人食欲大增。制作关键在于鸡肉的处理和调料的搭配。',
                    'tags' => ['川菜', '口水鸡', '凉菜', '麻辣'],
                    'is_featured' => true,
                ],
                [
                    'title' => '蒜泥白肉的传统工艺',
                    'excerpt' => '蒜泥白肉，肉质软嫩，蒜香浓郁，是川菜中的经典凉菜。',
                    'content' => '蒜泥白肉选用优质五花肉，煮制后切片，配以蒜泥调料。制作关键在于肉的煮制时间和蒜泥的调制比例，做出来的白肉要肥而不腻，蒜香浓郁。',
                    'tags' => ['川菜', '蒜泥白肉', '凉菜'],
                    'is_featured' => false,
                ],
                [
                    'title' => '辣子鸡丁的正宗做法',
                    'excerpt' => '辣子鸡丁，鸡肉鲜嫩，辣椒香脆，是川菜中的经典热菜。',
                    'content' => '辣子鸡丁选用鸡胸肉，配以干辣椒和花生米，炒制而成。制作关键在于鸡肉的腌制和火候的掌握，做出来的鸡肉要嫩滑，辣椒要香脆。',
                    'tags' => ['川菜', '辣子鸡丁', '热菜', '麻辣'],
                    'is_featured' => true,
                ],
                [
                    'title' => '鱼香肉丝的家常做法',
                    'excerpt' => '鱼香肉丝，酸甜可口，是川菜中最受欢迎的家常菜之一。',
                    'content' => '鱼香肉丝选用猪肉丝，配以木耳、胡萝卜丝等，用鱼香调料炒制。制作关键在于肉丝的腌制和调料的搭配，做出来的菜品要酸甜适中，口感丰富。',
                    'tags' => ['川菜', '鱼香肉丝', '家常菜'],
                    'is_featured' => false,
                ],
                [
                    'title' => '毛血旺的制作秘诀',
                    'excerpt' => '毛血旺，麻辣鲜香，是重庆火锅文化的代表菜品。',
                    'content' => '毛血旺选用鸭血、毛肚、豆皮等多种食材，配以麻辣底料煮制。制作关键在于底料的调制和煮制的时间，做出来的菜品要麻辣适中，食材丰富。',
                    'tags' => ['川菜', '毛血旺', '麻辣', '重庆菜'],
                    'is_featured' => true,
                ],
                [
                    'title' => '干煸豆角的经典做法',
                    'excerpt' => '干煸豆角，豆角酥脆，调味香浓，是川菜中的经典素菜。',
                    'content' => '干煸豆角选用新鲜豆角，配以肉末和干辣椒煸炒。制作关键在于豆角的处理和火候的掌握，做出来的豆角要外酥内嫩，味道香浓。',
                    'tags' => ['川菜', '干煸豆角', '素菜'],
                    'is_featured' => false,
                ],
                [
                    'title' => '酸菜鱼的正宗做法',
                    'excerpt' => '酸菜鱼，鱼肉鲜嫩，汤汁酸辣，是川菜中的招牌菜。',
                    'content' => '酸菜鱼选用草鱼或黑鱼，配以酸菜和特制调料煮制。制作关键在于鱼片的处理和汤底的调制，做出来的鱼肉要嫩滑，汤汁要酸辣开胃。',
                    'tags' => ['川菜', '酸菜鱼', '鱼类', '酸辣'],
                    'is_featured' => true,
                ],
                [
                    'title' => '棒棒鸡的制作工艺',
                    'excerpt' => '棒棒鸡，鸡肉香嫩，调料丰富，是川菜凉菜的代表。',
                    'content' => '棒棒鸡选用土鸡，煮熟后撕成丝状，配以特制的芝麻酱调料。制作关键在于鸡肉的煮制和调料的搭配，做出来的鸡肉要香嫩，调料要香浓。',
                    'tags' => ['川菜', '棒棒鸡', '凉菜', '芝麻酱'],
                    'is_featured' => false,
                ],
                [
                    'title' => '香辣虾的制作方法',
                    'excerpt' => '香辣虾，虾肉鲜美，香辣下饭，是川菜海鲜的经典。',
                    'content' => '香辣虾选用新鲜大虾，配以干辣椒和花椒爆炒。制作关键在于虾的处理和调料的搭配，做出来的虾要鲜美，调料要香辣适中。',
                    'tags' => ['川菜', '香辣虾', '海鲜', '香辣'],
                    'is_featured' => true,
                ],
                [
                    'title' => '麻辣香锅的家庭版',
                    'excerpt' => '麻辣香锅，食材丰富，口味浓郁，适合家庭聚餐。',
                    'content' => '麻辣香锅可以根据个人喜好添加各种食材，如肉类、蔬菜、豆制品等，配以麻辣底料炒制。制作简单，营养丰富，是很受欢迎的家常菜。',
                    'tags' => ['川菜', '麻辣香锅', '家常菜', '聚餐'],
                    'is_featured' => false,
                ],
            ];

            foreach ($sichuanPosts as $postData) {
                $randomUser = $users->random();
                Post::create([
                    'user_id' => $randomUser->id,
                    'post_category_id' => $sichuanCategory->id,
                    'title' => $postData['title'],
                    'slug' => Str::slug($postData['title']) ?: Str::random(10),
                    'content' => $postData['content'],
                    'excerpt' => $postData['excerpt'],
                    'type' => 'tutorial',
                    'status' => 'published',
                    'is_featured' => $postData['is_featured'],
                    'is_premium' => false,
                    'view_count' => rand(100, 3000),
                    'like_count' => rand(10, 300),
                    'comment_count' => rand(0, 80),
                    'share_count' => rand(0, 50),
                    'tags' => $postData['tags'],
                    'published_at' => Carbon::now()->subDays(rand(1, 60)),
                ]);
            }
        }

        // More 家常菜 posts
        $homeCategory = $categories->where('slug', 'home-cooking')->first();
        if ($homeCategory) {
            $homePosts = [
                [
                    'title' => '糖醋排骨的经典做法',
                    'excerpt' => '酸甜可口的糖醋排骨，老少皆宜，是家庭聚餐的必备菜。',
                    'content' => '糖醋排骨选用猪小排，通过焯水、炸制、调味等步骤制作。关键在于糖醋汁的调配和火候的掌握，做出来的排骨要外酥内嫩，酸甜适中。',
                    'tags' => ['家常菜', '糖醋排骨', '聚餐菜'],
                    'is_featured' => true,
                ],
                [
                    'title' => '青椒肉丝的简单做法',
                    'excerpt' => '青椒肉丝，色彩搭配美观，营养丰富，是最常见的家常菜。',
                    'content' => '青椒肉丝选用猪肉丝和青椒丝，快速炒制而成。制作简单，但要做得好吃需要掌握肉丝的腌制和炒制的火候，做出来的菜要嫩滑爽脆。',
                    'tags' => ['家常菜', '青椒肉丝', '简单易学'],
                    'is_featured' => false,
                ],
                [
                    'title' => '土豆丝的多种做法',
                    'excerpt' => '土豆丝是最基础的家常菜，酸辣土豆丝、干煸土豆丝各有特色。',
                    'content' => '土豆丝的做法多样，可以做成酸辣土豆丝、干煸土豆丝、炝拌土豆丝等。每种做法都有其特点和技巧，是练习刀工和火候的好菜品。',
                    'tags' => ['家常菜', '土豆丝', '基础菜'],
                    'is_featured' => true,
                ],
                [
                    'title' => '蛋炒饭的完美做法',
                    'excerpt' => '蛋炒饭看似简单，但要做得粒粒分明、香味浓郁需要技巧。',
                    'content' => '好的蛋炒饭要做到粒粒分明，蛋香米香融合。关键在于米饭的处理、鸡蛋的炒制和调味的搭配。掌握了技巧，就能做出餐厅水准的蛋炒饭。',
                    'tags' => ['家常菜', '蛋炒饭', '主食'],
                    'is_featured' => false,
                ],
                [
                    'title' => '白萝卜炖排骨汤',
                    'excerpt' => '白萝卜炖排骨汤，清淡营养，适合老人孩子，是很好的滋补汤品。',
                    'content' => '白萝卜炖排骨汤选用新鲜排骨和白萝卜，慢火炖煮。制作关键在于排骨的处理和炖煮的时间，做出来的汤要清澈鲜美，萝卜要软糯。',
                    'tags' => ['家常菜', '炖汤', '营养汤', '滋补'],
                    'is_featured' => true,
                ],
                [
                    'title' => '凉拌黄瓜的清爽做法',
                    'excerpt' => '凉拌黄瓜，清爽解腻，是夏日必备的开胃凉菜。',
                    'content' => '凉拌黄瓜制作简单，选用新鲜黄瓜，用盐腌制后拌以调料。关键在于黄瓜的处理和调料的搭配，做出来的菜要爽脆开胃。',
                    'tags' => ['家常菜', '凉拌菜', '开胃菜', '夏日菜'],
                    'is_featured' => false,
                ],
                [
                    'title' => '鸡蛋羹的嫩滑秘诀',
                    'excerpt' => '鸡蛋羹，嫩滑如豆腐，营养丰富，是老少皆宜的营养菜。',
                    'content' => '鸡蛋羹看似简单，但要做得嫩滑需要掌握蛋液和水的比例、蒸制的时间和火候。好的鸡蛋羹应该像豆腐一样嫩滑，没有蜂窝。',
                    'tags' => ['家常菜', '鸡蛋羹', '营养菜', '蒸菜'],
                    'is_featured' => true,
                ],
                [
                    'title' => '小白菜炒豆腐',
                    'excerpt' => '小白菜炒豆腐，清淡营养，制作简单，是很好的素菜。',
                    'content' => '小白菜炒豆腐选用新鲜小白菜和嫩豆腐，快速炒制。制作关键在于豆腐的处理和炒制的火候，做出来的菜要清香爽口。',
                    'tags' => ['家常菜', '素菜', '清淡菜'],
                    'is_featured' => false,
                ],
            ];

            foreach ($homePosts as $postData) {
                $randomUser = $users->random();
                Post::create([
                    'user_id' => $randomUser->id,
                    'post_category_id' => $homeCategory->id,
                    'title' => $postData['title'],
                    'slug' => Str::slug($postData['title']) ?: Str::random(10),
                    'content' => $postData['content'],
                    'excerpt' => $postData['excerpt'],
                    'type' => 'tutorial',
                    'status' => 'published',
                    'is_featured' => $postData['is_featured'],
                    'is_premium' => false,
                    'view_count' => rand(80, 2500),
                    'like_count' => rand(8, 250),
                    'comment_count' => rand(0, 60),
                    'share_count' => rand(0, 40),
                    'tags' => $postData['tags'],
                    'published_at' => Carbon::now()->subDays(rand(1, 45)),
                ]);
            }
        }

        $this->command->info('Additional posts seeded successfully!');
    }
}