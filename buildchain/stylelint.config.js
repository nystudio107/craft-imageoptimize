/** @type {import('stylelint').Config} */
export default {
  "extends": [
    "stylelint-config-recommended",
    "stylelint-config-standard-scss"
  ],
  "rules": {
    "scss/at-rule-no-unknown": [
      true,
      {
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
