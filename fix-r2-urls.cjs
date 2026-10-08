const fs = require('fs');
const glob = require('fs').readdirSync('c:/laragon/www/kiron-backend/resources/views/landing')
    .filter(f => f.endsWith('.blade.php'))
    .map(f => 'c:/laragon/www/kiron-backend/resources/views/landing/' + f);

glob.forEach(f => {
    let c = fs.readFileSync(f, 'utf8');
    
    // Replace asset('storage/' . $p1) with Storage::disk('r2')->url($p1)
    // But since it might already have the replaced code, let's target the exact generated code from previous run:
    c = c.replace(/asset\('storage\/' \. (\$[a-zA-Z0-9_\[\]'"]+)\)/g, "\\Illuminate\\Support\\Facades\\Storage::disk('r2')->url($1)");
    
    fs.writeFileSync(f, c);
    console.log('Updated ' + f);
});
