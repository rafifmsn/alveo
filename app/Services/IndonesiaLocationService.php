<?php

namespace App\Services;

class IndonesiaLocationService
{
    /**
     * @var array<int, array<string, mixed>>|null
     */
    protected static ?array $cachedRecords = null;

    /**
     * Search Indonesian provinces, cities/regencies, and districts deterministically.
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, int $limit = 12): array
    {
        $needle = mb_strtolower(trim(preg_replace('/\s+/', ' ', $query) ?? ''));
        if (mb_strlen($needle) < 2) {
            return [];
        }

        $limit = max(1, min(25, $limit));
        $scored = [];

        foreach ($this->records() as $record) {
            $score = $this->scoreRecord($record, $needle);
            if ($score > 0) {
                $scored[] = ['score' => $score, 'record' => $record];
            }
        }

        usort($scored, static function (array $a, array $b): int {
            if ($a['score'] !== $b['score']) {
                return $b['score'] <=> $a['score'];
            }

            if ($a['record']['level'] !== $b['record']['level']) {
                return $a['record']['level'] <=> $b['record']['level'];
            }

            return strcmp($a['record']['label'], $b['record']['label']);
        });

        return array_map(
            static fn (array $entry): array => $entry['record'],
            array_slice($scored, 0, $limit)
        );
    }

    /**
     * Resolve the highest-ranked administrative location for a free-text query.
     *
     * @return array<string, mixed>|null
     */
    public function findByQuery(string $query): ?array
    {
        $results = $this->search($query, 1);

        return $results[0] ?? null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function records(): array
    {
        if (self::$cachedRecords !== null) {
            return self::$cachedRecords;
        }

        $path = database_path('data/indonesia-locations.json');
        if (! is_file($path)) {
            return self::$cachedRecords = [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        self::$cachedRecords = is_array($decoded) ? $decoded : [];

        return self::$cachedRecords;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    protected function scoreRecord(array $record, string $needle): int
    {
        $name = mb_strtolower((string) ($record['name'] ?? ''));
        $nameEn = mb_strtolower((string) ($record['name_en'] ?? ''));
        $label = mb_strtolower((string) ($record['label'] ?? ''));

        $base = 0;

        if ($name === $needle || $nameEn === $needle) {
            $base = 1000;
        } elseif (str_starts_with($name, $needle) || str_starts_with($nameEn, $needle)) {
            $base = 800;
        } elseif (str_contains($name, ' '.$needle) || str_contains($nameEn, ' '.$needle)) {
            $base = 650;
        } elseif (str_contains($name, $needle) || str_contains($nameEn, $needle)) {
            $base = 450;
        } elseif (str_starts_with($label, $needle) || str_contains($label, ' '.$needle)) {
            $base = 300;
        } elseif (str_contains($label, $needle)) {
            $base = 150;
        }

        if ($base === 0) {
            return 0;
        }

        $levelBonus = (4 - (int) ($record['level'] ?? 3)) * 50;

        return $base + $levelBonus;
    }
}
