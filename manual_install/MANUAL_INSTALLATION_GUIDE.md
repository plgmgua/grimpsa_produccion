# Manual Joomla Component Installation Guide

## Step 1: Create Directory Structure

SSH into your server and run these commands:

```bash
cd /var/www/grimpsa_webserver

# Create component directories
mkdir -p administrator/components/com_produccion
mkdir -p components/com_produccion
mkdir -p media/com_produccion

# Set proper permissions
chown -R www-data:www-data administrator/components/com_produccion
chown -R www-data:www-data components/com_produccion
chown -R www-data:www-data media/com_produccion

chmod -R 755 administrator/components/com_produccion
chmod -R 755 components/com_produccion
chmod -R 755 media/com_produccion
```

## Step 2: Upload Component Files

Upload the following files to your server:

### ⚠️ CRITICAL: XML Manifest Files
**You MUST include the XML manifest files for Joomla to recognize the component:**
- `administrator/components/com_produccion/com_produccion.xml` - Admin component manifest
- `components/com_produccion/com_produccion.xml` - Site component manifest

These XML files tell Joomla:
- What the component is
- How to load it
- What files belong to it
- Menu structure
- Dependencies

### Admin Component Files (to `/administrator/components/com_produccion/`):
- `administrator/components/com_produccion/produccion.php`
- `administrator/components/com_produccion/controller.php`
- `administrator/components/com_produccion/config.xml`
- `administrator/components/com_produccion/debug.php`
- `administrator/components/com_produccion/com_produccion.xml` ⚠️ **IMPORTANT**
- `administrator/components/com_produccion/controllers/` (entire folder)
- `administrator/components/com_produccion/models/` (entire folder)
- `administrator/components/com_produccion/services/` (entire folder)
- `administrator/components/com_produccion/src/` (entire folder)
- `administrator/components/com_produccion/language/` (entire folder)
- `administrator/components/com_produccion/tmpl/` (entire folder)

### Site Component Files (to `/components/com_produccion/`):
- `components/com_produccion/produccion.php`
- `components/com_produccion/com_produccion.xml` ⚠️ **IMPORTANT**
- `components/com_produccion/services/` (entire folder)
- `components/com_produccion/src/` (entire folder)
- `components/com_produccion/language/` (entire folder)

### Media Files (to `/media/com_produccion/`):
- `media/com_produccion/css/` (entire folder)
- `media/com_produccion/js/` (entire folder)

## Step 3: Register Component in Database

### Option A: Using PHP Script (Recommended)
1. Upload `register_component.php` to your Joomla root directory
2. Access it via browser: `https://your-domain.com/register_component.php`
3. Follow the instructions on the page
4. Delete the script after use

### Option B: Using SQL (Alternative)
1. Access your database (phpMyAdmin or command line)
2. Run the SQL commands from `register_component.sql`
3. Adjust table prefixes if needed (replace `joomla_` with your actual prefix)

## Step 4: Set File Permissions

```bash
# Set proper ownership
chown -R www-data:www-data /var/www/grimpsa_webserver/administrator/components/com_produccion
chown -R www-data:www-data /var/www/grimpsa_webserver/components/com_produccion
chown -R www-data:www-data /var/www/grimpsa_webserver/media/com_produccion

# Set proper permissions
find /var/www/grimpsa_webserver/administrator/components/com_produccion -type d -exec chmod 755 {} \;
find /var/www/grimpsa_webserver/administrator/components/com_produccion -type f -exec chmod 644 {} \;
find /var/www/grimpsa_webserver/components/com_produccion -type d -exec chmod 755 {} \;
find /var/www/grimpsa_webserver/components/com_produccion -type f -exec chmod 644 {} \;
find /var/www/grimpsa_webserver/media/com_produccion -type d -exec chmod 755 {} \;
find /var/www/grimpsa_webserver/media/com_produccion -type f -exec chmod 644 {} \;
```

## Step 5: Clear Joomla Cache

```bash
# Clear Joomla cache
rm -rf /var/www/grimpsa_webserver/cache/*
rm -rf /var/www/grimpsa_webserver/administrator/cache/*
rm -rf /var/www/grimpsa_webserver/tmp/*
```

## Step 6: Test Installation

1. **Access Admin Interface**: Go to `/administrator/index.php?option=com_produccion`
2. **Check Menu**: Look for "COM_PRODUCCION" in the admin menu
3. **Test Views**: Try accessing different views (dashboard, ordenes, webhook, debug)
4. **Test Webhook**: Access `/index.php?option=com_produccion&task=webhook.receive`

## Troubleshooting

### If component doesn't appear in admin menu:
1. Check database registration in `#__extensions` table
2. Verify file permissions
3. Clear Joomla cache
4. Check for PHP errors in logs

### If pages show blank:
1. Check file permissions
2. Verify all files are uploaded correctly
3. Check PHP error logs
4. Ensure all required files are present

### If webhook doesn't work:
1. Check `/index.php?option=com_produccion&task=webhook.receive`
2. Verify site component files are uploaded
3. Check file permissions on site component

## Security Notes

- Delete `register_component.php` after use
- Delete `register_component.sql` after use
- Ensure proper file permissions
- Keep component files secure

## File Structure After Installation

```
/var/www/grimpsa_webserver/
├── administrator/components/com_produccion/
│   ├── produccion.php
│   ├── controller.php
│   ├── config.xml
│   ├── debug.php
│   ├── controllers/
│   ├── models/
│   ├── services/
│   ├── src/
│   ├── language/
│   └── tmpl/
├── components/com_produccion/
│   ├── produccion.php
│   ├── services/
│   ├── src/
│   └── language/
└── media/com_produccion/
    ├── css/
    └── js/
```
