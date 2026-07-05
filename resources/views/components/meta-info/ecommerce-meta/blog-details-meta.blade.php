 @include('components.meta-info.meta', [
        'setup' => $setup,

        'type' => 'BlogPosting',

        'title' => $blogPost->meta_title ?: $blogPost->title,

        'description' => $blogPost->meta_description ?: Str::limit(strip_tags($blogPost->short), 160),

        'keywords' => is_array($blogPost->meta_keywords)
            ? implode(',', $blogPost->meta_keywords)
            : $blogPost->meta_keywords,

        'image' => count($blogPost->images)
            ? asset('storage/' . $blogPost->images[0])
            : asset('storage/' . $setup->logo),

        'canonical' => route('blog.details', $blogPost->slug),

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
                'name' => $blogPost->title,
                'url' => route('blog.details', $blogPost->slug),
            ],
        ],

        'schema' => [
            'headline' => $blogPost->title,

            'author' => $setup->founder_name,

            'published' => $blogPost->created_at,

            'updated' => $blogPost->updated_at,
        ],
    ])
