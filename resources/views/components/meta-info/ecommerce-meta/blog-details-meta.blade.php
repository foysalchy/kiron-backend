 @include('components.meta-info.meta', [
        'setup' => $setup,

        'type' => 'BlogPosting',

        'title' => $blog->meta_title ?: $blog->title,

        'description' => $blog->meta_description ?: Str::limit(strip_tags($blog->short), 160),

        'keywords' => is_array($blog->meta_keywords)
            ? implode(',', $blog->meta_keywords)
            : $blog->meta_keywords,

        'image' => count($blog->images)
            ? asset('storage/' . $blog->images[0])
            : asset('storage/' . $setup->logo),

        'canonical' => route('blog.details', $blog->slug),

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
                'url' => route('blog.details', $blog->slug),
            ],
        ],

        'schema' => [
            'headline' => $blog->title,

            'author' => $setup->founder_name,

            'published' => $blog->created_at,

            'updated' => $blog->updated_at,
        ],
    ])
