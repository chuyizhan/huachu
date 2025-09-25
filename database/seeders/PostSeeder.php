<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PostSeeder extends Seeder
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

        $posts = [
            // 大厨献菜 (Chef Special)
            [
                'category_slug' => 'chef-special',
                'posts' => [
                    [
                        'title' => '川味回锅肉的正宗做法',
                        'excerpt' => '传承百年的川菜经典，教你做出地道的回锅肉，肥而不腻，香辣下饭。',
                        'content' => '回锅肉是川菜中最具代表性的菜品之一。选用五花肉，配以豆瓣酱、甜面酱等调料，炒制出香辣可口的经典川菜。制作关键在于肉片要薄，火候要足，调料要正宗。',
                        'type' => 'tutorial',
                        'tags' => ['川菜', '回锅肉', '经典菜', '下饭菜'],
                        'is_featured' => true,
                    ],
                    [
                        'title' => '宫保鸡丁的秘制做法',
                        'excerpt' => '宫保鸡丁的精髓在于糖醋汁的调配，酸甜适中，鸡肉嫩滑。',
                        'content' => '宫保鸡丁选用鸡胸肉，配以花生米、干辣椒等，关键在于糖醋汁的调配比例。先将鸡肉腌制入味，再下锅快炒，保持鸡肉的嫩滑口感。',
                        'type' => 'tutorial',
                        'tags' => ['川菜', '宫保鸡丁', '经典菜'],
                        'is_featured' => true,
                    ],
                    [
                        'title' => '麻婆豆腐的传统工艺',
                        'excerpt' => '正宗麻婆豆腐的制作要点，麻辣鲜香，豆腐嫩滑。',
                        'content' => '麻婆豆腐是川菜名菜，以豆腐为主料，配以肉末、豆瓣酱等调料。制作时要掌握好火候，保持豆腐的完整和嫩滑，调料的搭配要恰到好处。',
                        'type' => 'tutorial',
                        'tags' => ['川菜', '麻婆豆腐', '豆腐菜'],
                        'is_featured' => false,
                    ],
                ]
            ],
            // 网友技术珍藏区 (User Techniques)
            [
                'category_slug' => 'user-techniques',
                'posts' => [
                    [
                        'title' => '炒菜不粘锅的10个小技巧',
                        'excerpt' => '分享多年炒菜经验，教你如何让炒菜不粘锅，保持菜品的完美形状。',
                        'content' => '炒菜不粘锅的关键技巧包括：锅要热油要温、食材要干燥、火候要掌握等。这些技巧能让你的炒菜更加专业，菜品卖相更好。',
                        'type' => 'tutorial',
                        'tags' => ['炒菜技巧', '不粘锅', '厨房技巧'],
                        'is_featured' => true,
                    ],
                    [
                        'title' => '刀工基础：如何切出均匀的丝',
                        'excerpt' => '刀工是厨艺的基本功，教你如何切出粗细均匀的丝，提升菜品档次。',
                        'content' => '好的刀工是厨艺进步的基础。切丝要领：刀要锋利、手要稳定、切面要平整。通过练习基本刀法，能让你的菜品更加专业美观。',
                        'type' => 'tutorial',
                        'tags' => ['刀工', '基础技能', '厨艺'],
                        'is_featured' => false,
                    ],
                    [
                        'title' => '调色调香的秘诀',
                        'excerpt' => '分享调味的心得体会，如何让菜品色香味俱全。',
                        'content' => '调味是烹饪的灵魂，好的调味能让普通的食材焕发出诱人的香味。调色调香要根据不同菜品的特点，选择合适的调料和搭配。',
                        'type' => 'tutorial',
                        'tags' => ['调味', '调色', '烹饪技巧'],
                        'is_featured' => true,
                    ],
                ]
            ],
            // 特色小吃区 (Specialty Snacks)
            [
                'category_slug' => 'specialty-snacks',
                'posts' => [
                    [
                        'title' => '手工包子的制作全过程',
                        'excerpt' => '从和面到包馅，详细介绍手工包子的制作方法，皮薄馅大，香甜可口。',
                        'content' => '手工包子的制作需要掌握和面、发面、调馅、包制、蒸制等多个环节。每个步骤都有其技巧和要点，只有掌握了这些，才能做出真正美味的包子。',
                        'type' => 'tutorial',
                        'tags' => ['包子', '面食', '手工制作'],
                        'is_featured' => true,
                    ],
                    [
                        'title' => '煎饼果子的正宗做法',
                        'excerpt' => '天津煎饼果子的传统制作方法，酥脆香甜，营养丰富。',
                        'content' => '煎饼果子是天津的特色小吃，制作简单但要做得正宗需要掌握面糊调配、摊饼技巧等。配菜的选择和搭配也很重要。',
                        'type' => 'tutorial',
                        'tags' => ['煎饼果子', '天津小吃', '街头美食'],
                        'is_featured' => false,
                    ],
                    [
                        'title' => '糖葫芦的制作秘诀',
                        'excerpt' => '传统糖葫芦的制作方法，糖浆熬制是关键，酸甜可口。',
                        'content' => '糖葫芦制作的关键在于糖浆的熬制，火候掌握得当才能做出晶莹剔透的效果。山楂的选择和处理也很重要，要选择新鲜酸甜的山楂。',
                        'type' => 'tutorial',
                        'tags' => ['糖葫芦', '传统小吃', '甜品'],
                        'is_featured' => true,
                    ],
                ]
            ],
            // 家常菜 (Home Cooking)
            [
                'category_slug' => 'home-cooking',
                'posts' => [
                    [
                        'title' => '西红柿鸡蛋的经典做法',
                        'excerpt' => '最家常的西红柿鸡蛋，简单易学，营养丰富，老少皆宜。',
                        'content' => '西红柿鸡蛋是最经典的家常菜之一。制作简单，但要做得好吃需要掌握火候和调味。西红柿要选择熟透的，鸡蛋要嫩滑，搭配得当才能做出美味的菜品。',
                        'type' => 'tutorial',
                        'tags' => ['西红柿鸡蛋', '家常菜', '简单易学'],
                        'is_featured' => false,
                    ],
                    [
                        'title' => '红烧肉的家庭做法',
                        'excerpt' => '肥而不腻的红烧肉，色泽红亮，入口即化，家庭聚餐的必备菜。',
                        'content' => '红烧肉是经典的家常菜，选用五花肉，通过焯水、炒糖色、慢炖等步骤制作。关键在于火候的掌握和调料的配比，做出来的肉要肥而不腻。',
                        'type' => 'tutorial',
                        'tags' => ['红烧肉', '家常菜', '聚餐菜'],
                        'is_featured' => true,
                    ],
                    [
                        'title' => '蒜蓉西兰花的清爽做法',
                        'excerpt' => '清淡爽口的蒜蓉西兰花，制作简单，营养价值高，适合日常食用。',
                        'content' => '蒜蓉西兰花是一道清爽的家常菜，西兰花营养丰富，配以蒜蓉调味，制作简单快捷。关键在于西兰花的焯水时间要掌握好，保持脆嫩的口感。',
                        'type' => 'tutorial',
                        'tags' => ['西兰花', '清淡菜', '营养菜'],
                        'is_featured' => false,
                    ],
                ]
            ],
            // 火锅区 (Hotpot)
            [
                'category_slug' => 'hotpot',
                'posts' => [
                    [
                        'title' => '重庆火锅底料的制作方法',
                        'excerpt' => '正宗重庆火锅底料的家庭制作方法，麻辣鲜香，越吃越想吃。',
                        'content' => '重庆火锅底料的制作需要多种香料和辣椒，通过慢火熬制而成。关键在于香料的搭配和熬制的时间，做出来的底料要香辣适中，回味悠长。',
                        'type' => 'tutorial',
                        'tags' => ['火锅底料', '重庆火锅', '麻辣'],
                        'is_featured' => true,
                    ],
                    [
                        'title' => '清汤火锅的制作技巧',
                        'excerpt' => '清淡不腻的清汤火锅，适合老人孩子，营养丰富，口感清香。',
                        'content' => '清汤火锅以清淡为主，通过骨头汤或鸡汤作为底料，配以各种蔬菜和肉类。制作关键在于汤底要清澈鲜美，食材要新鲜。',
                        'type' => 'tutorial',
                        'tags' => ['清汤火锅', '清淡', '营养'],
                        'is_featured' => false,
                    ],
                    [
                        'title' => '火锅蘸料的经典搭配',
                        'excerpt' => '介绍几种经典的火锅蘸料搭配，让火锅更加美味。',
                        'content' => '好的蘸料能让火锅更加美味。经典搭配包括芝麻酱蘸料、蒜泥蘸料、香菜蘸料等。每种蘸料都有其特色和适合搭配的食材。',
                        'type' => 'tutorial',
                        'tags' => ['火锅蘸料', '调料搭配', '火锅配菜'],
                        'is_featured' => true,
                    ],
                ]
            ],
            // 川菜 (Sichuan Cuisine)
            [
                'category_slug' => 'sichuan',
                'posts' => [
                    [
                        'title' => '水煮鱼的正宗做法',
                        'excerpt' => '麻辣鲜香的水煮鱼，鱼肉嫩滑，汤汁浓郁，是川菜的经典代表。',
                        'content' => '水煮鱼是川菜的招牌菜之一，以其麻辣鲜香而闻名。制作关键在于鱼片要嫩滑，汤底要浓郁，辣椒和花椒的使用要恰到好处。',
                        'type' => 'tutorial',
                        'tags' => ['水煮鱼', '川菜', '麻辣'],
                        'is_featured' => true,
                    ],
                    [
                        'title' => '夫妻肺片的传统制作',
                        'excerpt' => '经典川菜夫妻肺片，选料丰富，调味独特，麻辣爽口。',
                        'content' => '夫妻肺片是川菜中的凉菜代表，选用牛肉、牛舌、牛心等部位，配以特制的麻辣调料。制作关键在于食材的处理和调料的搭配。',
                        'type' => 'tutorial',
                        'tags' => ['夫妻肺片', '川菜', '凉菜'],
                        'is_featured' => false,
                    ],
                ]
            ],
            // 粤菜 (Cantonese Cuisine)
            [
                'category_slug' => 'cantonese',
                'posts' => [
                    [
                        'title' => '白切鸡的精致做法',
                        'excerpt' => '粤菜经典白切鸡，鸡肉嫩滑，原汁原味，配以特制蘸料。',
                        'content' => '白切鸡是粤菜的代表菜品，以其清淡鲜美而著称。制作关键在于鸡的选择和水温的控制，要保持鸡肉的嫩滑和鲜味。',
                        'type' => 'tutorial',
                        'tags' => ['白切鸡', '粤菜', '清淡'],
                        'is_featured' => true,
                    ],
                    [
                        'title' => '蒸蛋羹的嫩滑秘诀',
                        'excerpt' => '如何蒸出嫩滑如豆腐的鸡蛋羹，口感顺滑，营养丰富。',
                        'content' => '蒸蛋羹看似简单，但要做得嫩滑需要掌握水蛋比例、蒸制时间和火候。好的蛋羹应该像豆腐一样嫩滑，没有蜂窝状的气孔。',
                        'type' => 'tutorial',
                        'tags' => ['蒸蛋羹', '粤菜', '嫩滑'],
                        'is_featured' => false,
                    ],
                ]
            ],
            // 面食 (Noodles)
            [
                'category_slug' => 'noodles',
                'posts' => [
                    [
                        'title' => '手擀面的制作技艺',
                        'excerpt' => '传统手擀面的制作方法，面条劲道爽滑，是面食的经典。',
                        'content' => '手擀面是传统面食的代表，制作需要掌握和面、醒面、擀面等技巧。好的手擀面应该劲道爽滑，口感层次丰富。',
                        'type' => 'tutorial',
                        'tags' => ['手擀面', '面食', '传统工艺'],
                        'is_featured' => true,
                    ],
                    [
                        'title' => '酸汤水饺的制作方法',
                        'excerpt' => '酸辣开胃的酸汤水饺，饺子鲜美，汤汁酸辣，非常下饭。',
                        'content' => '酸汤水饺结合了饺子的鲜美和酸汤的开胃，是很受欢迎的面食。制作关键在于饺子馅的调制和酸汤的调配。',
                        'type' => 'tutorial',
                        'tags' => ['酸汤水饺', '面食', '酸辣'],
                        'is_featured' => false,
                    ],
                ]
            ],
            // 烘焙甜品 (Baking & Desserts)
            [
                'category_slug' => 'baking-desserts',
                'posts' => [
                    [
                        'title' => '戚风蛋糕的零失败做法',
                        'excerpt' => '详细的戚风蛋糕制作步骤，蓬松香甜，新手也能轻松掌握。',
                        'content' => '戚风蛋糕是烘焙入门的经典，制作过程中需要注意蛋白的打发、面糊的搅拌等关键步骤。掌握了技巧就能做出蓬松香甜的蛋糕。',
                        'type' => 'tutorial',
                        'tags' => ['戚风蛋糕', '烘焙', '甜品'],
                        'is_featured' => true,
                    ],
                    [
                        'title' => '免烤芝士蛋糕的简单做法',
                        'excerpt' => '不用烤箱也能做的芝士蛋糕，免烤免烘，口感丝滑。',
                        'content' => '免烤芝士蛋糕制作简单，不需要烤箱，适合没有烘焙设备的朋友。关键在于芝士糊的调制和凝固剂的使用。',
                        'type' => 'tutorial',
                        'tags' => ['芝士蛋糕', '免烤', '甜品'],
                        'is_featured' => false,
                    ],
                ]
            ],
            // 素食 (Vegetarian)
            [
                'category_slug' => 'vegetarian',
                'posts' => [
                    [
                        'title' => '素食版麻婆豆腐',
                        'excerpt' => '不用肉末的素食麻婆豆腐，同样麻辣鲜香，健康美味。',
                        'content' => '素食版麻婆豆腐用香菇丁代替肉末，配以豆瓣酱等调料，同样能做出麻辣鲜香的效果。适合素食爱好者和追求健康饮食的朋友。',
                        'type' => 'tutorial',
                        'tags' => ['素食', '麻婆豆腐', '健康'],
                        'is_featured' => true,
                    ],
                    [
                        'title' => '清炒时蔬的营养搭配',
                        'excerpt' => '各种时令蔬菜的清炒方法，营养丰富，清淡健康。',
                        'content' => '清炒时蔬是素食的基础菜品，不同蔬菜有不同的处理方法和炒制技巧。合理搭配能让蔬菜保持最佳的营养价值和口感。',
                        'type' => 'tutorial',
                        'tags' => ['素食', '时蔬', '营养'],
                        'is_featured' => false,
                    ],
                ]
            ],
        ];

        foreach ($posts as $categoryData) {
            $category = $categories->where('slug', $categoryData['category_slug'])->first();

            if (!$category) {
                continue;
            }

            foreach ($categoryData['posts'] as $postData) {
                $randomUser = $users->random();

                $post = Post::create([
                    'user_id' => $randomUser->id,
                    'post_category_id' => $category->id,
                    'title' => $postData['title'],
                    'slug' => Str::slug($postData['title']) ?: Str::random(10),
                    'content' => $postData['content'],
                    'excerpt' => $postData['excerpt'],
                    'type' => $postData['type'],
                    'status' => 'published',
                    'is_featured' => $postData['is_featured'],
                    'is_premium' => false,
                    'view_count' => rand(50, 2000),
                    'like_count' => rand(5, 200),
                    'comment_count' => rand(0, 50),
                    'share_count' => rand(0, 30),
                    'tags' => $postData['tags'],
                    'published_at' => Carbon::now()->subDays(rand(1, 30)),
                ]);
            }
        }

        $this->command->info('Posts seeded successfully!');
    }
}