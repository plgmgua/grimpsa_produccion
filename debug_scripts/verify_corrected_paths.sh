#!/bin/bash

# Verify Corrected Manifest Paths Script
# This script verifies that the manifest paths match the actual file structure

echo "=========================================="
echo "Verify Corrected Manifest Paths"
echo "=========================================="
echo "Current Time: $(date)"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    if [ $2 -eq 0 ]; then
        echo -e "${GREEN}✅ $1${NC}"
    else
        echo -e "${RED}❌ $1${NC}"
    fi
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

echo "1. CHECKING MANIFEST STRUCTURE"
echo "==============================="

manifest_file="com_produccion_corrected.xml"
if [ -f "$manifest_file" ]; then
    print_status "Manifest file found: $manifest_file" 0
else
    print_warning "Manifest file not found: $manifest_file"
    exit 1
fi

echo ""
echo "2. VERIFYING MANIFEST PATHS MATCH ACTUAL FILES"
echo "=============================================="

# Check that manifest paths match actual file structure
print_info "Checking manifest expects: com_produccion/admin/produccion.php"
if [ -f "com_produccion/admin/produccion.php" ]; then
    print_status "✅ File exists at expected path" 0
else
    print_warning "❌ File missing at expected path"
fi

print_info "Checking manifest expects: com_produccion/site/produccion.php"
if [ -f "com_produccion/site/produccion.php" ]; then
    print_status "✅ File exists at expected path" 0
else
    print_warning "❌ File missing at expected path"
fi

print_info "Checking manifest expects: com_produccion/script.php"
if [ -f "com_produccion/script.php" ]; then
    print_status "✅ File exists at expected path" 0
else
    print_warning "❌ File missing at expected path"
fi

print_info "Checking manifest expects: com_produccion/admin/sql/install.mysql.utf8.sql"
if [ -f "com_produccion/admin/sql/install.mysql.utf8.sql" ]; then
    print_status "✅ File exists at expected path" 0
else
    print_warning "❌ File missing at expected path"
fi

echo ""
echo "3. CHECKING KEY ENTRY POINTS"
echo "============================="

# Check key entry points that Joomla looks for
entry_points=(
    "com_produccion/admin/produccion.php"
    "com_produccion/site/produccion.php"
    "com_produccion/script.php"
)

for file in "${entry_points[@]}"; do
    if [ -f "$file" ]; then
        print_status "Entry point found: $file" 0
    else
        print_warning "Entry point missing: $file"
    fi
done

echo ""
echo "4. CHECKING MANIFEST XML STRUCTURE"
echo "==================================="

# Check if manifest has correct folder references
if grep -q 'folder="com_produccion/admin"' "$manifest_file"; then
    print_status "Manifest correctly references com_produccion/admin folder" 0
else
    print_warning "Manifest does not reference com_produccion/admin folder"
fi

if grep -q 'folder="com_produccion/site"' "$manifest_file"; then
    print_status "Manifest correctly references com_produccion/site folder" 0
else
    print_warning "Manifest does not reference com_produccion/site folder"
fi

if grep -q 'folder="com_produccion/media"' "$manifest_file"; then
    print_status "Manifest correctly references com_produccion/media folder" 0
else
    print_warning "Manifest does not reference com_produccion/media folder"
fi

echo ""
echo "5. FINAL VERIFICATION"
echo "====================="

print_info "Manifest structure should now match Joomla's expectations:"
echo "- Files are in com_produccion/admin/ and com_produccion/site/"
echo "- Manifest references these paths correctly"
echo "- Entry points (produccion.php) are at the expected locations"

echo ""
echo "=========================================="
echo "VERIFICATION COMPLETE"
echo "=========================================="
echo ""
print_warning "Remember to delete this script after use for security!"
