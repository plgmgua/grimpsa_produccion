#!/bin/bash

# Clean Install Component Script
# This script removes existing component files and prepares for fresh installation

echo "=========================================="
echo "Clean Install Component"
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
echo "1. BACKING UP EXISTING COMPONENT"
echo "================================="

# Check if component exists
if [ -d "administrator/components/com_produccion" ] || [ -d "components/com_produccion" ] || [ -d "media/com_produccion" ]; then
    print_info "Existing component found. Creating backup..."
    backup_dir="backup_com_produccion_$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$backup_dir"
    
    if [ -d "administrator/components/com_produccion" ]; then
        cp -r "administrator/components/com_produccion" "$backup_dir/admin_component" 2>/dev/null
        print_status "Backed up admin component" 0
    fi
    
    if [ -d "components/com_produccion" ]; then
        cp -r "components/com_produccion" "$backup_dir/site_component" 2>/dev/null
        print_status "Backed up site component" 0
    fi
    
    if [ -d "media/com_produccion" ]; then
        cp -r "media/com_produccion" "$backup_dir/media" 2>/dev/null
        print_status "Backed up media files" 0
    fi
    
    print_info "Backup created in: $backup_dir"
else
    print_info "No existing component found"
fi

echo ""
echo "2. REMOVING EXISTING COMPONENT FILES"
echo "===================================="

# Remove existing component directories
component_dirs=(
    "administrator/components/com_produccion"
    "components/com_produccion"
    "media/com_produccion"
)

for dir in "${component_dirs[@]}"; do
    if [ -d "$dir" ]; then
        print_info "Removing: $dir"
        rm -rf "$dir" 2>/dev/null
        if [ $? -eq 0 ]; then
            print_status "Removed: $dir" 0
        else
            print_warning "Failed to remove: $dir"
        fi
    else
        print_info "Directory not found: $dir"
    fi
done

echo ""
echo "3. CLEARING INSTALLATION CACHES"
echo "================================"

# Clear temporary installation directories
print_info "Clearing temporary installation directories..."
find tmp -name "install_*" -type d -exec rm -rf {} \; 2>/dev/null
find administrator/tmp -name "install_*" -type d -exec rm -rf {} \; 2>/dev/null
print_status "Cleared temporary installation directories" 0

# Clear Joomla caches
print_info "Clearing Joomla caches..."
rm -rf cache/* 2>/dev/null
rm -rf tmp/* 2>/dev/null
rm -rf administrator/cache/* 2>/dev/null
rm -rf administrator/tmp/* 2>/dev/null
print_status "Cleared Joomla caches" 0

echo ""
echo "4. CHECKING DATABASE ENTRIES"
echo "============================="

# Check if component is registered in database
print_info "Checking database for component registration..."
if command -v mysql >/dev/null 2>&1; then
    # Get database credentials from configuration.php
    DB_HOST=$(grep "\$this->host" configuration.php | cut -d"'" -f2)
    DB_USER=$(grep "\$this->user" configuration.php | cut -d"'" -f2)
    DB_PASS=$(grep "\$this->password" configuration.php | cut -d"'" -f2)
    DB_NAME=$(grep "\$this->db" configuration.php | cut -d"'" -f2)
    DB_PREFIX=$(grep "\$this->dbprefix" configuration.php | cut -d"'" -f2)
    
    if [ -n "$DB_HOST" ] && [ -n "$DB_USER" ] && [ -n "$DB_PASS" ] && [ -n "$DB_NAME" ]; then
        # Check if component exists in extensions table
        COMPONENT_EXISTS=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -sNe "SELECT COUNT(*) FROM ${DB_PREFIX}extensions WHERE element = 'com_produccion' AND type = 'component'" 2>/dev/null)
        
        if [ "$COMPONENT_EXISTS" -gt 0 ]; then
            print_warning "Component is registered in database. Consider uninstalling from Joomla admin first."
            print_info "You can uninstall from: Extensions > Manage > Components > Production Management"
        else
            print_info "Component not registered in database (clean state)"
        fi
    else
        print_warning "Could not read database credentials from configuration.php"
    fi
else
    print_warning "MySQL client not available"
fi

echo ""
echo "5. VERIFYING CLEAN STATE"
echo "========================"

# Verify that component directories are gone
for dir in "${component_dirs[@]}"; do
    if [ ! -d "$dir" ]; then
        print_status "Confirmed removed: $dir" 0
    else
        print_warning "Still exists: $dir"
    fi
done

echo ""
echo "6. PREPARING FOR FRESH INSTALLATION"
echo "===================================="

# Ensure directories exist and have proper permissions
target_dirs=(
    "administrator/components"
    "components"
    "media"
)

for dir in "${target_dirs[@]}"; do
    if [ -d "$dir" ]; then
        chmod 755 "$dir" 2>/dev/null
        chown www-data:www-data "$dir" 2>/dev/null
        print_status "Prepared: $dir" 0
    else
        print_warning "Directory not found: $dir"
    fi
done

echo ""
echo "7. FINAL RECOMMENDATIONS"
echo "========================"

print_info "The component has been cleaned up. Now you can:"
echo "1. Try installing the component again from Joomla admin"
echo "2. Use the corrected package: com_produccion_v1.0.30_corrected.zip"
echo "3. If it still fails, check the Apache error logs for specific errors"

print_warning "If you need to restore the backup, it's in: $backup_dir"

echo ""
echo "=========================================="
echo "CLEAN INSTALL PREPARATION COMPLETE"
echo "=========================================="
echo ""
print_warning "Remember to delete this script after use for security!"
