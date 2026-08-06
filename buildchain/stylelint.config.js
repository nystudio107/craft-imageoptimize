/** @type {import('stylelint').Config} */
export default {
  "extends": [
    "stylelint-config-recommended",
    "stylelint-config-standard-scss",
    "stylelint-config-recommended-vue"
  ],
  "rules": {
    // For TailwindCSS @apply directive
    "at-rule-no-deprecated": [
      true,
      {
        "ignoreAtRules": [
          "apply"
        ],
      }
    ],
    // For TailwindCSS theme() function properties
    "declaration-property-value-no-unknown": [
      true,
      {
        "ignoreProperties": {
          "/.+/": "/^.*?theme/"
        }
      },
    ],
    // For TailwindCSS theme() function
    "function-no-unknown": [
      true,
      {
        "ignoreFunctions": [
          "screen",
          "theme"
        ]
      },
    ],
    "scss/at-rule-no-unknown": [
      true,
      {
        // For TailwindCSS custom @ directives
        "ignoreAtRules": [
          "theme",
          "source",
          "utility",
          "variant",
          "custom-variant",
          "plugin",
          "tailwind",
          "apply",
          "layer",
          "config",
          "variants",
          "responsive",
          "screen"
        ]
      }
    ],
    "no-descending-specificity": null,
    "no-invalid-position-at-import-rule": null,
    "import-notation": null,
    "block-no-empty": null,
    "selector-id-pattern": null,
    "selector-class-pattern": null,
    "font-family-no-missing-generic-family-keyword": null
  }
}
