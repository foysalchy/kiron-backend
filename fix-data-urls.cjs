const fs = require('fs');
const glob = require('fs').readdirSync('c:/laragon/www/kiron-backend/resources/views/landing')
    .filter(f => f.endsWith('.blade.php'))
    .map(f => 'c:/laragon/www/kiron-backend/resources/views/landing/' + f);

glob.forEach(f => {
    let c = fs.readFileSync(f, 'utf8');
    c = c.replace(/str_starts_with\(\$([a-zA-Z0-9_\[\]'"]+),\s*'blob:'\)/g, "(str_starts_with($$1, 'blob:') || str_starts_with($$1, 'data:'))");
    fs.writeFileSync(f, c);
    console.log('Updated ' + f);
});
