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
				heading: [ 'Prompt', 'sans-serif' ],
			},
			colors: {
				brand: {
					light: '#34d399',
					DEFAULT: '#10b981',
					dark: '#047857',
				},
			},
		},
	},
	plugins: [ require( '@tailwindcss/typography' ) ],
};
