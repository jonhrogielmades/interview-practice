const fs = require('fs');
let css = fs.readFileSync('resources/css/app.css', 'utf8');

// Fix formatter errors like `dark: !text-gray-400` -> `dark:!text-gray-400`
// Also `hover: !`, `2xsm: !`, etc.
css = css.replace(/([a-z0-9-]+):\s+!/g, '$1:!');

fs.writeFileSync('resources/css/app.css', css);
