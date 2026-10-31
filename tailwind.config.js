import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: ['class', '[data-theme="alveo-dark"]'],
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        fontFamily: {
            sans: ['"DM Sans"', 'system-ui', '-apple-system', 'sans-serif'],
            serif: ['"DM Sans"', 'system-ui', '-apple-system', 'sans-serif'],
            mono: ['"DM Sans"', 'system-ui', '-apple-system', 'sans-serif'],
        },
        extend: {
            colors: {
                forest: {
                    50: '#ecfdf5',
                    100: '#d1fae5',
                    200: '#a7f3d0',
                    300: '#6ee7b7',
                    400: '#34d399',
                    500: '#10b981',
                    600: '#059669',
                    700: '#047857',
                    800: '#065f46',
                    900: '#064e3b',
                    950: '#022c22',
                },
            },
        },
    },
    plugins: [daisyui],
    daisyui: {
        themes: [
            {
                alveo: {
                    'primary': '#064e3b',
                    'primary-content': '#ffffff',
                    'secondary': '#047857',
                    'secondary-content': '#ffffff',
                    'accent': '#059669',
                    'accent-content': '#ffffff',
                    'neutral': '#0f172a',
                    'neutral-content': '#f8fafc',
                    'base-100': '#ffffff',
                    'base-200': '#f8fafc',
                    'base-300': '#e2e8f0',
                    'base-content': '#0f172a',
                    'info': '#0284c7',
                    'success': '#047857',
                    'warning': '#d97706',
                    'error': '#dc2626',
                },
                'alveo-dark': {
                    'primary': '#059669',
                    'primary-content': '#ffffff',
                    'secondary': '#047857',
                    'secondary-content': '#ffffff',
                    'accent': '#10b981',
                    'accent-content': '#022c22',
                    'neutral': '#1e293b',
                    'neutral-content': '#f8fafc',
                    'base-100': '#0f172a',
                    'base-200': '#090e1a',
                    'base-300': '#1e293b',
                    'base-content': '#f1f5f9',
                    'info': '#38bdf8',
                    'success': '#10b981',
                    'warning': '#f59e0b',
                    'error': '#ef4444',
                },
            },
        ],
        darkTheme: 'alveo-dark',
        logs: false,
    },
};

