import js from '@eslint/js';
import importPlugin from 'eslint-plugin-import';
import react from 'eslint-plugin-react';
import globals from 'globals';

export default [
  js.configs.recommended,
  {
    files: ['**/*.{js,jsx}'],
    plugins: {
      import: importPlugin,
      react,
    },
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        ...globals.browser,
        ...globals.node,
      },
      parserOptions: {
        ecmaFeatures: {
          jsx: true,
        },
      },
    },
    settings: {
      react: {
        version: 'detect',
      },
    },
    rules: {
      // Reglas de import - estas detectarán errores como el que tuviste
      'import/named': 'error',
      'import/default': 'error',
      'import/no-unresolved': 'off', // Desactivado porque puede dar falsos positivos sin resolver aliases

      // Reglas básicas de React
      'react/jsx-uses-react': 'error',
      'react/jsx-uses-vars': 'error',
    },
  },
  {
    ignores: ['node_modules/', 'vendor/', 'public/', 'storage/'],
  },
];
