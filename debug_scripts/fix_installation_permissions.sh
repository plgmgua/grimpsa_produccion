#!/bin/bash

# Fix Installation Permissions Script
# This script addresses the "Copy failed" error during Joomla component installation

echo "=========================================="
echo "Fix Installation Permissions"
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
echo "1. CHECKING CURRENT PERMISSIONS"
echo "==============================="

# Check current permissions on key directories
directories=(
    "administrator/components"
    "components"
    "media"
    "cache"
    "tmp"
    "administrator/cache"
    "administrator/tmp"
    "logs"
    "administrator/logs"
)

for dir in "${directories[@]}"; do
    if [ -d "$dir" ]; then
        perms=$(stat -c "%a" "$dir" 2>/dev/null || stat -f "%OLp" "$dir" 2>/dev/null)
        owner=$(stat -c "%U:%G" "$dir" 2>/dev/null || stat -f "%Su:%Sg" "$dir" 2>/dev/null)
        print_info "$dir: perms=$perms, owner=$owner"
    else
        print_warning "$dir: directory not found"
    fi
done

echo ""
echo "2. FIXING DIRECTORY PERMISSIONS"
echo "==============================="

# Fix permissions on all Joomla directories
print_info "Setting permissions to 755 on all directories..."
find . -type d -exec chmod 755 {} \; 2>/dev/null
print_status "Directory permissions set to 755" 0

# Fix permissions on all files
print_info "Setting permissions to 644 on all files..."
find . -type f -exec chmod 644 {} \; 2>/dev/null
print_status "File permissions set to 644" 0

echo ""
echo "3. FIXING OWNERSHIP"
echo "==================="

# Try to set ownership to www-data (common for web servers)
print_info "Setting ownership to www-data:www-data..."
if command -v chown >/dev/null 2>&1; then
    chown -R www-data:www-data . 2>/dev/null
    if [ $? -eq 0 ]; then
        print_status "Ownership set to www-data:www-data" 0
    else
        print_warning "Could not set ownership to www-data (may need sudo)"
        print_info "Trying with sudo..."
        sudo chown -R www-data:www-data . 2>/dev/null
        if [ $? -eq 0 ]; then
            print_status "Ownership set to www-data:www-data (with sudo)" 0
        else
            print_warning "Could not set ownership even with sudo"
        fi
    fi
else
    print_warning "chown command not available"
fi

echo ""
echo "4. CREATING MISSING DIRECTORIES"
echo "==============================="

# Create missing directories that Joomla needs
missing_dirs=(
    "administrator/tmp"
    "administrator/cache"
    "tmp"
    "cache"
    "logs"
    "administrator/logs"
)

for dir in "${missing_dirs[@]}"; do
    if [ ! -d "$dir" ]; then
        print_info "Creating missing directory: $dir"
        mkdir -p "$dir" 2>/dev/null
        if [ $? -eq 0 ]; then
            print_status "Created: $dir" 0
        else
            print_warning "Failed to create: $dir"
        fi
    else
        print_status "Directory exists: $dir" 0
    fi
done

echo ""
echo "5. SETTING SPECIAL PERMISSIONS"
echo "=============================="

# Set special permissions for writable directories
writable_dirs=(
    "cache"
    "tmp"
    "administrator/cache"
    "administrator/tmp"
    "logs"
    "administrator/logs"
)

for dir in "${writable_dirs[@]}"; do
    if [ -d "$dir" ]; then
        print_info "Setting writable permissions on: $dir"
        chmod 777 "$dir" 2>/dev/null
        if [ $? -eq 0 ]; then
            print_status "Set writable permissions on: $dir" 0
        else
            print_warning "Failed to set writable permissions on: $dir"
        fi
    fi
done

echo ""
echo "6. CLEARING CACHES"
echo "=================="

# Clear all caches
print_info "Clearing all caches..."
for cache_dir in "cache" "tmp" "administrator/cache" "administrator/tmp"; do
    if [ -d "$cache_dir" ]; then
        rm -rf "$cache_dir"/* 2>/dev/null
        print_status "Cleared: $cache_dir" 0
    fi
done

echo ""
echo "7. TESTING FILE CREATION"
echo "========================"

# Test if we can create files in key directories
test_dirs=(
    "tmp"
    "cache"
    "administrator/tmp"
    "administrator/cache"
)

for dir in "${test_dirs[@]}"; do
    if [ -d "$dir" ]; then
        test_file="$dir/test_write_$(date +%s).txt"
        if echo "test" > "$test_file" 2>/dev/null; then
            print_status "Can write to: $dir" 0
            rm -f "$test_file" 2>/dev/null
        else
            print_warning "Cannot write to: $dir"
        fi
    fi
done

echo ""
echo "8. FINAL PERMISSION CHECK"
echo "========================"

# Final check of key directories
for dir in "${directories[@]}"; do
    if [ -d "$dir" ]; then
        perms=$(stat -c "%a" "$dir" 2>/dev/null || stat -f "%OLp" "$dir" 2>/dev/null)
        owner=$(stat -c "%U:%G" "$dir" 2>/dev/null || stat -f "%Su:%Sg" "$dir" 2>/dev/null)
        print_info "$dir: perms=$perms, owner=$owner"
    fi
done

echo ""
echo "=========================================="
echo "PERMISSION FIX COMPLETE"
echo "=========================================="
echo ""
print_info "You can now try installing the component again."
print_warning "Remember to delete this script after use for security!"
