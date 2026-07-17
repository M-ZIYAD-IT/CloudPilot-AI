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
                // GT Walsheim Medium is unlicensed here; DESIGN.md names Inter as the
                // documented substitute for display sizes as well as body copy.
                sans: ['InterVariable', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            colors: {
                primary: '#ffffff',
                'on-primary': '#000000',
                'accent-blue': '#0099ff',
                ink: '#ffffff',
                'ink-muted': '#999999',
                canvas: '#090909',
                'surface-1': '#141414',
                'surface-2': '#1c1c1c',
                hairline: '#262626',
                'hairline-soft': '#1a1a1a',
                gradient: {
                    magenta: '#d44df0',
                    violet: '#6a4cf5',
                    orange: '#ff7a3d',
                    coral: '#ff5577',
                },
                success: '#22c55e',
                danger: '#f87171',
            },
            fontSize: {
                'display-xxl': ['110px', { lineHeight: '0.85', letterSpacing: '-5.5px', fontWeight: '500' }],
                'display-xl': ['85px', { lineHeight: '0.95', letterSpacing: '-4.25px', fontWeight: '500' }],
                'display-lg': ['62px', { lineHeight: '1.00', letterSpacing: '-3.1px', fontWeight: '500' }],
                'display-md': ['32px', { lineHeight: '1.13', letterSpacing: '-1.0px', fontWeight: '500' }],
                headline: ['22px', { lineHeight: '1.20', letterSpacing: '-0.8px', fontWeight: '700' }],
                subhead: ['24px', { lineHeight: '1.30', letterSpacing: '-0.01px', fontWeight: '400' }],
                'body-lg': ['18px', { lineHeight: '1.30', letterSpacing: '-0.18px', fontWeight: '400' }],
                body: ['15px', { lineHeight: '1.30', letterSpacing: '-0.15px', fontWeight: '400' }],
                'body-sm': ['14px', { lineHeight: '1.40', letterSpacing: '-0.14px', fontWeight: '500' }],
                caption: ['13px', { lineHeight: '1.20', letterSpacing: '-0.13px', fontWeight: '500' }],
                micro: ['12px', { lineHeight: '1.20', letterSpacing: '-0.12px', fontWeight: '400' }],
                button: ['14px', { lineHeight: '1.0', letterSpacing: '-0.14px', fontWeight: '500' }],
            },
            borderRadius: {
                xs: '4px',
                sm: '6px',
                md: '10px',
                lg: '15px',
                xl: '20px',
                xxl: '30px',
                pill: '100px',
            },
            spacing: {
                hair: '1px',
                xxs: '4px',
                xs: '8px',
                sm: '12px',
                md: '15px',
                lg: '20px',
                xl: '30px',
                xxl: '40px',
                section: '96px',
            },
            boxShadow: {
                'ring-focus': '0 0 0 1px rgba(0, 153, 255, 0.15)',
                'edge-light': 'inset 0 0.5px 0 rgba(255, 255, 255, 0.10), 0 10px 30px rgba(0, 0, 0, 0.25)',
            },
        },
    },

    plugins: [forms],
};
