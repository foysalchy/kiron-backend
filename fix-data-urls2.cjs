const fs = require('fs');
const glob = require('fs').readdirSync('c:/laragon/www/kiron-backend/resources/views/landing')
    .filter(f => f.endsWith('.blade.php'))
    .map(f => 'c:/laragon/www/kiron-backend/resources/views/landing/' + f);

glob.forEach(f => {
    let c = fs.readFileSync(f, 'utf8');
    c = c.replace(/\(str_starts_with\(\$1,\s*'blob:'\)\s*\|\|\s*str_starts_with\(\$1,\s*'data:'\)\)/g, "(str_starts_with($feature['image'], 'blob:') || str_starts_with($feature['image'], 'data:'))");
    
    // Also if $1 ended up in other places? Let's just fix it properly globally:
    c = c.replace(/\$1/g, "$feature['image']");

    fs.writeFileSync(f, c);
    console.log('Updated ' + f);
});
