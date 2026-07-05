@php
$pageData = \App\Services\Saas\SystemPageService::get(
\App\Enums\SystemPageType::FAQ,
$setup->company_id ?? null
);
@endphp

@include('components.meta-info.meta', [
'setup' => $setup,
'type' => 'FAQPage',

'title' => $pageData->meta_title ?? 'Frequently Asked Questions',
'description' => $pageData->meta_description ?? 'Frequently Asked Questions',

'faq' => $faqs->map(function ($item) {
return [
'question' => $item->title,
'answer' => strip_tags($item->content),
];
})->toArray(),
])
