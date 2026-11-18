import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '127.0.0.1', // Forces IPv4 server binding
        hmr: {
            host: '127.0.0.1', // Forces the HMR WebSocket to match IPv4
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});