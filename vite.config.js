import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Admin layout bundles (core + all shared components)
                'resources/css/admin-bundle.css',
                'resources/js/admin-bundle.js',

                // Auth layout
                'resources/css/auth.css',
                'resources/js/auth.js',

                // Page-specific CSS
                'resources/css/pages/dashboard.css',
                'resources/css/pages/customer-service.css',
                'resources/css/pages/account-settings.css',
                'resources/css/pages/audit-trail.css',
                'resources/css/pages/settings.css',
                'resources/css/pages/notifications.css',
                'resources/css/route-form.css',
                'resources/css/landmark-form.css',

                // Page-specific JS
                'resources/js/pages/dashboard.js',
                'resources/js/pages/customer-service-index.js',
                'resources/js/pages/customer-service-show.js',
                'resources/js/pages/account-settings.js',
                'resources/js/pages/audit-trail.js',
                'resources/js/pages/settings.js',
                'resources/js/pages/notifications.js',
                'resources/js/pages/routes-index.js',
                'resources/js/pages/routes-batch.js',
                'resources/js/pages/landmarks-index.js',
                'resources/js/pages/landmarks-batch.js',
                'resources/js/route-map.js',
                'resources/js/landmark-form.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
