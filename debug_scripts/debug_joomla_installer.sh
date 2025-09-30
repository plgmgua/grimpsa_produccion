#!/bin/bash

# Debug Joomla Installer Script
# This script provides comprehensive debugging for Joomla component installation issues

echo "=========================================="
echo "Debug Joomla Installer"
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

# Check if we're in a Joomla directory
if [ ! -f "configuration.php" ]; then
    print_warning "Not in Joomla root directory. Please run this script from your Joomla root."
    exit 1
fi

JOOMLA_ROOT=$(pwd)
print_status "Joomla Root: $JOOMLA_ROOT" 0

echo ""
echo "1. CHECKING JOOMLA VERSION AND ENVIRONMENT"
echo "=========================================="

# Check Joomla version
if [ -f "libraries/src/Version.php" ]; then
    JOOMLA_VERSION=$(grep "RELEASE" libraries/src/Version.php | head -1 | cut -d"'" -f2)
    print_info "Joomla Version: $JOOMLA_VERSION"
else
    print_warning "Could not determine Joomla version"
fi

# Check PHP version
print_info "PHP Version: $(php -v | head -1)"

echo ""
echo "2. ANALYZING INSTALLATION PACKAGE"
echo "================================="

# Check if we have any component packages
PACKAGE_FILES=$(find . -name "com_produccion*.zip" -type f 2>/dev/null)
if [ -n "$PACKAGE_FILES" ]; then
    print_info "Found component packages:"
    echo "$PACKAGE_FILES"
    
    # Analyze the latest package
    LATEST_PACKAGE=$(ls -t com_produccion*.zip | head -1)
    print_info "Analyzing latest package: $LATEST_PACKAGE"
    
    echo ""
    print_info "Package contents:"
    unzip -l "$LATEST_PACKAGE" | head -20
    
    echo ""
    print_info "Checking for manifest file:"
    if unzip -l "$LATEST_PACKAGE" | grep -q "com_produccion.xml"; then
        print_status "Manifest file found: com_produccion.xml" 0
    else
        print_warning "Manifest file com_produccion.xml NOT found in package"
        print_info "Files in package:"
        unzip -l "$LATEST_PACKAGE" | grep "\.xml"
    fi
    
    echo ""
    print_info "Checking for entry point files:"
    if unzip -l "$LATEST_PACKAGE" | grep -q "produccion.php"; then
        print_status "Entry point files found" 0
        unzip -l "$LATEST_PACKAGE" | grep "produccion.php"
    else
        print_warning "Entry point files NOT found"
    fi
    
else
    print_warning "No component packages found in current directory"
fi

echo ""
echo "3. CHECKING TEMPORARY INSTALLATION DIRECTORIES"
echo "=============================================="

# Check tmp directories
TMP_DIRS=("tmp" "administrator/tmp")
for dir in "${TMP_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        print_info "Checking $dir:"
        perms=$(stat -c "%a" "$dir" 2>/dev/null || stat -f "%OLp" "$dir" 2>/dev/null)
        owner=$(stat -c "%U:%G" "$dir" 2>/dev/null || stat -f "%Su:%Sg" "$dir" 2>/dev/null)
        writable=""
        if [ -w "$dir" ]; then
            writable="✅ writable"
        else
            writable="❌ not writable"
        fi
        print_info "  Permissions: $perms, Owner: $owner, $writable"
        
        # Check for old installation directories
        old_installs=$(find "$dir" -name "install_*" -type d 2>/dev/null | wc -l)
        if [ "$old_installs" -gt 0 ]; then
            print_warning "  Found $old_installs old installation directories"
            print_info "  Old installs: $(find "$dir" -name "install_*" -type d 2>/dev/null | head -3)"
        else
            print_info "  No old installation directories found"
        fi
    else
        print_warning "$dir: directory not found"
    fi
done

echo ""
echo "4. SIMULATING INSTALLATION PROCESS"
echo "=================================="

# Create a test installation directory
TEST_INSTALL_DIR="tmp/debug_install_$(date +%s)"
print_info "Creating test installation directory: $TEST_INSTALL_DIR"

if mkdir -p "$TEST_INSTALL_DIR" 2>/dev/null; then
    print_status "Test directory created" 0
    
    # Test if we can extract a package
    if [ -n "$LATEST_PACKAGE" ] && [ -f "$LATEST_PACKAGE" ]; then
        print_info "Testing package extraction..."
        if unzip -q "$LATEST_PACKAGE" -d "$TEST_INSTALL_DIR" 2>/dev/null; then
            print_status "Package extracted successfully" 0
            
            # Check what was extracted
            print_info "Extracted contents:"
            ls -la "$TEST_INSTALL_DIR"
            
            # Check for manifest file
            if [ -f "$TEST_INSTALL_DIR/com_produccion.xml" ]; then
                print_status "Manifest file found in extracted package" 0
            else
                print_warning "Manifest file NOT found in extracted package"
                print_info "Looking for XML files:"
                find "$TEST_INSTALL_DIR" -name "*.xml" -type f
            fi
            
            # Check for entry points
            if [ -f "$TEST_INSTALL_DIR/com_produccion_joomla5/admin/produccion.php" ]; then
                print_status "Admin entry point found" 0
            else
                print_warning "Admin entry point NOT found"
            fi
            
            if [ -f "$TEST_INSTALL_DIR/com_produccion_joomla5/site/produccion.php" ]; then
                print_status "Site entry point found" 0
            else
                print_warning "Site entry point NOT found"
            fi
            
        else
            print_warning "Package extraction failed"
        fi
    else
        print_warning "No package available for testing"
    fi
    
    # Clean up test directory
    rm -rf "$TEST_INSTALL_DIR"
    print_info "Test directory cleaned up"
else
    print_warning "Could not create test directory"
fi

echo ""
echo "5. CHECKING JOOMLA INSTALLER LOGS"
echo "=================================="

# Check for Joomla installer logs
LOG_FILES=(
    "administrator/logs/joomla_update.php"
    "administrator/logs/error.php"
    "logs/error.php"
    "/var/log/apache2/error.log"
    "/var/log/php_errors.log"
)

for log_file in "${LOG_FILES[@]}"; do
    if [ -f "$log_file" ]; then
        print_info "Found log file: $log_file"
        recent_errors=$(tail -10 "$log_file" | grep -i "install\|copy\|xml\|setup" | tail -3)
        if [ -n "$recent_errors" ]; then
            print_warning "Recent installer errors:"
            echo "$recent_errors"
        else
            print_info "No recent installer errors found"
        fi
    fi
done

echo ""
echo "6. CHECKING FILE PERMISSIONS AND OWNERSHIP"
echo "=========================================="

# Check key directories
KEY_DIRS=(
    "administrator/components"
    "components"
    "media"
    "tmp"
    "administrator/tmp"
)

for dir in "${KEY_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        perms=$(stat -c "%a" "$dir" 2>/dev/null || stat -f "%OLp" "$dir" 2>/dev/null)
        owner=$(stat -c "%U:%G" "$dir" 2>/dev/null || stat -f "%Su:%Sg" "$dir" 2>/dev/null)
        writable=""
        if [ -w "$dir" ]; then
            writable="✅ writable"
        else
            writable="❌ not writable"
        fi
        print_info "$dir: perms=$perms, owner=$owner, $writable"
    else
        print_warning "$dir: directory not found"
    fi
done

echo ""
echo "7. TESTING FILE OPERATIONS"
echo "=========================="

# Test file creation in key directories
TEST_DIRS=("tmp" "administrator/tmp")
for dir in "${TEST_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        test_file="$dir/debug_test_$(date +%s).txt"
        if echo "test" > "$test_file" 2>/dev/null; then
            print_status "Can create files in: $dir" 0
            rm -f "$test_file" 2>/dev/null
        else
            print_warning "Cannot create files in: $dir"
        fi
    fi
done

echo ""
echo "8. RECOMMENDATIONS"
echo "=================="

print_info "Based on the debug results:"
echo "1. Check if the manifest file (com_produccion.xml) is in the root of the zip package"
echo "2. Verify that all file paths in the manifest match the actual package structure"
echo "3. Ensure all directories are writable by the web server user"
echo "4. Check for any old installation directories that might be causing conflicts"
echo "5. Verify that the package contains the expected entry point files"

echo ""
echo "=========================================="
echo "DEBUG COMPLETE"
echo "=========================================="
echo ""
print_warning "Remember to delete this script after use for security!"
