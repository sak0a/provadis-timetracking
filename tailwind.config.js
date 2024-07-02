/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
        "./{login,assets,admin,backend}/**/*.{html,js,php,sass,scss,css}",
  ],
  theme: {
    extend: {
      colors: {
        'primary': '#F7CD45',
        'secondary': '#0F2D3B',
        'normal': '#DCE2E5',
      }
    },
  },
  plugins: [],
}

