// Utilities for the dashboard's own views (photo library, picker). Only these files are
// scanned, preflight is off, and colours come from Filament's CSS variables, so this
// adds a few classes on top of Filament's stylesheet without changing anything else.
const shades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];
const fromVars = (name) => Object.fromEntries(shades.map((s) => [s, `rgba(var(--${name}-${s}), <alpha-value>)`]));

/** @type {import('tailwindcss').Config} */
export default {
    content: ['./resources/views/filament/**/*.blade.php', './app/Filament/**/*.php'],
    darkMode: 'class',
    corePlugins: { preflight: false },
    theme: {
        extend: {
            colors: { primary: fromVars('primary'), gray: fromVars('gray'), danger: fromVars('danger'), success: fromVars('success') },
        },
    },
};
