import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const devServerHost = env.VITE_DEV_SERVER_HOST || '127.0.0.1';
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
