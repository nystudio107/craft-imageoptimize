/** @type {import('stylelint').Config} */
export default {
  "extends": [
    "stylelint-config-recommended",
    "stylelint-config-standard-scss"
  ],
  "rules": {
    // For TailwindCSS @apply directive
    "at-rule-no-deprecated": {
      "ignoreAtRules": [
        "apply"
      ],
    },
    // For TailwindCSS theme() function properties
    "declaration-property-value-no-unknown": {
      "ignoreProperties": [
        "/^theme/"
      ],
    },
    // For TailwindCSS theme() function
    "function-no-unknown": {
      "ignoreFunctions": [
        "theme"
      ]
    },
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
          "apply",
          "reference",
          "config",
          "plugin",
        ]
      }
    ],
    "no-invalid-position-at-import-rule": null,
    "block-no-empty": null,
    "selector-id-pattern": null,
    "selector-class-pattern": null,
    "no-descending-specificity": null,
    "font-family-no-missing-generic-family-keyword": null
  }
}
