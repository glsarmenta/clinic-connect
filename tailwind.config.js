import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#f0fbfd',
                    100: '#dcf5f9',
                    200: '#cbeef4',
                    300: '#8ce0ec',
                    400: '#4ecadc',
                    500: '#26b2c8',
                    600: '#1793a9',
                    700: '#16768a',
                    800: '#186070',
                    900: '#18505e',
                    teal: '#38a3b5',
                    'teal-dark': '#0e7490',
                },
                arctic: {
                    50: '#f0f9fb',
                    100: '#e6f7fa',
                    200: '#cbeef4',
                    300: '#bcecf3',
                }
            },
            borderRadius: {
                '3xl': '1.5rem',
                '4xl': '2rem',
                '5xl': '2.5rem',
            }
        },
    },

    plugins: [forms],
};
