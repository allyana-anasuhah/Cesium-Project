/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./*.{html,js}","./node_modules/flowbite/**/*.js"],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#2f3e5a',
          darken: '#212b3f',
        },
        secondary: {
          DEFAULT: '#6db2e0',
          light: '#cfdeeb',
          darken: '#5499C7',
        },
        tertiary: {
          DEFAULT: '#ff794e',
          light: '#ff8660',
        },
        background: {
          DEFAULT: '#E1E2E1',
          darken: '#caccca',
        },
        surface: {
          DEFAULT: '#F5F5F6',
          darken: '#E5E5E5',
        },
        top: {
          DEFAULT: '#ffffff',
        },
        onPrimary: {
          DEFAULT: '#ffffff',
        },
        onSurface: {
          DEFAULT: '#4d4d4d',
        },
        border: {
          DEFAULT: '#dadada',
        },
        checked: {
          DEFAULT: '#5499c7',
        },
      }
    },
  },
  plugins: [
    require('flowbite/plugin')
  ],
}

