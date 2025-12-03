<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MovieSeeder extends Seeder
{
    public function run()
    {
        $movies = [
            [
                'title' => 'Avatar: The Way of Water',
                'poster_url' => 'https://placehold.co/300x450?text=Avatar+2',
                'genre' => 'Action',
                'language' => 'English',
                'duration_minutes' => 192,
                'trailer_url' => 'https://www.youtube.com/watch?v=d9MyW72ELq0',
                'description' => 'Set more than a decade after the events of the first film...',
                'release_date' => '2022-12-16',
                'rating_average' => 4.5,
                'status' => 'ended',
            ],
            [
                'title' => 'Spider‑Man: No Way Home',
                'poster_url' => 'spiderman_no_way_home.jpg',
                'genre' => 'Action',
                'language' => 'English',
                'duration_minutes' => 148,
                'trailer_url' => 'https://www.youtube.com/watch?v=JfVOs4VSpmA',
                'description' => 'With Spider-Man\'s identity now revealed, Peter asks Doctor Strange for help...',
                'release_date' => '2021-12-17',
                'rating_average' => 4.3,
                'status' => 'ended',
            ],
            [
                'title' => 'Top Gun: Maverick',
                'poster_url' => 'top_gun_maverick.jpg',
                'genre' => 'Action',
                'language' => 'English',
                'duration_minutes' => 131,
                'trailer_url' => 'https://www.youtube.com/watch?v=giXco2jaZ_4',
                'description' => 'After more than thirty years, Pete "Maverick" Mitchell is still pushing the envelope...',
                'release_date' => '2022-05-27',
                'rating_average' => 4.4,
                'status' => 'ended',
            ],
            [
                'title' => 'Barbie',
                'poster_url' => 'barbie2023.jpg',
                'genre' => 'Comedy',
                'language' => 'English',
                'duration_minutes' => 114,
                'trailer_url' => 'https://www.youtube.com/watch?v=K0E6OOPWgkc',
                'description' => 'Barbie and Ken enter the real world, uncovering truths about one another...',
                'release_date' => '2023-07-21',
                'rating_average' => 4.2,
                'status' => 'ended',
            ],
            [
                'title' => 'Oppenheimer',
                'poster_url' => 'oppenheimer.jpg',
                'genre' => 'Drama',
                'language' => 'English',
                'duration_minutes' => 180,
                'trailer_url' => 'https://www.youtube.com/watch?v=wgK1gE5GVV4',
                'description' => 'The story of J. Robert Oppenheimer and the development of the atomic bomb...',
                'release_date' => '2023-07-21',
                'rating_average' => 4.6,
                'status' => 'now_showing',
            ],
            [
                'title' => 'Inside Out 2',
                'poster_url' => 'inside_out_2.jpg',
                'genre' => 'Animation',
                'language' => 'English',
                'duration_minutes' => 95,
                'trailer_url' => 'https://www.youtube.com/watch?v=example',
                'description' => 'Riley and her emotions navigate the challenges of adolescence...',
                'release_date' => '2024-06-14',
                'rating_average' => 4.0,
                'status' => 'now_showing',
            ],
            [
                'title' => 'The Super Mario Bros. Movie',
                'poster_url' => 'super_mario_bros_movie.jpg',
                'genre' => 'Animation',
                'language' => 'English',
                'duration_minutes' => 92,
                'trailer_url' => 'https://www.youtube.com/watch?v=TnGl01FkMMo',
                'description' => 'Mario and Luigi embark on an adventure to save the Mushroom Kingdom...',
                'release_date' => '2023-04-05',
                'rating_average' => 4.1,
                'status' => 'now_showing',
            ],
            [
                'title' => 'Dune: Part Two',
                'poster_url' => 'dune_part_two.jpg',
                'genre' => 'Sci‑Fi',
                'language' => 'English',
                'duration_minutes' => 166,
                'trailer_url' => 'https://www.youtube.com/watch?v=example',
                'description' => 'Paul Atreides unites with the Fremen while on a warpath of revenge...',
                'release_date' => '2025-03-01',
                'rating_average' => 4.5,
                'status' => 'now_showing',
            ],
            [
                'title' => 'Ne Zha 2',
                'poster_url' => 'nezha2.jpg',
                'genre' => 'Animation',
                'language' => 'Chinese',
                'duration_minutes' => 112,
                'trailer_url' => 'https://www.youtube.com/watch?v=example',
                'description' => 'In a mythical setting, Ne Zha must face new challenges and enemies...',
                'release_date' => '2025-08-01',
                'rating_average' => 4.0,
                'status' => 'now_showing',
            ],
            [
                'title' => 'Lilo & Stitch (2025)',
                'poster_url' => 'lilo_stitch_2025.jpg',
                'genre' => 'Family',
                'language' => 'English',
                'duration_minutes' => 108,
                'trailer_url' => 'https://www.youtube.com/watch?v=example',
                'description' => 'The mischievous Stitch finds a new adventure in the islands...',
                'release_date' => '2025-06-10',
                'rating_average' => 3.9,
                'status' => 'now_showing',
            ],
            [
                'title' => 'Galactic Odyssey',
                'poster_url' => 'galactic_odyssey_2026.jpg',
                'genre' => 'Sci‑Fi',
                'language' => 'English',
                'duration_minutes' => 140,
                'trailer_url' => 'https://www.youtube.com/watch?v=example',
                'description' => 'An interstellar crew sets out on a dangerous mission to save a dying planet.',
                'release_date' => '2026-01-20',
                'rating_average' => 0.0,
                'status' => 'upcoming',
            ],
            [
                'title' => 'The Last Horizon',
                'poster_url' => 'the_last_horizon_2026.jpg',
                'genre' => 'Adventure',
                'language' => 'English',
                'duration_minutes' => 125,
                'trailer_url' => 'https://www.youtube.com/watch?v=example',
                'description' => 'A band of explorers crosses unknown lands to find a mythical city beyond the horizon.',
                'release_date' => '2026-01-12',
                'rating_average' => 0.0,
                'status' => 'upcoming',
            ],
            [
                'title' => 'Rise of Titans',
                'poster_url' => 'rise_of_titans_2026.jpg',
                'genre' => 'Fantasy',
                'language' => 'English',
                'duration_minutes' => 150,
                'trailer_url' => 'https://www.youtube.com/watch?v=example',
                'description' => 'When ancient beings awaken, humanity must unite to reclaim the world.',
                'release_date' => '2026-01-04',
                'rating_average' => 0.0,
                'status' => 'upcoming',
            ],
            [
                'title' => 'Moonlight Sonata',
                'poster_url' => 'moonlight_sonata_2026.jpg',
                'genre' => 'Drama',
                'language' => 'English',
                'duration_minutes' => 118,
                'trailer_url' => 'https://www.youtube.com/watch?v=example',
                'description' => 'A moving story about music, memory, and the ties that bind a family together.',
                'release_date' => '2026-01-19',
                'rating_average' => 0.0,
                'status' => 'upcoming',
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($movies as $movie) {
            $exists = DB::table('movies')->where('title', $movie['title'])->exists();
            
            if (!$exists) {
                DB::table('movies')->insert(array_merge($movie, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                $created++;
            } else {
                $skipped++;
            }
        }

        $this->command->info("MovieSeeder: Đã tạo {$created} phim mới, bỏ qua {$skipped} phim đã tồn tại.");
    }
}
