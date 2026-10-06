import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import postcss from 'postcss';

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const fontAwesomeCssPath = path.join(
    projectRoot,
    'node_modules',
    '@fortawesome',
    'fontawesome-free',
    'css',
    'all.min.css',
);
const outputPath = path.join(path.dirname(fontAwesomeCssPath), 'all-used.min.css');
const scanRoots = [
    ...['template1', 'template2', 'template3', 'template4', 'template5', 'landing', 'saas']
        .map((directory) => path.join(projectRoot, 'resources', 'views', directory)),
    path.join(projectRoot, 'resources', 'views', 'components'),
];
const usedIconClasses = new Set([
    'fa-facebook',
    'fa-instagram',
    'fa-whatsapp',
]);

function collectFiles(directory) {
    if (!fs.existsSync(directory)) return [];

    return fs.readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
        const filePath = path.join(directory, entry.name);

        if (entry.isDirectory()) return collectFiles(filePath);
        return /\.(?:blade\.php|php|js|css)$/.test(entry.name) ? [filePath] : [];
    });
}

for (const filePath of scanRoots.flatMap(collectFiles)) {
    const source = fs.readFileSync(filePath, 'utf8');

    for (const match of source.matchAll(/\bfa-[a-z0-9-]+\b/gi)) {
        usedIconClasses.add(match[0].toLowerCase());
    }
}

const stylesheet = fs.readFileSync(fontAwesomeCssPath, 'utf8');
const root = postcss.parse(stylesheet, { from: fontAwesomeCssPath });
const includedClasses = new Set();

root.walkRules((rule) => {
    const hasGlyph = rule.nodes?.some((node) => (
        node.type === 'decl'
        && node.prop === 'content'
        && /^(['"])\\[0-9a-f]{1,6}\1$/i.test(node.value.trim())
    ));

    if (!hasGlyph) return;

    const selectorClasses = [...rule.selector.matchAll(/\.((?:fa-)[a-z0-9-]+)/gi)]
        .map((match) => match[1].toLowerCase());

    if (!selectorClasses.some((className) => usedIconClasses.has(className))) {
        rule.remove();
        return;
    }

    for (const className of selectorClasses) {
        includedClasses.add(className);
    }
});

fs.writeFileSync(outputPath, root.toString());

console.log(
    `Generated shared Font Awesome CSS from package: `
    + `${includedClasses.size} icon classes, `
    + `${Buffer.byteLength(root.toString())} bytes.`,
);
