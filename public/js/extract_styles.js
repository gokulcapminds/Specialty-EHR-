const fs = require('fs');

const appJsPath = 'c:/wamp64/www/pf_ehr/public/js/app.js';
const content = fs.readFileSync(appJsPath, 'utf8');

const stylePattern = /style="([^"]+)"/g;
let match;
const uniqueStyles = new Set();

while ((match = stylePattern.exec(content)) !== null) {
    uniqueStyles.add(match[1]);
}

const stylesArray = Array.from(uniqueStyles);
console.log(`Found ${stylesArray.length} unique styles.`);

stylesArray.forEach((style, i) => {
    console.log(`Style ${i}: ${style}`);
});
