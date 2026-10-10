const fs = require('fs');

const files = [
    'c:/laragon/www/kiron-backend/resources/views/landing/landing1.blade.php',
    'c:/laragon/www/kiron-backend/resources/views/landing/landing2.blade.php',
    'c:/laragon/www/kiron-backend/resources/views/landing/landing3.blade.php',
];

for (const file of files) {
    let content = fs.readFileSync(file, 'utf8');

    // Rename tags to exactly match the builder
    content = content.replace(/>Landing Name\*/g, '>Title');
    // For landing2, $landing->name is used. We should label it "Landing Name"
    if (file.includes('landing2')) {
        // Line 181: @if(isset($isPreview) && $isPreview)<span class="preview-badge" style="top: -20px; color: white;">Title</span>@endif
        // wait, I replaced all Landing Name* to Title. I'll fix landing2's specific one back to Landing Name.
        content = content.replace(
            `@if(isset($isPreview) && $isPreview)<span class="preview-badge" style="top: -20px; color: white;">Title</span>@endif\n                        {!! $landing->name !!}`,
            `@if(isset($isPreview) && $isPreview)<span class="preview-badge" style="top: -20px; color: white;">Landing Name</span>@endif\n                        {!! $landing->name !!}`
        );
        content = content.replace(
            `@if(isset($isPreview) && $isPreview)<span class="preview-badge" style="top: -20px; color: white;">Title</span>@endif\r\n                        {!! $landing->name !!}`,
            `@if(isset($isPreview) && $isPreview)<span class="preview-badge" style="top: -20px; color: white;">Landing Name</span>@endif\r\n                        {!! $landing->name !!}`
        );
    }
    
    content = content.replace(/>Video\/Image\*/g, '>Thumbnail / Video');
    content = content.replace(/>Offer Price\*/g, '>Discount Price');
    content = content.replace(/>Regular Price\*/g, '>Regular Price');
    content = content.replace(/>Description\*/g, '>Product Details');
    content = content.replace(/>Short Description\*/g, '>Short Description');
    content = content.replace(/>Long Description\*/g, '>Product Details');

    fs.writeFileSync(file, content);
}

console.log('Update complete');
