#!/bin/bash

# Verify Manifest Files Script
# This script verifies that all files referenced in the manifest actually exist

echo "=========================================="
echo "Verify Manifest Files"
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

# Check if we're in the right directory
if [ ! -d "com_produccion" ]; then
    print_warning "com_produccion directory not found. Please run this script from the correct directory."
    exit 1
fi

echo "1. CHECKING MANIFEST FILE"
echo "========================="

manifest_file="com_produccion_verified.xml"
if [ -f "$manifest_file" ]; then
    print_status "Manifest file found: $manifest_file" 0
else
    print_warning "Manifest file not found: $manifest_file"
    exit 1
fi

echo ""
echo "2. VERIFYING ADMIN FILES"
echo "========================"

# Check admin files
admin_files=(
    "com_produccion/admin/produccion.php"
    "com_produccion/admin/controller.php"
    "com_produccion/admin/config.xml"
    "com_produccion/admin/debug.php"
)

for file in "${admin_files[@]}"; do
    if [ -f "$file" ]; then
        print_status "Found: $file" 0
    else
        print_warning "Missing: $file"
    fi
done

echo ""
echo "3. VERIFYING ADMIN DIRECTORIES"
echo "==============================="

# Check admin directories
admin_dirs=(
    "com_produccion/admin/controllers"
    "com_produccion/admin/models"
    "com_produccion/admin/views"
    "com_produccion/admin/services"
    "com_produccion/admin/src"
    "com_produccion/admin/sql"
    "com_produccion/admin/language"
    "com_produccion/admin/tmpl"
)

for dir in "${admin_dirs[@]}"; do
    if [ -d "$dir" ]; then
        print_status "Found directory: $dir" 0
    else
        print_warning "Missing directory: $dir"
    fi
done

echo ""
echo "4. VERIFYING SITE FILES"
echo "======================="

# Check site files
site_files=(
    "com_produccion/site/produccion.php"
)

for file in "${site_files[@]}"; do
    if [ -f "$file" ]; then
        print_status "Found: $file" 0
    else
        print_warning "Missing: $file"
    fi
done

echo ""
echo "5. VERIFYING SITE DIRECTORIES"
echo "=============================="

# Check site directories
site_dirs=(
    "com_produccion/site/services"
    "com_produccion/site/src"
    "com_produccion/site/language"
)

for dir in "${site_dirs[@]}"; do
    if [ -d "$dir" ]; then
        print_status "Found directory: $dir" 0
    else
        print_warning "Missing directory: $dir"
    fi
done

echo ""
echo "6. VERIFYING MEDIA FILES"
echo "========================"

# Check media files
media_files=(
    "com_produccion/media/css/com_produccion.css"
    "com_produccion/media/js/com_produccion.js"
)

for file in "${media_files[@]}"; do
    if [ -f "$file" ]; then
        print_status "Found: $file" 0
    else
        print_warning "Missing: $file"
    fi
done

echo ""
echo "7. VERIFYING LANGUAGE FILES"
echo "==========================="

# Check language files
lang_files=(
    "com_produccion/admin/language/en-GB/com_produccion.ini"
    "com_produccion/admin/language/en-GB/com_produccion.sys.ini"
    "com_produccion/site/language/en-GB/com_produccion.ini"
)

for file in "${lang_files[@]}"; do
    if [ -f "$file" ]; then
        print_status "Found: $file" 0
    else
        print_warning "Missing: $file"
    fi
done

echo ""
echo "8. VERIFYING SQL FILES"
echo "======================"

# Check SQL files
sql_files=(
    "com_produccion/admin/sql/install.mysql.utf8.sql"
    "com_produccion/admin/sql/uninstall.mysql.utf8.sql"
)

for file in "${sql_files[@]}"; do
    if [ -f "$file" ]; then
        print_status "Found: $file" 0
    else
        print_warning "Missing: $file"
    fi
done

echo ""
echo "9. VERIFYING SCRIPT FILE"
echo "========================"

# Check script file
script_file="com_produccion/script.php"
if [ -f "$script_file" ]; then
    print_status "Found: $script_file" 0
else
    print_warning "Missing: $script_file"
fi

echo ""
echo "10. DETAILED FILE COUNT"
echo "======================="

# Count files in each directory
print_info "File counts:"
echo "Admin files: $(find com_produccion/admin -type f | wc -l)"
echo "Site files: $(find com_produccion/site -type f | wc -l)"
echo "Media files: $(find com_produccion/media -type f | wc -l)"
echo "Total files: $(find com_produccion -type f | wc -l)"

echo ""
echo "11. LISTING ALL FILES"
echo "====================="

print_info "All files in component:"
find com_produccion -type f | sort

echo ""
echo "=========================================="
echo "VERIFICATION COMPLETE"
echo "=========================================="
echo ""
print_warning "Remember to delete this script after use for security!"
