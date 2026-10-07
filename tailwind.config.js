import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#f0f7f4',
                    100: '#dceee6',
                    200: '#bbdccf',
                    300: '#8fc4b1',
                    400: '#5fa68e',
                    500: '#3f8a73',
                    600: '#2f6f5c',
                    700: '#285a4c',
                    800: '#23483e',
                    900: '#1f3c35',
                    950: '#0d211d',
                },
                gold: {
                    200: '#f2e3b3',
                    300: '#e8cd7a',
                    400: '#dcb85c',
                    500: '#c9a227',
                    600: '#a9851d',
                },
            },
            fontFamily: {
                sans: ['Instrument Sans', ...defaultTheme.fontFamily.sans],
                display: ['Fraunces', 'Georgia', 'serif'],
            },
            boxShadow: {
                soft: '0 1px 2px rgba(16, 28, 24, 0.04), 0 12px 32px -18px rgba(16, 28, 24, 0.25)',
            },
        },
    },

    plugins: [forms],
};
