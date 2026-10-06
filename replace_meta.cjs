const fs = require('fs');
const path = require('path');

function walkDir(dir, callback) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let isDirectory = fs.statSync(dirPath).isDirectory();
        isDirectory ? walkDir(dirPath, callback) : callback(dirPath);
    });
}

let count = 0;

walkDir('c:/laragon/www/kiron-backend/app/Http/Requests', function(filePath) {
    if (filePath.endsWith('.php')) {
        let content = fs.readFileSync(filePath, 'utf8');
        let originalContent = content;

        // Replace array syntax: ['nullable', 'string'] -> ['nullable', 'string', 'max:255']
        content = content.replace(/'meta_title'\s*=>\s*\[(.*?)\]/g, (match, p1) => {
            if (!p1.includes('max:255')) return match.replace(/\]$/, ", 'max:255']");
            return match;
        });
        content = content.replace(/'meta_description'\s*=>\s*\[(.*?)\]/g, (match, p1) => {
            if (!p1.includes('max:255')) return match.replace(/\]$/, ", 'max:255']");
            return match;
        });
        content = content.replace(/'meta_keywords'\s*=>\s*\[(.*?)\]/g, (match, p1) => {
            if (!p1.includes('max:255') && !p1.includes('string')) return match.replace(/\]$/, "'string', 'max:255']");
            if (!p1.includes('max:255') && p1.includes('string')) return match.replace(/\]$/, ", 'max:255']");
            return match;
        });

        // Replace string syntax: 'nullable|string' -> 'nullable|string|max:255'
        content = content.replace(/'meta_title'\s*=>\s*'([^']*)'/g, (match, p1) => {
            if (!p1.includes('max:255')) return `'meta_title' => '${p1}|max:255'`;
            return match;
        });
        content = content.replace(/'meta_description'\s*=>\s*'([^']*)'/g, (match, p1) => {
            if (!p1.includes('max:255')) return `'meta_description' => '${p1}|max:255'`;
            return match;
        });
        content = content.replace(/'meta_keywords'\s*=>\s*'([^']*)'/g, (match, p1) => {
            if (!p1.includes('max:255')) return `'meta_keywords' => '${p1}|string|max:255'`;
            return match;
        });
        
        // Clean up weird double pipes or commas if any
        content = content.replace(/\|\|/g, '|');
        content = content.replace(/, ,/g, ',');

        if (content !== originalContent) {
            fs.writeFileSync(filePath, content);
            console.log('Updated: ' + filePath);
            count++;
        }
    }
});

console.log('Total files updated: ' + count);
