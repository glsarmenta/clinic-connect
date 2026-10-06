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
                id: '/',
                name: 'Clinic Connect',
                short_name: 'Clinic Connect',
                description: 'Clinic Management & Family Patient Healthcare Portal',
                theme_color: '#0d9488',
                background_color: '#ffffff',
                display: 'standalone',
                orientation: 'portrait',
                start_url: '/',
                scope: '/',
                icons: [
                    {
                        src: '/pwa-192x192.jpg',
                        sizes: '192x192',
                        type: 'image/jpeg',
                        purpose: 'any'
                    },
                    {
                        src: '/pwa-192x192.jpg',
                        sizes: '192x192',
                        type: 'image/jpeg',
                        purpose: 'maskable'
                    },
                    {
                        src: '/pwa-512x512.jpg',
                        sizes: '512x512',
                        type: 'image/jpeg',
                        purpose: 'any'
                    },
                    {
                        src: '/pwa-512x512.jpg',
                        sizes: '512x512',
                        type: 'image/jpeg',
                        purpose: 'maskable'
                    }
                ]
            }
        })
    ],
});
