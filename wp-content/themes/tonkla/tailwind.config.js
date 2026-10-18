/** @type {import('tailwindcss').Config} */
/**
 * Safelist = only classes built dynamically in PHP
 * (tonkla_icon_color_classes / tonkla_bg_color_options / process grid cols).
 * Static class="..." strings are picked up via content paths.
 * Keep content narrow — do NOT scan admin / Carbon field definition files.
 */
const iconPalette = [
	'slate',
	'red',
	'orange',
	'amber',
	'yellow',
	'lime',
	'green',
	'emerald',
	'teal',
	'cyan',
	'sky',
	'blue',
	'indigo',
	'violet',
	'purple',
	'fuchsia',
	'pink',
	'rose',
];

/** Matches tonkla_icon_color_classes() + feature-split dynamic hovers. */
const iconSafelist = iconPalette.flatMap( ( color ) => [
	`bg-${color}-100`,
	`bg-${color}-500`,
	`text-${color}-500`,
	`text-${color}-600`,
	`hover:border-${color}-500`,
	`group-hover:bg-${color}-500`,
	`group-hover:text-${color}-500`,
] );

module.exports = {
	content: [
		'./*.php',
		'./parts/**/*.php',
		'./inc/builder/icons.php',
		'./inc/class-*.php',
		'./inc/menu-helpers.php',
		'./inc/sitemap.php',
		'./assets/js/main.js',
		'./assets/js/newsletter.js',
	],
	safelist: [
		// Page Builder bg_color (tonkla_bg_color_options)
		'bg-white',
		'bg-light',
		'bg-light-gray',
		'bg-gray',
		'bg-dark-gray',
		'bg-dark',
		'bg-brand',
		'bg-brand/10',
		'bg-brand/5',
		'text-brand',
		'hover:border-brand',
		'group-hover:bg-brand',
		'group-hover:text-brand',
		'hover:border-n8n',
		'group-hover:bg-n8n',
		'group-hover:text-n8n',
		// process.php: md:grid-cols-{n}
		'md:grid-cols-1',
		'md:grid-cols-2',
		'md:grid-cols-3',
		'md:grid-cols-4',
		'md:grid-cols-5',
		// Hero / CMS headline HTML (stored in DB — not visible to content scanner)
		'text-transparent',
		'bg-clip-text',
		'bg-gradient-to-r',
		'bg-gradient-to-tr',
		'bg-gradient-to-br',
		'from-blue-400',
		'from-blue-500',
		'from-brand',
		'from-brand-light',
		'from-slate-900',
		'to-brand-light',
		'to-blue-500',
		'to-yellow-300',
		'to-slate-800',
		...iconSafelist,
	],
	theme: {
		extend: {
			// Values come from :root in input.css; overridden per site by inc/appearance.php.
			fontFamily: {
				sans: [ 'var(--tk-font-body)' ],
				heading: [ 'var(--tk-font-heading)' ],
			},
			colors: {
				brand: {
					light: 'rgb(var(--tk-brand-light) / <alpha-value>)',
					DEFAULT: 'rgb(var(--tk-brand) / <alpha-value>)',
					dark: 'rgb(var(--tk-brand-dark) / <alpha-value>)',
				},
				n8n: {
					DEFAULT: '#ea4b71',
					dark: '#c43c5d',
					light: '#ff7b9c',
				},
				dark: '#0f172a',
				'dark-gray': '#1e293b',
				gray: '#f1f5f9',
				'light-gray': '#f8fafc',
				light: '#f8fafc',
				white: '#ffffff',
			},
			transitionTimingFunction: {
				DEFAULT: 'cubic-bezier(0.22, 1, 0.36, 1)',
			},
			transitionDuration: {
				DEFAULT: '300ms',
			},
			animation: {
				flow: 'flow 2s infinite linear',
			},
			keyframes: {
				flow: {
					'0%': { transform: 'translateX(-100%)' },
					'100%': { transform: 'translateX(100%)' },
				},
			},
		},
	},
	plugins: [],
};
