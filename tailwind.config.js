import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    safelist: [
        {
            pattern:
                /bg-(red|green|blue|yellow|gray|amber|orange|indigo|purple|pink|teal|cyan|lime|emerald|rose|sky|violet|fuchsia)-(50|100|200|300|400|500|600|700|800|900|950)/,
        },
        {
            pattern:
                /hover:bg-(red|green|blue|yellow|gray|amber|orange|indigo|purple|pink|teal|cyan|lime|emerald|rose|sky|violet|fuchsia)-(50|100|200|300|400|500|600|700|800|900|950)/,
        },
        {
            pattern:
                /text-(red|green|blue|yellow|gray|amber|orange|indigo|purple|pink|teal|cyan|lime|emerald|rose|sky|violet|fuchsia)-(50|100|200|300|400|500|600|700|800|900|950)/,
        },
        {
            pattern:
                /border-(red|green|blue|yellow|gray|amber|orange|indigo|purple|pink|teal|cyan|lime|emerald|rose|sky|violet|fuchsia)-(50|100|200|300|400|500|600|700|800|900|950)/,
        },
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
