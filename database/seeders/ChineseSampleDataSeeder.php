<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CreatorProfile;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ChineseSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing sample data first
        Post::whereIn('user_id', User::where('email', 'like', '%@example.com')->pluck('id'))->delete();
        CreatorProfile::whereIn('user_id', User::where('email', 'like', '%@example.com')->pluck('id'))->delete();
        User::where('email', 'like', '%@example.com')->delete();

        // Create Chinese sample users with creator profiles
        $chefs = [
            [
                'name' => '王师傅',
                'email' => 'wangshifu@example.com',
                'display_name' => '川菜王师傅',
                'bio' => '川菜传统工艺传承人，专注于正宗川菜制作30年，擅长麻婆豆腐、宫保鸡丁等经典川菜。',
                'specialty' => '川菜',
                'location' => '四川成都',
                'experience_years' => 30,
                'rating' => 4.9,
                'follower_count' => 15680,
            ],
            [
                'name' => '李大厨',
                'email' => 'lidachu@example.com',
                'display_name' => '粤菜李大厨',
                'bio' => '粤菜名厨，精通广式点心制作，曾在五星级酒店任职，擅长各类精致粤菜和茶点。',
                'specialty' => '粤菜',
                'location' => '广东广州',
                'experience_years' => 25,
                'rating' => 4.8,
                'follower_count' => 12450,
            ],
            [
                'name' => '张厨师',
                'email' => 'zhangchushi@example.com',
                'display_name' => '面点张师傅',
                'bio' => '北方面食专家，精通各种面条、饺子、包子制作，传承家族面点手艺。',
                'specialty' => '面食',
                'location' => '山东济南',
                'experience_years' => 20,
                'rating' => 4.7,
                'follower_count' => 9320,
            ],
            [
                'name' => '陈师傅',
                'email' => 'chenshifu@example.com',
                'display_name' => '湘菜陈师傅',
                'bio' => '湘菜大师，专攻湖南传统菜肴，擅长各种辣味菜品，口味正宗地道。',
                'specialty' => '湘菜',
                'location' => '湖南长沙',
                'experience_years' => 18,
                'rating' => 4.6,
                'follower_count' => 8750,
            ],
            [
                'name' => '刘大妈',
                'email' => 'liudama@example.com',
                'display_name' => '家常菜刘大妈',
                'bio' => '家常菜达人，擅长制作各种营养美味的家常菜，注重营养搭配和健康饮食。',
                'specialty' => '家常菜',
                'location' => '北京',
                'experience_years' => 35,
                'rating' => 4.8,
                'follower_count' => 18900,
            ],
        ];

        $categories = PostCategory::all();

        foreach ($chefs as $chefData) {
            // Create user
            $user = User::create([
                'name' => $chefData['name'],
                'email' => $chefData['email'],
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            // Create creator profile
            $creator = CreatorProfile::create([
                'user_id' => $user->id,
                'display_name' => $chefData['display_name'],
                'bio' => $chefData['bio'],
                'specialty' => $chefData['specialty'],
                'location' => $chefData['location'],
                'experience_years' => $chefData['experience_years'],
                'rating' => $chefData['rating'],
                'follower_count' => $chefData['follower_count'],
                'verification_status' => 'verified',
                'is_featured' => true,
                'social_links' => [],
            ]);

            // Create sample posts for each chef
            $posts = $this->getPostsForChef($chefData['specialty']);

            foreach ($posts as $postData) {
                $categoryName = $this->mapCategoryToChinese($postData['category']);
                $category = $categories->where('name', $categoryName)->first() ?? $categories->first();

                Post::create([
                    'user_id' => $user->id,
                    'post_category_id' => $category->id,
                    'title' => $postData['title'],
                    'slug' => Str::slug($postData['title']) ?: Str::random(10),
                    'content' => $postData['content'],
                    'excerpt' => $postData['excerpt'],
                    'type' => $postData['type'],
                    'status' => 'published',
                    'is_featured' => $postData['is_featured'] ?? false,
                    'is_premium' => $postData['is_premium'] ?? false,
                    'view_count' => rand(500, 5000),
                    'like_count' => rand(20, 300),
                    'published_at' => now()->subDays(rand(1, 60)),
                    'tags' => $postData['tags'] ?? [],
                ]);
            }
        }
    }

    private function mapCategoryToChinese(string $englishCategory): string
    {
        $mapping = [
            'Sichuan' => '川菜',
            'Cantonese' => '粤菜',
            'Noodles' => '面食',
            'Hunan' => '湘菜',
            'Home Cooking' => '家常菜',
        ];

        return $mapping[$englishCategory] ?? 'General Discussion';
    }

    private function getPostsForChef(string $specialty): array
    {
        $posts = [
            '川菜' => [
                [
                    'title' => '正宗麻婆豆腐的制作秘诀',
                    'excerpt' => '川菜经典菜品麻婆豆腐，关键在于豆瓣酱的选择和火候的掌握，教你做出最正宗的麻婆豆腐。',
                    'content' => '麻婆豆腐是川菜中的经典菜品，以其麻辣鲜香而闻名。制作时要选用优质的郫县豆瓣酱...',
                    'category' => 'Sichuan',
                    'type' => 'tutorial',
                    'is_featured' => true,
                    'tags' => ['川菜', '麻婆豆腐', '豆瓣酱', '经典菜品'],
                ],
                [
                    'title' => '宫保鸡丁的传统做法',
                    'excerpt' => '宫保鸡丁是川菜代表菜之一，鸡丁嫩滑，花生米酥脆，酸甜适中，是下饭神菜。',
                    'content' => '宫保鸡丁制作要点：鸡丁要先腌制，炒制时要掌握好火候，花生米要炸至金黄酥脆...',
                    'category' => 'Sichuan',
                    'type' => 'tutorial',
                    'tags' => ['川菜', '宫保鸡丁', '花生米', '传统做法'],
                ],
                [
                    'title' => '水煮鱼片的家常做法',
                    'excerpt' => '麻辣鲜香的水煮鱼，鱼片嫩滑，汤汁浓郁，是川菜中的人气菜品。',
                    'content' => '水煮鱼的关键在于鱼片的处理和调料的搭配，要保持鱼片的鲜嫩...',
                    'category' => 'Sichuan',
                    'type' => 'tutorial',
                    'is_premium' => true,
                    'tags' => ['川菜', '水煮鱼', '麻辣', '鱼片'],
                ],
            ],
            '粤菜' => [
                [
                    'title' => '港式茶餐厅经典虾饺制作',
                    'excerpt' => '晶莹剔透的虾饺皮，鲜美的虾仁馅，是粤式茶点中的经典，制作技巧全分享。',
                    'content' => '虾饺制作的关键在于面皮的调制和虾仁的处理，要做出晶莹剔透的效果...',
                    'category' => 'Cantonese',
                    'type' => 'tutorial',
                    'is_featured' => true,
                    'tags' => ['粤菜', '虾饺', '茶点', '港式'],
                ],
                [
                    'title' => '广式烧鹅的制作工艺',
                    'excerpt' => '皮脆肉嫩的广式烧鹅，是粤菜中的招牌菜，制作工艺复杂但味道绝佳。',
                    'content' => '烧鹅制作需要选用优质鹅肉，腌制入味后用特制的调料涂抹...',
                    'category' => 'Cantonese',
                    'type' => 'tutorial',
                    'is_premium' => true,
                    'tags' => ['粤菜', '烧鹅', '广式', '制作工艺'],
                ],
            ],
            '面食' => [
                [
                    'title' => '手工拉面的制作技巧',
                    'excerpt' => '从和面到拉面，每一个步骤都有讲究，教你做出劲道爽滑的手工拉面。',
                    'content' => '手工拉面的关键在于面团的醒发和拉面的手法，需要反复练习...',
                    'category' => 'Noodles',
                    'type' => 'tutorial',
                    'is_featured' => true,
                    'tags' => ['面食', '拉面', '手工', '技巧'],
                ],
                [
                    'title' => '北方饺子的包法大全',
                    'excerpt' => '各种饺子包法图解，从最简单的到最复杂的，让你的饺子更加美观。',
                    'content' => '饺子包法多种多样，基础包法要先掌握，然后可以尝试各种花式包法...',
                    'category' => 'Noodles',
                    'type' => 'tutorial',
                    'tags' => ['面食', '饺子', '包法', '北方'],
                ],
            ],
            '湘菜' => [
                [
                    'title' => '湖南辣椒炒肉的正宗做法',
                    'excerpt' => '湘菜经典家常菜辣椒炒肉，简单但要做得好吃需要掌握火候和调料搭配。',
                    'content' => '辣椒炒肉看似简单，但要做得好吃需要选对辣椒品种，猪肉要切得恰到好处...',
                    'category' => 'Hunan',
                    'type' => 'tutorial',
                    'is_featured' => true,
                    'tags' => ['湘菜', '辣椒炒肉', '家常菜', '正宗'],
                ],
                [
                    'title' => '剁椒鱼头的制作秘诀',
                    'excerpt' => '湘菜名菜剁椒鱼头，鱼肉鲜嫩，剁椒香辣，是湖南人最爱的菜品之一。',
                    'content' => '剁椒鱼头的关键在于鱼头的选择和剁椒的制作，要保证鱼肉的鲜嫩...',
                    'category' => 'Hunan',
                    'type' => 'tutorial',
                    'tags' => ['湘菜', '剁椒鱼头', '鱼头', '剁椒'],
                ],
            ],
            '家常菜' => [
                [
                    'title' => '番茄鸡蛋的完美做法',
                    'excerpt' => '最家常的番茄鸡蛋，看似简单却有很多小技巧，掌握后能做出餐厅级别的味道。',
                    'content' => '番茄鸡蛋的关键在于鸡蛋要炒得嫩滑，番茄要出汁但不能太烂...',
                    'category' => 'Home Cooking',
                    'type' => 'tutorial',
                    'is_featured' => true,
                    'tags' => ['家常菜', '番茄鸡蛋', '简单', '技巧'],
                ],
                [
                    'title' => '红烧肉的家常做法',
                    'excerpt' => '肥而不腻的红烧肉，色泽红亮，入口即化，是每个家庭都应该会做的经典菜。',
                    'content' => '红烧肉要选用五花肉，切块后要先焯水去腥，然后用冰糖炒色...',
                    'category' => 'Home Cooking',
                    'type' => 'tutorial',
                    'tags' => ['家常菜', '红烧肉', '五花肉', '经典'],
                ],
                [
                    'title' => '营养搭配的一周菜谱',
                    'excerpt' => '科学搭配营养的一周家常菜谱，荤素搭配，营养均衡，适合全家人。',
                    'content' => '营养搭配要考虑蛋白质、维生素、纤维素的均衡摄入...',
                    'category' => 'Home Cooking',
                    'type' => 'discussion',
                    'tags' => ['家常菜', '营养', '搭配', '菜谱'],
                ],
            ],
        ];

        return $posts[$specialty] ?? [];
    }
}