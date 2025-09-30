#!/bin/bash

# Upload Package Script
# This script helps upload the component package to your server

echo "=========================================="
echo "Upload Component Package"
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

echo "1. CHECKING PACKAGE FILES"
echo "========================="

# Check if package exists
if [ -f "com_produccion_v1.0.33_final.zip" ]; then
    print_status "Package found: com_produccion_v1.0.33_final.zip" 0
    print_info "Package size: $(ls -lh com_produccion_v1.0.33_final.zip | awk '{print $5}')"
else
    print_warning "Package not found: com_produccion_v1.0.33_final.zip"
    exit 1
fi

echo ""
echo "2. PACKAGE STRUCTURE VERIFICATION"
echo "================================="

# Verify package structure
print_info "Checking package contents:"
unzip -l com_produccion_v1.0.33_final.zip | head -10

echo ""
print_info "Checking for manifest file:"
if unzip -l com_produccion_v1.0.33_final.zip | grep -q "com_produccion.xml"; then
    print_status "✅ Manifest file found: com_produccion.xml" 0
else
    print_warning "❌ Manifest file NOT found"
fi

echo ""
print_info "Checking for entry points:"
if unzip -l com_produccion_v1.0.33_final.zip | grep -q "produccion.php"; then
    print_status "✅ Entry point files found" 0
    unzip -l com_produccion_v1.0.33_final.zip | grep "produccion.php"
else
    print_warning "❌ Entry point files NOT found"
fi

echo ""
echo "3. UPLOAD INSTRUCTIONS"
echo "======================"

print_info "To upload the package to your server:"
echo ""
echo "Option 1: Using SCP (if you have SSH access):"
echo "scp com_produccion_v1.0.33_final.zip pgrant@grimpsa_webserver.grantsolutions.cc:/var/www/grimpsa_webserver/"
echo ""
echo "Option 2: Using SFTP:"
echo "1. Connect to your server via SFTP"
echo "2. Navigate to /var/www/grimpsa_webserver/"
echo "3. Upload com_produccion_v1.0.33_final.zip"
echo ""
echo "Option 3: Using File Manager:"
echo "1. Access your server's file manager"
echo "2. Navigate to /var/www/grimpsa_webserver/"
echo "3. Upload com_produccion_v1.0.33_final.zip"
echo ""
echo "Option 4: Using wget/curl (if you host the file somewhere):"
echo "wget https://your-domain.com/com_produccion_v1.0.33_final.zip"

echo ""
echo "4. AFTER UPLOADING"
echo "==================="

print_info "After uploading the package to your server:"
echo "1. Run the debug script again: ./debug_installer_fixed.sh"
echo "2. The script should now find the package and analyze it"
echo "3. Try installing the component from Joomla admin"
echo "4. If it still fails, the debug script will show the exact issue"

echo ""
echo "5. PACKAGE VERIFICATION"
echo "======================="

print_info "The package contains:"
echo "- ✅ Manifest file: com_produccion.xml (in root)"
echo "- ✅ Admin files: com_produccion_joomla5/admin/"
echo "- ✅ Site files: com_produccion_joomla5/site/"
echo "- ✅ Media files: com_produccion_joomla5/media/"
echo "- ✅ Script file: com_produccion_joomla5/script.php"
echo "- ✅ SQL files: com_produccion_joomla5/admin/sql/"

echo ""
echo "=========================================="
echo "UPLOAD INSTRUCTIONS COMPLETE"
echo "=========================================="
echo ""
print_warning "Remember to delete this script after use for security!"
