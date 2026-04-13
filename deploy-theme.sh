#!/bin/bash

# ---------------------------------------------------------
# Configuración
# ---------------------------------------------------------
THEME_NAME="abc-theme"
ZIP_FILE="../$THEME_NAME-update.zip"
HEADER_CSS="header.css"
STYLE_CSS="style.css"
TEMP_CSS="temp_tailwind.css"

# Colores para salida
CYAN='\033[0;36m'
GREEN='\033[0;32m'
RED='\033[1;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# ---------------------------------------------------------
# Paso 0: Incrementar Versión (Minor) en header.css
# ---------------------------------------------------------
echo -e "${CYAN}--- Actualizando versión del tema en $HEADER_CSS ---${NC}"

if [ ! -f "$HEADER_CSS" ]; then
    echo -e "${RED}Error: $HEADER_CSS no encontrado.${NC}"
    exit 1
fi

CURRENT_VERSION=$(grep -m 1 "Version:" "$HEADER_CSS" | awk '{print $2}')

if [ -z "$CURRENT_VERSION" ]; then
    echo -e "${RED}Error: No se encontró la etiqueta 'Version:' en $HEADER_CSS.${NC}"
    exit 1
fi

IFS='.' read -r MAJOR MINOR PATCH <<< "$CURRENT_VERSION"
NEW_MINOR=$((MINOR + 1))
NEW_VERSION="$MAJOR.$NEW_MINOR.$PATCH"

sed -i "s/Version: $CURRENT_VERSION/Version: $NEW_VERSION/" "$HEADER_CSS"

echo -e "${GREEN}Versión actualizada de ${YELLOW}$CURRENT_VERSION${GREEN} a ${YELLOW}$NEW_VERSION${NC}"

# ---------------------------------------------------------
# Paso 1: Compilación de Tailwind CSS
# ---------------------------------------------------------
echo -e "\n${CYAN}--- Iniciando compilación de Tailwind CSS ---${NC}"

if [ ! -f "./tailwindcss" ]; then
    echo -e "${RED}Error: Binario 'tailwindcss' no encontrado.${NC}"
    exit 1
fi

chmod +x ./tailwindcss
./tailwindcss -i ./src/input.css -o "$TEMP_CSS" --minify

if [ $? -ne 0 ]; then
    echo -e "${RED}Error en la compilación de Tailwind. Despliegue abortado.${NC}"
    rm -f "$TEMP_CSS"
    exit 1
fi

cat "$HEADER_CSS" "$TEMP_CSS" > "$STYLE_CSS"
rm "$TEMP_CSS"

echo -e "${GREEN}Estilos compilados y $STYLE_CSS generado correctamente.${NC}"

# ---------------------------------------------------------
# Paso 2: Empaquetado del Tema usando Python (Lógica Anti-Errores Críticos)
# ---------------------------------------------------------
echo -e "\n${CYAN}--- Iniciando empaquetado de $THEME_NAME ---${NC}"

if [ -f "$ZIP_FILE" ]; then
    rm "$ZIP_FILE"
fi

# EXPLICACIÓN DE EXCLUSIONES:
# - Patrones que empiezan con './' son relativos a la raíz únicamente.
# - Patrones sin './' son globales (se ignoran en cualquier nivel).
EXCLUDE_LIST=(
    ".git"
    "node_modules"
    "tailwindcss"
    "tailwindcss.exe"
    ".ddev"
    "./src"                 # Protegemos los 'src' internos de vendor
    "*.zip"
    "*.ps1"
    "deploy-theme.sh"
    ".gitignore"
    ".editorconfig"
    "Zone.Identifier"
    "package.json"
    "package-lock.json"
    "composer.json"
    "composer.lock"
    ".gemini"
    ".geminiignore"
    "RANK-MATH-CONFIG.md"
    "header.css"
    "zip_it.py"
)

cat <<EOF > zip_it.py
import zipfile
import os
import fnmatch

zip_file_path = "$ZIP_FILE"
excludes = [
$(for i in "${EXCLUDE_LIST[@]}"; do echo "    '$i',"; done)
]

def should_exclude(rel_path):
    # rel_path viene como 'vendor/twig/src/file.php' o 'src/input.css'
    for pattern in excludes:
        # Si el patrón es de raíz (ej: ./src)
        if pattern.startswith('./'):
            pure_pattern = pattern[2:]
            # Solo excluimos si el path empieza exactamente con eso
            if rel_path == pure_pattern or rel_path.startswith(pure_pattern + os.sep):
                return True
        else:
            # Búsqueda global (comportamiento original para .git, node_modules, etc)
            if fnmatch.fnmatch(rel_path, pattern) or fnmatch.fnmatch(os.path.basename(rel_path), pattern):
                return True
            parts = rel_path.split(os.sep)
            if any(fnmatch.fnmatch(part, pattern) for part in parts):
                return True
    return False

with zipfile.ZipFile(zip_file_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for root, dirs, files in os.walk('.'):
        # Limpiar el root para que sea relativo y limpio
        clean_root = root.lstrip('.').lstrip(os.sep)
        
        # Filtrar directorios para no entrar en los excluidos
        dirs[:] = [d for d in dirs if not should_exclude(os.path.join(clean_root, d))]
        
        for file in files:
            file_path = os.path.join(root, file)
            relative_path = os.path.join(clean_root, file)
            if not should_exclude(relative_path) and file != 'zip_it.py':
                zipf.write(file_path, relative_path)
EOF

python3 zip_it.py
rm zip_it.py

if [ $? -eq 0 ]; then
    SIZE=$(du -h "$ZIP_FILE" | cut -f1)
    echo -e "${GREEN}✅ ¡Listo! Versión ${YELLOW}$NEW_VERSION${GREEN} empaquetada en: $ZIP_FILE${NC}"
    echo -e "${YELLOW}Tamaño estimado: $SIZE${NC}"
    echo -e "${CYAN}Nota: Se han respetado las carpetas 'src' internas de las dependencias.${NC}"
else
    echo -e "${RED}Error al crear el archivo comprimido.${NC}"
    exit 1
fi
