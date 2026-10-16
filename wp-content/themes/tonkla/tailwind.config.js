/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./**/*.php",
        "./assets/js/**/*.js"
    ],
    safelist: [
        {
            pattern: /(bg|text|border|ring)-(brand|slate|gray|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose)-(100|300|400|500|600)/,
            variants: ['hover', 'group-hover', 'focus'],
        },
        'bg-brand/10', 'text-brand',
        'md:grid-cols-3', 'md:grid-cols-4', 'md:grid-cols-5',
        'bg-white', 'bg-light-gray', 'bg-gray', 'bg-dark-gray', 'bg-dark', 'bg-brand',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Sarabun', 'sans-serif'],
                heading: ['Prompt', 'sans-serif'],
            },
            colors: {
                brand: {
                    light: '#34d399',
                    DEFAULT: '#10b981',
                    dark: '#047857',
                },
                n8n: {
                    DEFAULT: '#ea4b71',
                    dark: '#c43c5d',
                    light: '#ff7b9c'
                },
                'dark': '#0f172a', // slate-900
                'dark-gray': '#1e293b', // slate-800
                'gray': '#f1f5f9', // slate-100
                'light-gray': '#f8fafc', // slate-50
                'white': '#ffffff', // Explicit white
            },
            transitionTimingFunction: {
                DEFAULT: 'cubic-bezier(0.22, 1, 0.36, 1)',
            },
            transitionDuration: {
                DEFAULT: '300ms',
            },
            animation: {
                'flow': 'flow 2s infinite linear',
            },
            keyframes: {
                flow: {
                    '0%': { transform: 'translateX(-100%)' },
                    '100%': { transform: 'translateX(100%)' },
                }
            }
        },
    },
    plugins: [
        require('@tailwindcss/typography'),
    ],
}
