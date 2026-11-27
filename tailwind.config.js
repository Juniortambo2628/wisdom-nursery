import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

// Wisdom Nursery design tokens (Design Concepts Rev 1, Concept B, green palette).
export const palette = {
    forest: '#123524', // headers, footer, dark buttons
    deep: '#0D2A1C',   // footer background
    leaf: '#2D6A3E',   // main buttons, links, active states
    fern: '#4F8A4B',   // eyebrow labels
    moss: '#8FB36B',   // highlights on dark backgrounds
    sage: '#C9DBB4',   // panels (bulk quote band); darker than mist so panels stand out
    mist: '#E4EDD9',   // page background, soft backgrounds, in-cart states
    cream: '#F8F7F1',  // page background
    ink: '#14211A',    // body text
    clay: '#B9822F',   // badges, cart count, "ready in X weeks"
    mpesa: '#3D9B35',  // M-Pesa pay button only
};

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/**/*.php',
    ],
    theme: {
        extend: {
            colors: palette,
            fontFamily: {
                sans: ['"Inter Variable"', 'Inter', ...defaultTheme.fontFamily.sans],
                display: ['"Fraunces Variable"', 'Fraunces', ...defaultTheme.fontFamily.serif],
            },
            borderRadius: {
                card: '18px',
                image: '20px',
                'image-lg': '28px',
            },
            boxShadow: {
                card: '0 1px 2px rgba(20, 33, 26, 0.06)',   // ink at 6%
                lift: '0 12px 32px rgba(20, 33, 26, 0.08)', // floating panels (stats strip)
            },
            maxWidth: {
                page: '1440px',
            },
            spacing: {
                gutter: '72px',
            },
        },
    },
    plugins: [forms],
};
