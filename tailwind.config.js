/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./views/**/*.php",
    "./public/**/*.php"
  ],
  theme: {
    extend: {
      fontFamily: {
        lao: ['Noto Sans Lao', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
