#!/bin/bash

# Controleren of er een mapnaam is opgegeven
if [ -z "$1" ]; then
  echo "Gebruik: $0 <folder>"
  exit 1
fi

# De opgegeven map
PLUGIN_DIR="$1"

# Controleren of de opgegeven map bestaat
if [ ! -d "$PLUGIN_DIR" ]; then
  echo "De map '$PLUGIN_DIR' bestaat niet."
  exit 1
fi

# De naam van de plugin-map, gebruikt voor het zipbestand
PLUGIN_NAME=$(basename "$PLUGIN_DIR")

# De naam van het uiteindelijke zip-bestand
ZIP_FILE="${PLUGIN_NAME}.zip"

# Dit scriptbestand uitsluiten (zorgt ervoor dat het script niet in de zip komt)
SCRIPT_NAME=$(basename "$0")

# Archiveren en de ongewenste bestanden uitsluiten
zip -r "$ZIP_FILE" "$PLUGIN_DIR" \
    -x "*.git*" \
    -x "*idea*" \
    -x "*composer.json" \
    -x "*composer.lock" \
    -x "*node_modules*" \
    -x "*.DS_Store*" \
    -x "*.vscode*" \
    -x "*vendor*" \
    -x "*.gitignore" \
    -x "*$SCRIPT_NAME"

# Output het resultaat
echo "De plugin is succesvol ingepakt als $ZIP_FILE."
