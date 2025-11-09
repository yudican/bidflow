const defaultTheme = require("tailwindcss/defaultTheme")
// const darkMode =
//   JSON.parse(localStorage.getItem("user_data")).dark_mode === 1 ? true : false;

module.exports = {
  darkMode: "media",
  content: [
    "./vendor/rappasoft/laravel-livewire-tables/resources/views/**/*.blade.php",
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
    "./vendor/laravel/jetstream/**/*.blade.php",
    "./vendor/wire-elements/modal/resources/views/*.blade.php",
    "./storage/framework/views/*.php",
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.{js,jsx,ts,tsx}",
    "./resources/views/livewire/datatables/**/*.blade.php",
    "./node_modules/flowbite-react/**/*.{js,jsx,ts,tsx}",
  ],
  theme: {
    extend: {
      animation: {
        "gradient-x": "gradient-x 15s ease infinite",
        "gradient-y": "gradient-y 15s ease infinite",
        "gradient-xy": "gradient-xy 15s ease infinite",
      },
      keyframes: {
        "gradient-y": {
          "0%, 100%": {
            "background-size": "400% 400%",
            "background-position": "center top",
          },
          "50%": {
            "background-size": "200% 200%",
            "background-position": "center center",
          },
        },
        "gradient-x": {
          "0%, 100%": {
            "background-size": "200% 200%",
            "background-position": "left center",
          },
          "50%": {
            "background-size": "200% 200%",
            "background-position": "right center",
          },
        },
        "gradient-xy": {
          "0%, 100%": {
            "background-size": "400% 400%",
            "background-position": "left center",
          },
          "50%": {
            "background-size": "200% 200%",
            "background-position": "right center",
          },
        },
      },
      fontFamily: {
        sans: ["Mulish", ...defaultTheme.fontFamily.sans],
      },
      boxShadow: {
        right: "12px 0 15px -4px rgba(0, 0, 0, 0.3)",
        left: "-12px 0 15px -4px rgba(0, 0, 0, 0.3)",
        horizontal: "0px 0 15px -4px rgba(0, 0, 0, 0.3)",
        top: "0 -12px 30px -4px rgba(0, 0, 0, 0.3)",
        bottom: "0 12px 15px -4px rgba(0, 0, 0, 0.3)",
        flimty: "0 2px 4px 0 rgba(0,0,0,0.15)",
      },
      colors: ({ colors }) => ({
        neutralColor: "#808080",
        orangeButton: "#FE8311",
        movementColor: "#004AA6",
        blueColor: "#008BE1",
        mainColor: "#7C9B3A",
        grayColor: "#C4C4C4",
        secondaryColor: "#FFE55B",
        secondaryOutlineColor: "#FFC120",
        greenCheckColor: "#009688",
        greenApproveColor: "#00964E",
        mainText: "#0C1A30",
        secondaryText: "#4E4E4E",
        grayText: "#C4C4C4",
        link: "#FE3A30",
        grayDivider: "#C9C9C9",
        white: "#FFFFFF",
        inherit: colors.inherit,
        current: colors.current,
        transparent: colors.transparent,
        black: "#0C1A30",
        // white: colors.white,
        slate: colors.slate,
        gray: colors.gray,
        zinc: colors.zinc,
        neutral: colors.neutral,
        stone: colors.stone,
        red: colors.red,
        orange: colors.orange,
        amber: colors.amber,
        yellow: colors.yellow,
        lime: colors.lime,
        green: colors.green,
        emerald: colors.emerald,
        teal: colors.teal,
        cyan: colors.cyan,
        sky: colors.sky,
        blue: colors.blue,
        indigo: colors.indigo,
        violet: colors.violet,
        purple: colors.purple,
        fuchsia: colors.fuchsia,
        pink: colors.pink,
        rose: colors.rose,
      }),
      display: ["group-hover"],
    },
  },

  variants: {
    extend: {
      opacity: ["disabled"],
    },
  },

  plugins: [
    require("@tailwindcss/forms"),
    require("@tailwindcss/typography"),
    require("flowbite/plugin"),
    require("@tailwindcss/line-clamp"),
    require("tailwind-scrollbar-hide"),
  ],
}
