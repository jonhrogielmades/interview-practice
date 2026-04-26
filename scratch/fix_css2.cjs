const fs = require('fs');
let css = fs.readFileSync('resources/css/app.css', 'utf8');

css = css.replace(/([a-z0-9-]+)\s+!(?=\s|;)/g, '!$1');
css = css.replace(/([a-z0-9-]+):\s+!/g, '$1:!');

fs.writeFileSync('resources/css/app.css', css);
