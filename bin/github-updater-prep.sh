#!/bin/bash
# github-updater-prep.sh
# Usage: ./github-updater-prep.sh "path/to/main-plugin-file" "plugin-slug" "owner/repository"

MAIN_FILE_LOCATION="$1"
PLUGIN_NAME="$2"
REPO_NAME="$3"
TAG="\$tag"

# Define the new headers to add
NEW_HEADERS=$(cat <<-END
 * GitHub Plugin URI: $REPO_NAME
 * Primary Branch: trunk
 * Release Asset: ${PLUGIN_NAME}-github-v${TAG}.zip
END
)

# Backup the original file
cp "${MAIN_FILE_LOCATION}${PLUGIN_NAME}.php" "${MAIN_FILE_LOCATION}${PLUGIN_NAME}.bak"

# Check if the headers are already present to avoid duplicates
if ! grep -q "GitHub Plugin URI: $REPO_NAME" "${MAIN_FILE_LOCATION}${PLUGIN_NAME}.php"; then
  # Use awk to insert the new headers right after "Requires PHP" line without adding an extra blank line
  awk -v new_headers="$NEW_HEADERS" '
    BEGIN { added = 0 }
    /Requires PHP:/ && !added {
      print $0       # Print the current line (Requires PHP)
      print new_headers  # Print new headers after Requires PHP
      added = 1
      next
    }
    { print $0 }  # Print the rest of the file as is
  ' "${MAIN_FILE_LOCATION}${PLUGIN_NAME}.php" > "${MAIN_FILE_LOCATION}${PLUGIN_NAME}.tmp" && mv "${MAIN_FILE_LOCATION}${PLUGIN_NAME}.tmp" "${MAIN_FILE_LOCATION}${PLUGIN_NAME}.php"
else
  echo "Headers already present in ${MAIN_FILE_LOCATION}${PLUGIN_NAME}.php"
fi

# Delete the backup file after successful modification
rm -f "${MAIN_FILE_LOCATION}${PLUGIN_NAME}.bak"