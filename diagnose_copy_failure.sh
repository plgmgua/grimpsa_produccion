#!/bin/bash

# Diagnose Copy Failure Script
# This script helps diagnose why Joomla installation is failing with "Copy failed" error

echo "=========================================="
echo "Diagnose Copy Failure"
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
echo "1. CHECKING SYSTEM INFORMATION"
echo "==============================="

# Check system info
print_info "OS: $(uname -a)"
print_info "User: $(whoami)"
print_info "Groups: $(groups)"
print_info "Current directory: $(pwd)"

echo ""
echo "2. CHECKING DISK SPACE"
echo "======================"

# Check disk space
print_info "Disk space:"
df -h . 2>/dev/null || print_warning "Could not check disk space"

echo ""
echo "3. CHECKING JOOMLA DIRECTORIES"
echo "============================="

# Check Joomla directory structure
joomla_dirs=(
    "administrator"
    "components"
    "administrator/components"
    "media"
    "cache"
    "tmp"
    "administrator/cache"
    "administrator/tmp"
    "logs"
    "administrator/logs"
)

for dir in "${joomla_dirs[@]}"; do
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
echo "4. CHECKING COMPONENT DIRECTORIES"
echo "================================="

# Check if component directories exist and their permissions
component_dirs=(
    "administrator/components/com_produccion"
    "components/com_produccion"
    "media/com_produccion"
)

for dir in "${component_dirs[@]}"; do
    if [ -d "$dir" ]; then
        perms=$(stat -c "%a" "$dir" 2>/dev/null || stat -f "%OLp" "$dir" 2>/dev/null)
        owner=$(stat -c "%U:%G" "$dir" 2>/dev/null || stat -f "%Su:%Sg" "$dir" 2>/dev/null)
        print_info "$dir: perms=$perms, owner=$owner (exists)"
    else
        print_info "$dir: does not exist (will be created during installation)"
    fi
done

echo ""
echo "5. CHECKING TEMPORARY DIRECTORIES"
echo "================================="

# Check tmp directories where Joomla extracts files
tmp_dirs=(
    "tmp"
    "administrator/tmp"
)

for dir in "${tmp_dirs[@]}"; do
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
        
        # Check if there are any old installation directories
        old_installs=$(find "$dir" -name "install_*" -type d 2>/dev/null | wc -l)
        if [ "$old_installs" -gt 0 ]; then
            print_warning "Found $old_installs old installation directories in $dir"
            print_info "Old installs: $(find "$dir" -name "install_*" -type d 2>/dev/null | head -5)"
        fi
    else
        print_warning "$dir: directory not found"
    fi
done

echo ""
echo "6. TESTING FILE OPERATIONS"
echo "=========================="

# Test file creation in key directories
test_dirs=(
    "tmp"
    "cache"
    "administrator/tmp"
    "administrator/cache"
)

for dir in "${test_dirs[@]}"; do
    if [ -d "$dir" ]; then
        test_file="$dir/test_$(date +%s).txt"
        if echo "test content" > "$test_file" 2>/dev/null; then
            print_status "Can create files in: $dir" 0
            rm -f "$test_file" 2>/dev/null
        else
            print_warning "Cannot create files in: $dir"
        fi
    fi
done

echo ""
echo "7. CHECKING APACHE ERROR LOGS"
echo "============================="

# Check Apache error logs for relevant errors
print_info "Checking Apache error logs..."
if [ -f "/var/log/apache2/error.log" ]; then
    recent_errors=$(tail -20 /var/log/apache2/error.log | grep -i "copy\|permission\|denied" | tail -5)
    if [ -n "$recent_errors" ]; then
        print_warning "Recent Apache errors related to copy/permission:"
        echo "$recent_errors"
    else
        print_info "No recent copy/permission errors in Apache logs"
    fi
else
    print_warning "Apache error log not found at /var/log/apache2/error.log"
fi

echo ""
echo "8. CHECKING PHP ERROR LOGS"
echo "==========================="

# Check PHP error logs
php_logs=(
    "/var/log/php_errors.log"
    "/var/log/php/error.log"
    "/var/log/php8.3-fpm.log"
    "/var/log/php-fpm.log"
)

for log in "${php_logs[@]}"; do
    if [ -f "$log" ]; then
        print_info "Found PHP log: $log"
        recent_php_errors=$(tail -10 "$log" | grep -i "copy\|permission\|denied" | tail -3)
        if [ -n "$recent_php_errors" ]; then
            print_warning "Recent PHP errors:"
            echo "$recent_php_errors"
        fi
    fi
done

echo ""
echo "9. RECOMMENDATIONS"
echo "=================="

print_info "Based on the diagnostic results:"
echo "1. Ensure all directories are writable by the web server user"
echo "2. Check that disk space is sufficient"
echo "3. Verify that the web server user (www-data) has proper permissions"
echo "4. Clear any old installation directories in tmp/"
echo "5. Check Apache and PHP error logs for specific error messages"

echo ""
echo "=========================================="
echo "DIAGNOSTIC COMPLETE"
echo "=========================================="
echo ""
print_warning "Remember to delete this script after use for security!"
