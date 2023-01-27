<?php

namespace App\Integration;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class JoomlaService
{
    protected Connection $connection;

    public function __construct()
    {
        $this->connection = DB::connection('joomla');
    }

    public function posts(int $offset = 0, int $perPage = 10): array
    {
        return $this->connection
            ->table('content')
            ->where('fulltext', 'like', '%{gallery}%')
            ->skip($offset)
            ->take($perPage)
            ->get()
            ->map(function ($post) {
                return [
                    'title' => $post->title,
                    'description' => str_replace(["\r", "\n", "\t", "&nbsp;"],'', strip_tags($post->introtext)),
                    'cover' => data_get(json_decode($post->images, true), 'image_intro'),
                    'path' => $this->getGalleryPath($post->fulltext),
                ];
            })
            ->toArray();
    }

    private function getGalleryPath(string $value): string
    {
        $start = strpos($value, '{gallery}') + 9;

        if ($start < 10) {
            return '';
        }

        $end = strpos($value, '{/gallery}', $start) - $start;

        return trim(substr($value, $start, $end));
    }
}
