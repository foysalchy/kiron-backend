const fs = require('fs');
const path = require('path');

const requestsDir = path.join(__dirname, 'app/Http/Requests');

function walkDir(dir, callback) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let isDirectory = fs.statSync(dirPath).isDirectory();
        isDirectory ? walkDir(dirPath, callback) : callback(dirPath);
    });
}

walkDir(requestsDir, (filePath) => {
    if (filePath.endsWith('.php')) {
        let content = fs.readFileSync(filePath, 'utf8');
        let modified = false;

        // Replace array syntax: ['nullable', 'string', 'max:255'] -> ['nullable', 'string']
        // using regex that matches meta_keywords line
        let newContent = content.replace(/('meta_keywords'\s*=>\s*\[[^\]]*)'max:255'\s*,?\s*([^\]]*\])/g, (match, p1, p2) => {
            modified = true;
            return p1 + p2;
        });

        // Replace string syntax: 'nullable|string|max:255' -> 'nullable|string'
        newContent = newContent.replace(/('meta_keywords'\s*=>\s*'[^']*)(\|max:255)([^']*')/g, (match, p1, p2, p3) => {
            modified = true;
            return p1 + p3;
        });
        
        // Clean up trailing commas in array if it was ['nullable', 'string', ] -> ['nullable', 'string']
        newContent = newContent.replace(/('meta_keywords'\s*=>\s*\[.*?),\s*\]/g, '$1]');
        
        if (modified) {
            fs.writeFileSync(filePath, newContent);
            console.log('Updated:', filePath);
        }
    }
});
