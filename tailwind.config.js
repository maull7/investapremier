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
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#0b1324',
                    light: '#1a2640',
                    dark: '#060b14',
                },
                accent: {
                    DEFAULT: '#0f766e',
                    light: '#14b8a6',
                    dark: '#0a5c56',
                },
                gold: {
                    DEFAULT: '#c9a227',
                    light: '#f59e0b',
                },
                muted: '#475569',
                line: '#e2e8f0',
            },
        },
    },

    plugins: [forms],
};
