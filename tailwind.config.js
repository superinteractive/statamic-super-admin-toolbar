const plugin = require('tailwindcss/plugin');

module.exports = {
  important: '.super-admin-toolbar',
  content: ['./resources/**/*.vue', './resources/**/*.js', './resources/**/*.css', './resources/**/*.blade.php'],
  theme: {
    extend: {
      boxShadow: {
        'super-toolbar':
          '0px 6px 12px 0px rgba(0,0,0,0.02), 0px 0.5px 1px 0px rgba(0,0,0,0.10), 0px -1px 2px 0px rgba(0,0,0,0.10) inset, 0px 2px 4px 0px rgba(0,0,0,0.10), 0px 1px 1px 0px #FFF inset',
        'toolbar-icon': 'inset 0px 1px 0px 0px rgba(0,0,0,0.20), inset 0px 1px 0px 0px #FFF',
      },
      backgroundImage: {
        'toolbar-gradient': 'linear-gradient(152deg, #DA2FB6 14.12%, #FE1876 91.48%)',
      },
    },
  },
  corePlugins: {
    preflight: false,
    container: false,
  },
  plugins: [
    plugin(function ({ addVariant, e }) {
      addVariant('si-group-toggled', '[si-group-toggled] &');
    }),
  ],
};
