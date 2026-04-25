import os from 'os';
import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

function resolveLanHost() {
    const interfaces = os.networkInterfaces();

    for (const addresses of Object.values(interfaces)) {
        if (!addresses) {
            continue;
        }

        for (const address of addresses) {
            if (address.family !== 'IPv4' || address.internal) {
                continue;
            }

            if (
                address.address.startsWith('10.') ||
                address.address.startsWith('172.') ||
                address.address.startsWith('192.168.') ||
                address.address.startsWith('169.254.')
            ) {
                return address.address;
            }
        }
    }

    return '127.0.0.1';
}

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const devServerHost = env.VITE_DEV_SERVER_HOST || resolveLanHost();
    const devServerPort = Number(env.VITE_DEV_SERVER_PORT || 5173);

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            tailwindcss(),
        ],
        css: {
            // Prevent Vite from inheriting a parent PostCSS config outside this app.
            postcss: {
                plugins: [],
            },
        },
        server: {
            host: '0.0.0.0',
            port: devServerPort,
            strictPort: true,
            origin: `http://${devServerHost}:${devServerPort}`,
            hmr: {
                host: devServerHost,
                port: devServerPort,
            },
        },
    };
});
