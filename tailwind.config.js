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
                    green: '#17D469',
                },
                accent: {
                    DEFAULT: '#0f766e',
                    light: '#14b8a6',
                    dark: '#0a5c56',
                     teal: '#035863',
                },
                gold: {
                    DEFAULT: '#c9a227',
                    light: '#f59e0b',
                },
                secondary: {
                    teal: '#3D6C73',
                },
                icon: '#859F9F',
                border: {
                    custom: '#B7C8C9',
                },
                background: {
                    dashboard: '#DFE4E8',
                },
                cardBg : {
                    bg: '#F3F6F7',
                },
                whiteOne : '#FDFDFD',
                negative: '#DF2E27',
                redDark: '#511B1B',
                muted: '#475569',
                line: '#e2e8f0',
            },
        },
    },

    plugins: [forms],
};
