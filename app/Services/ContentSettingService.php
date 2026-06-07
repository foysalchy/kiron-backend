<?php

namespace App\Services;

use App\Enums\Status;
use App\Helpers\FileUploadHelper;
use App\Models\ContentSetting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContentSettingService
{


    /* ─────────────────────────────────────────────
     |  GET all entries (optionally filter by page)
     |──────────────────────────────────────────── */
    public function getAll(?string $pageType = null): Collection
    {
        return ContentSetting::when($pageType, fn($q) => $q->byPage($pageType))
            ->ordered()
            ->get();
    }

    /* ─────────────────────────────────────────────
     |  GET single entry
     |──────────────────────────────────────────── */
    public function findOrFail(int $id): ContentSetting
    {
        return ContentSetting::findOrFail($id);
    }

    /* ─────────────────────────────────────────────
     |  CREATE new entry
     |──────────────────────────────────────────── */
    public function store(array $data): ContentSetting
    {
        /* Auto-assign next sort_order within the same page_type */
        if (! isset($data['sort_order'])) {
            $data['sort_order'] = ContentSetting::byPage($data['page_type'])
                ->max('sort_order') + 1;
        }
        if (isset($data['icon'])) {
            $data['icon'] = FileUploadHelper::uploadImage(
                $data['icon'],
                'icons',
              
            );
        }

        return ContentSetting::create([

            'page_type'    => $data['page_type'],
            'icon_url'     => $data['icon_url']     ?? null,
            'icon_file'     => $data['icon']     ?? null,
            'title'        => $data['title']        ?? null,
            'subtitle'     => $data['subtitle']     ?? null,
            'text_content' => $data['text_content'] ?? null,
            'sort_order'   => $data['sort_order'],
            'status'       => $data['status']       ?? Status::Active->value,
        ]);
    }

    /* ─────────────────────────────────────────────
     |  UPDATE existing entry
     |──────────────────────────────────────────── */
    public function update(int $id, array $data): ContentSetting
    {
   
        $setting = $this->findOrFail($id);

        if (isset($data['icon'])) {
            $data['icon'] = FileUploadHelper::uploadImage(
                $data['icon'],
                'icons',
              
            );
        }
        $setting->update([
            'page_type'    => $data['page_type']    ?? $setting->page_type,
            'icon_file'     => $data['icon']     ?? $setting->icon_file,
            'icon_url'     => $data['icon_url']     ?? $setting->icon_url,
            'title'        => $data['title']        ?? $setting->title,
            'subtitle'     => $data['subtitle']     ?? $setting->subtitle,
            'text_content' => $data['text_content'] ?? $setting->text_content,
            'sort_order'   => $data['sort_order']   ?? $setting->sort_order,
            'status'       => $data['status']       ?? $setting->status,
        ]);

        return $setting->fresh();
    }

    /* ─────────────────────────────────────────────
     |  TOGGLE status (active ↔ inactive)
     |──────────────────────────────────────────── */
    public function toggleStatus(int $id): ContentSetting
    {
        $setting = $this->findOrFail($id);

        $setting->update([
            'status' => $setting->status === Status::Active
                ? Status::Inactive->value
                : Status::Active->value,
        ]);

        return $setting->fresh();
    }

    /* ─────────────────────────────────────────────
     |  REORDER — bulk sort_order update
     |──────────────────────────────────────────── */
    public function reorder(array $items): bool
    {
        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                ContentSetting::where('id', $item['id'])
                    ->update(['sort_order' => $item['sort_order']]);
            }
        });

        return true;
    }

    /* ─────────────────────────────────────────────
     |  DELETE
     |──────────────────────────────────────────── */
    public function delete(int $id): bool
    {
        $setting = $this->findOrFail($id);
        return (bool) $setting->delete();
    }
}
