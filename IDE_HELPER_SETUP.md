# Laravel IDE Helper Installation Guide

## Purpose
This guide helps reduce the 720+ lint warnings in your Laravel project by installing Laravel IDE Helper package.

## Installation Steps

### 1. Install the package via Composer
```bash
composer require --dev barryvdh/laravel-ide-helper
```

### 2. Publish the configuration (optional)
```bash
php artisan vendor:publish --provider="Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider" --tag=config
```

### 3. Generate helper files
```bash
# Generate _ide_helper.php for Facades
php artisan ide-helper:generate

# Generate _ide_helper_models.php for Models
php artisan ide-helper:models -W

# Generate .phpstorm.meta.php for PhpStorm
php artisan ide-helper:meta
```

### 4. Add to .gitignore
Add these lines to your `.gitignore`:
```
_ide_helper.php
_ide_helper_models.php
.phpstorm.meta.php
```

### 5. (Optional) Auto-generate on composer update
Add this to your `composer.json` in the `scripts` section:
```json
"scripts": {
    "post-update-cmd": [
        "@php artisan ide-helper:generate",
        "@php artisan ide-helper:models -W",
        "@php artisan ide-helper:meta"
    ]
}
```

## What This Fixes

✅ **Fixes warnings for:**
- `route()` - Unknown function
- `session()` - Unknown function
- `collect()` - Unknown function
- `old()` - Unknown function
- `config()` - Unknown function
- `auth()` - Unknown function
- `view()` - Unknown function
- `redirect()` - Unknown function
- Model property autocomplete
- Facade autocomplete

## Expected Results

After running these commands, your lint warnings should drop from **720+** to near **0** for:
- Laravel helper functions
- Eloquent Model properties
- Facade method calls

## Alternative (if you can't install packages)

The manual stub files have been created:
- `_ide_helper_functions.php` - Basic helper function stubs
- `.phpstorm.meta.php` - PhpStorm type hints

These provide basic support but **NOT as complete as the package**.

## Recommended IDE Configuration

### For VSCode
Install extension: **"Laravel Extra Intellisense"**

### For PhpStorm
Enable: **Settings → PHP → Laravel → Enable plugin** 

---

**Note**: After installation, restart your IDE for changes to take effect.
