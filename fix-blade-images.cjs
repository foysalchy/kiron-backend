const fs = require('fs');
const glob = require('fs').readdirSync('c:/laragon/www/kiron-backend/resources/views/landing')
    .filter(f => f.endsWith('.blade.php'))
    .map(f => 'c:/laragon/www/kiron-backend/resources/views/landing/' + f);

glob.forEach(f => {
    let c = fs.readFileSync(f, 'utf8');
    c = c.replace(/asset\(\s*['"]storage\/['"]\s*\.\s*(\$[a-zA-Z0-9_\[\]'"]+)\s*\)/g, 
    "is_array($1) ? ($1['previewUrl'] ?? '') : (str_starts_with($1, 'blob:') ? $1 : asset('storage/' . $1))");
    fs.writeFileSync(f, c);
    console.log('Updated ' + f);
});
