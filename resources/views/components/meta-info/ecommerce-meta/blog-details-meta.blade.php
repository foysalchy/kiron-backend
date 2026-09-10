 @include('components.meta-info.meta', [
        'setup' => $setup,

        'type' => 'BlogPosting',

        'title' => $blog->meta_title ?: $blog->title,

        'description' => $blog->meta_description ?: Str::limit(strip_tags($blog->short), 160),

        'keywords' => is_array($blog->meta_keywords)
            ? implode(',', $blog->meta_keywords)
            : $blog->meta_keywords,

        'image' => !empty($blog->images) && isset($blog->images[0])
            ? \Illuminate\Support\Facades\Storage::disk('r2')->url($blog->images[0])
            : $setup->logo_url,

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

            'published' => $blog->created_at,

            'updated' => $blog->updated_at,
        ],
    ])
