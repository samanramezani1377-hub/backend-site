<?php
namespace WooGit\Backend;

defined('ABSPATH') || exit;

final class AnnouncementService
{
    private const OPTION = 'woogit_backend_announcements';
    private const MAX = 100;

    public function all(): array
    {
        $items = get_option(self::OPTION, []);
        return is_array($items) ? array_values($items) : [];
    }

    public function active(?string $appVersion = null): array
    {
        $now = time();
        $items = [];
        foreach ($this->all() as $item) {
            if (!is_array($item)) continue;
            $starts = !empty($item['starts_at']) ? strtotime((string)$item['starts_at']) : null;
            $expires = !empty($item['expires_at']) ? strtotime((string)$item['expires_at']) : null;
            if ($starts !== null && $starts > $now) continue;
            if ($expires !== null && $expires <= $now) continue;
            if (!$this->targetsVersion($item, $appVersion)) continue;
            $items[] = $item;
        }
        usort($items, static fn(array $a, array $b): int => ((int)($b['priority'] ?? 0)) <=> ((int)($a['priority'] ?? 0)));
        return array_slice($items, 0, self::MAX);
    }

    private function targetsVersion(array $item, ?string $version): bool
    {
        $versions = $item['app_versions'] ?? [];
        if (!is_array($versions) || $versions === []) return true;
        return $version !== null && in_array($version, array_map('strval', $versions), true);
    }
}
