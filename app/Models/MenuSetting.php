<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class MenuSetting extends Model
{
    use CompanyScoped;

    public const TYPE_MENU = 'menu';
    public const TYPE_FEATURE_CATEGORY = 'feature_category';

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'items',
        'status',
    ];

    protected $casts = [
        'items'  => 'array',
        'status' => 'boolean',
    ];

    protected static function levelModelMap(): array
    {
        return [
            'mega_category'  => MegaCategory::class,
            'sub_category'   => SubCategory::class,
            'mini_category'  => MiniCategory::class,
            'extra_category' => ExtraCategory::class,
        ];
    }

    public function isFeatureCategory(): bool
    {
        return $this->type === self::TYPE_FEATURE_CATEGORY;
    }

    /**
     * For type = menu: items already contain everything needed (label, link,
     * visible, order) — return as-is, just sorted by order.
     *
     * For type = feature_category: items only hold {id, level, ref_id, label,
     * visible, order}. Merge in fresh name/slug/image from the actual
     * category tables so nothing goes stale when a category is renamed or
     * gets a new image.
     */
    public function resolvedItems(): array
    {

        $items = collect($this->items ?? []);
        if ($items->isEmpty()) {
            return [];
        }

        if (! $this->isFeatureCategory()) {
            return $items->sortBy('order')->values()->all();
        }

        $map = self::levelModelMap();

        $idsByLevel = $items->groupBy('level')
            ->map(fn ($group) => $group->pluck('ref_id')->unique()->values());

        $freshByLevelAndId = [];
        foreach ($idsByLevel as $level => $ids) {
            if (! isset($map[$level])) {
                continue;
            }
            $freshByLevelAndId[$level] = $map[$level]::query()
                ->whereIn('id', $ids)
                ->get(['id', 'name', 'slug', 'image'])
                ->keyBy('id');
        }

        return $items
            ->map(function ($item) use ($freshByLevelAndId) {
                $fresh = $freshByLevelAndId[$item['level']][$item['ref_id']] ?? null;

                if (! $fresh) {
                    return null; // referenced category was deleted — drop it
                }

                return [
                    'id'      => $item['id'],
                    'level'   => $item['level'],
                    'ref_id'  => $item['ref_id'],
                    'label'   => $item['label'] ?: $fresh->name,
                    'slug'    => $fresh->slug,
                    'image'   => $fresh->image_url,
                    'link'    => "category/{$fresh->slug}",
                    'visible' => $item['visible'] ?? true,
                    'order'   => $item['order'] ?? 0,
                    'product_count' => \App\Models\Product::where('status', 1)
                        ->whereJsonContains($item['level'] . '_ids', (int)$item['ref_id'])
                        ->count(),
                ];
            })
            ->filter()
            ->sortBy('order')
            ->values()
            ->all();
    }
}
