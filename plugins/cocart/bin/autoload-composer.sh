#!/bin/bash

# Specify the path to your composer.json file
COMPOSER_JSON_PATH="composer.json"

# Specify the autoload configuration
AUTOLAOD_CONFIG='{
  "autoload": {
    "exclude-from-classmap": [
      "includes/classes/legacy",
      "includes/classes/rest-api/legacy"
    ],
    "classmap": [
      "includes",
      "packages/admin/includes",
      "packages/compatibility/includes",
      "packages/products-api/includes",
      "packages/session-api/includes",
      "packages/third-party/includes"
    ],
    "psr-4": {
      "CoCart\\": "src/",
      "CoCart\\Admin\\": "packages/admin/includes/",
      "CoCart\\Compatibility\\Modules\\": "packages/compatibility/includes/modules/",
      "CoCart\\ProductsAPI\\": "packages/products-api/includes/",
      "CoCart\\SessionAPI\\": "packages/session-api/includes/",
      "CoCart\\ThirdParty\\": "packages/third-party/includes/"
    }
  }
}'

# Create a temporary file
TMP_FILE=$(mktemp)

# Merge autoload configurations with composer.json
jq --argjson config "$AUTOLAOD_CONFIG" '. + $config' "$COMPOSER_JSON_PATH" > "$TMP_FILE"

# Overwrite composer.json with the updated contents
mv "$TMP_FILE" "$COMPOSER_JSON_PATH"
