 @include('components.meta-info.meta', [
        'setup' => $setup,

        'type' => 'BlogPosting',

        'title' => ($blog->meta_title ?? null) ?: $blog->title,

        'description' => ($blog->meta_description ?? null) ?: Str::limit(strip_tags($blog->short ?? ''), 160),

        'keywords' => is_array($blog->meta_keywords ?? null)
            ? implode(',', $blog->meta_keywords)
            : ($blog->meta_keywords ?? ''),

        'image' => !empty($blog->thumbnail_url)
            ? $blog->thumbnail_url
            : (!empty($blog->images) && isset($blog->images[0])
                ? \Illuminate\Support\Facades\Storage::disk('r2')->url($blog->images[0])
                : $setup->logo_url),

        'canonical' => url($blog->slug),

        'breadcrumb' => [
            [
                'name' => 'Home',
                'url' => url('/'),
            ],
            [
                'name' => 'Blog',
                'url' => route('blog.index'),
            ],
            [
                'name' => $blog->title,
                'url' => url($blog->slug),
            ],
        ],

        'schema' => [
            'headline' => $blog->title,

            'author' => $setup->founder_name,

            'published' => $blog->created_at ?? now(),

            'updated' => $blog->updated_at ?? ($blog->created_at ?? now()),
        ],
    ])
