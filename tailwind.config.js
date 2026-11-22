import defaultTheme from 'tailwindcss/defaultTheme';
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
            colors: {
                primary: {
                    DEFAULT: '#166534',
                    hover: '#14532D',
                    tint: '#DCFCE7',
                    50: '#F0FDF4',
                    100: '#DCFCE7',
                    200: '#BBF7D0',
                    300: '#86EFAC',
                    400: '#4ADE80',
                    500: '#22C55E',
                    600: '#16A34A',
                    700: '#15803D',
                    800: '#166534',
                    900: '#14532D',
                    950: '#052E16',
                },
                surface: {
                    page: '#F6F7F5',
                    card: '#FFFFFF',
                    border: '#E5E7EB',
                },
                content: {
                    primary: '#111827',
                    secondary: '#6B7280',
                    muted: '#9CA3AF',
                },
                status: {
                    success: '#16A34A',
                    'success-bg': '#DCFCE7',
                    warning: '#D97706',
                    'warning-bg': '#FEF3C7',
                    danger: '#DC2626',
                    'danger-bg': '#FEE2E2',
                    info: '#2563EB',
                    'info-bg': '#DBEAFE',
                },
                // Aliases for seamless legacy class backward compatibility
                forest: {
                    darkest: '#166534',
                    pine: '#14532D',
                    spruce: '#FFFFFF',
                    jade: '#166534',
                    sage: '#6B7280',
                    mint: '#DCFCE7',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                heading: ['Poppins', ...defaultTheme.fontFamily.sans],
                poppins: ['Poppins', ...defaultTheme.fontFamily.sans],
                inter: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                'subtle': '0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05)',
                'card': '0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px 0 rgba(0, 0, 0, 0.02)',
            },
        },
    },

    plugins: [forms],
};
