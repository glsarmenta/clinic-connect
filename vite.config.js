import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        VitePWA({
            registerType: 'autoUpdate',
            outDir: 'public',
            buildBase: '/',
            scope: '/',
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg}']
            },
            manifest: {
                name: 'Clinic Connect',
                short_name: 'Clinic',
                description: 'Clinic Management App',
                theme_color: '#ffffff',
                icons: [
                    {
                        src: 'pwa-192x192.jpg',
                        sizes: '192x192',
                        type: 'image/jpeg'
                    },
                    {
                        src: 'pwa-512x512.jpg',
                        sizes: '512x512',
                        type: 'image/jpeg'
                    }
                ]
            }
        })
    ],
});
