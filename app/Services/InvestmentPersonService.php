<?php

namespace App\Services;

use App\Models\InvestmentManager;
use App\Models\InvestmentPersonRole;
use App\Models\ReksaDana;
use App\Models\StockNews;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class InvestmentPersonService
{
    public const ROLE_LABELS = [
        'commissioner_president' => 'Komisaris Utama',
        'commissioner' => 'Komisaris',
        'director_president' => 'Direktur Utama',
        'director' => 'Direktur',
        'shareholder' => 'Pemegang Saham',
        'investment_committee' => 'Komite Investasi',
        'investment_management_team' => 'Tim Pengelola Investasi',
    ];

    public function sectionsForManager(InvestmentManager $manager): array
    {
        return [
            'commissioners' => [
                'label' => 'Komisaris',
                'items' => $this->mergeItems(
                    $this->singleItem($manager->commissioner_president, 'Komisaris Utama'),
                    $this->parsePeople($manager->commissioners, 'Komisaris')
                ),
            ],
            'directors' => [
                'label' => 'Direktur',
                'items' => $this->mergeItems(
                    $this->singleItem($manager->director_president, 'Direktur Utama'),
                    $this->parsePeople($manager->directors, 'Direktur')
                ),
            ],
            'shareholders' => [
                'label' => 'Pemegang Saham',
                'items' => $this->parsePeople($manager->shareholders, 'Pemegang Saham'),
            ],
            'investment_committee' => [
                'label' => 'Komite Investasi',
                'items' => $this->parsePeople($manager->investment_committee, 'Komite Investasi'),
            ],
            'investment_management_team' => [
                'label' => 'Tim Pengelola Investasi',
                'items' => $this->parsePeople($manager->investment_management_team, 'Tim Pengelola Investasi'),
            ],
        ];
    }

    public function syncInvestmentManager(InvestmentManager $manager, string $source = 'prospektus'): void
    {
        $sections = $this->sectionsForManager($manager);
        $roleMap = [
            'commissioners' => 'commissioner',
            'directors' => 'director',
            'shareholders' => 'shareholder',
            'investment_committee' => 'investment_committee',
            'investment_management_team' => 'investment_management_team',
        ];

        foreach ($sections as $key => $section) {
            foreach ($section['items'] as $item) {
                $this->upsertRole([
                    'person_name' => $item['name'],
                    'role_type' => $roleMap[$key],
                    'role_title' => $item['position'] ?: $section['label'],
                    'investment_manager_id' => $manager->id,
                    'reksa_dana_id' => null,
                    'source' => $source,
                ]);
            }
        }
    }

    public function syncFund(ReksaDana $fund, string $source = 'ffs'): void
    {
        $fund->loadMissing('managementTeams');
        foreach ($fund->managementTeams as $team) {
            $roleType = $team->type === 'committee' ? 'investment_committee' : 'investment_management_team';
            $this->upsertRole([
                'person_name' => $team->name,
                'role_type' => $roleType,
                'role_title' => $team->position ?: self::ROLE_LABELS[$roleType],
                'investment_manager_id' => $fund->investment_manager_id,
                'reksa_dana_id' => $fund->id,
                'source' => $source,
            ]);
        }
    }

    public function detail(string $name): array
    {
        $normalized = $this->normalizeName($name);
        $roles = InvestmentPersonRole::with(['investmentManager', 'reksaDana'])
            ->where('normalized_name', $normalized)
            ->orderBy('role_type')
            ->get();

        $fundRoles = $roles->filter(fn ($role) => $role->reksa_dana_id !== null)->values();
        $managerRoles = $roles->filter(fn ($role) => $role->investment_manager_id !== null)->values();

        $fallbackManagers = InvestmentManager::query()
            ->where('commissioner_president', 'like', "%{$name}%")
            ->orWhere('commissioners', 'like', "%{$name}%")
            ->orWhere('director_president', 'like', "%{$name}%")
            ->orWhere('directors', 'like', "%{$name}%")
            ->orWhere('shareholders', 'like', "%{$name}%")
            ->orWhere('investment_committee', 'like', "%{$name}%")
            ->orWhere('investment_management_team', 'like', "%{$name}%")
            ->limit(10)
            ->get();

        $fallbackFunds = ReksaDana::whereHas('managementTeams', fn ($q) => $q->where('name', 'like', "%{$name}%"))
            ->with(['managementTeams' => fn ($q) => $q->where('name', 'like', "%{$name}%")])
            ->limit(10)
            ->get();

        $news = StockNews::query()
            ->where('title', 'like', "%{$name}%")
            ->orWhere('summary', 'like', "%{$name}%")
            ->orderByDesc('published_at')
            ->limit(5)
            ->get(['title', 'url', 'source', 'published_at']);

        return [
            'name' => $name,
            'funds' => $this->fundDetailRows($fundRoles, $fallbackFunds),
            'managers' => $this->managerDetailRows($managerRoles, $fallbackManagers),
            'news' => $news->map(fn ($item) => [
                'title' => $item->title,
                'url' => $item->url,
                'source' => $item->source,
                'published_at' => $item->published_at?->format('d M Y'),
            ])->values(),
        ];
    }

    private function isJson(string $value): bool
    {
        if (empty($value)) return false;
        $first = trim($value)[0] ?? '';
        if ($first !== '[' && $first !== '{') return false;
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function normalizeName(?string $name): string
    {
        return Str::of((string) $name)
            ->lower()
            ->replaceMatches('/\s+/', ' ')
            ->replaceMatches('/[^\pL\pN .,&-]/u', '')
            ->trim()
            ->toString();
    }

    public function parsePeople(?string $text, ?string $defaultPosition = null): array
    {
        if (!filled($text)) {
            return [];
        }

        if ($this->isJson($text)) {
            return [];
        }

        $trimmed = trim($text);
        if (preg_match('/^[[{]/', $trimmed)) {
            preg_match_all('/"(?:nama|name)"\s*(?::\s*"|")([^"]+)"/i', $text, $matches);
            if (!empty($matches[1])) {
                $items = [];
                foreach ($matches[1] as $name) {
                    $name = $this->cleanName($name);
                    if ($name === '' || mb_strlen($name) < 3) {
                        continue;
                    }
                    $key = $this->normalizeName($name);
                    $items[$key] = [
                        'name' => $name,
                        'position' => $defaultPosition,
                    ];
                }
                return array_values($items);
            }
            return [];
        }

        $normalizedText = str_replace(["\r", "\t"], ["\n", ' '], (string) $text);
        $normalizedText = preg_replace('/\s*(?:;|•|\|)\s*/u', "\n", $normalizedText);
        $lines = preg_split('/\n+/', $normalizedText) ?: [];

        $items = [];
        foreach ($lines as $line) {
            $line = trim(preg_replace('/\s{2,}/', ' ', $line));
            $line = trim($line, "- \t\n\r\0\x0B");
            if ($line === '' || mb_strlen($line) < 3) {
                continue;
            }

            [$name, $position] = $this->splitNamePosition($line, $defaultPosition);
            $name = $this->cleanName($name);
            if ($name === '' || mb_strlen($name) < 3) {
                continue;
            }

            $key = $this->normalizeName($name);
            $items[$key] = [
                'name' => $name,
                'position' => $position ?: $defaultPosition,
            ];
        }

        return array_values($items);
    }

    private function singleItem(?string $name, string $position): array
    {
        $name = $this->cleanName((string) $name);

        return $name !== '' ? [[
            'name' => $name,
            'position' => $position,
        ]] : [];
    }

    private function mergeItems(array ...$groups): array
    {
        $items = [];
        foreach ($groups as $group) {
            foreach ($group as $item) {
                $items[$this->normalizeName($item['name'])] = $item;
            }
        }

        return array_values($items);
    }

    private function splitNamePosition(string $line, ?string $defaultPosition): array
    {
        if (preg_match('/^(.+?)\s*(?:-|:|–)\s*(.+)$/u', $line, $m)) {
            $left = trim($m[1]);
            $right = trim($m[2]);

            if ($this->looksLikePosition($left) && !$this->looksLikePosition($right)) {
                return [$right, $left];
            }

            return [$left, $right];
        }

        if (preg_match('/^(.+?)\s{2,}(.+)$/u', $line, $m)) {
            return [trim($m[1]), trim($m[2])];
        }

        return [$line, $defaultPosition];
    }

    private function looksLikePosition(string $value): bool
    {
        return (bool) preg_match('/\b(Komisaris|Direktur|Ketua|Anggota|President|Chief|CEO|CIO|Head|Manager|Pemegang Saham)\b/i', $value);
    }

    private function cleanName(string $name): string
    {
        $name = preg_replace('/\s*\(?\d+(?:[,.]\d+)?\s*%?\)?\s*$/', '', $name);
        $name = preg_replace('/^(?:Nama|Name)\s*:\s*/i', '', (string) $name);

        return trim($name, " \t\n\r\0\x0B.,:;-");
    }

    private function upsertRole(array $data): void
    {
        $name = $this->cleanName((string) ($data['person_name'] ?? ''));
        if ($name === '') {
            return;
        }

        $data['person_name'] = $name;
        $data['normalized_name'] = $this->normalizeName($name);

        InvestmentPersonRole::updateOrCreate(
            [
                'normalized_name' => $data['normalized_name'],
                'role_type' => $data['role_type'],
                'investment_manager_id' => $data['investment_manager_id'] ?? null,
                'reksa_dana_id' => $data['reksa_dana_id'] ?? null,
            ],
            $data
        );
    }

    private function fundDetailRows(Collection $roleRows, Collection $fallbackFunds): Collection
    {
        $rows = $roleRows->map(fn ($role) => [
            'name' => $role->reksaDana?->nama_reksa_dana,
            'code' => $role->reksaDana?->kode_reksa_dana,
            'role' => self::ROLE_LABELS[$role->role_type] ?? $role->role_type,
            'position' => $role->role_title,
            'source' => $role->source,
        ]);

        foreach ($fallbackFunds as $fund) {
            foreach ($fund->managementTeams as $team) {
                $rows->push([
                    'name' => $fund->nama_reksa_dana,
                    'code' => $fund->kode_reksa_dana,
                    'role' => $team->type === 'committee' ? 'Komite Investasi' : 'Tim Pengelola Investasi',
                    'position' => $team->position,
                    'source' => 'Prospektus',
                ]);
            }
        }

        return $rows->filter(fn ($row) => filled($row['name']))->unique(fn ($row) => implode('|', $row))->values();
    }

    private function managerDetailRows(Collection $roleRows, Collection $fallbackManagers): Collection
    {
        $rows = $roleRows->map(fn ($role) => [
            'name' => $role->investmentManager?->name,
            'role' => self::ROLE_LABELS[$role->role_type] ?? $role->role_type,
            'position' => $role->role_title,
            'source' => $role->source,
        ]);

        foreach ($fallbackManagers as $manager) {
            $rows->push([
                'name' => $manager->name,
                'role' => 'Terkait',
                'position' => null,
                'source' => 'Prospektus',
            ]);
        }

        return $rows->filter(fn ($row) => filled($row['name']))->unique(fn ($row) => implode('|', $row))->values();
    }
}
