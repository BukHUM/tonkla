/** @type {import('tailwindcss').Config} */
/**
 * Separate sheet for @tailwindcss/typography — only enqueued on content pages.
 */
module.exports = {
	content: [
		'./page.php',
		'./single.php',
		'./parts/content/**/*.php',
	],
	corePlugins: {
		preflight: false,
	},
	theme: {
		extend: {
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
			},
		},
	},
	plugins: [ require( '@tailwindcss/typography' ) ],
};
