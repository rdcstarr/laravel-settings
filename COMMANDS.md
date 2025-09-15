# Settings Commands

The Laravel Settings package now provides dedicated commands for each operation, making them easier to use and more focused.

## Available Commands

### `settings:list`
List all settings from a specific group or the default group.

```bash
# List all settings from default group
php artisan settings:list

# List settings from a specific group
php artisan settings:list --group=admin
```

### `settings:set`
Set a setting value in a specific group.

```bash
# Interactive mode (prompts for input)
php artisan settings:set

# With arguments
php artisan settings:set app.name "My Application"

# With group
php artisan settings:set site_name "Admin Panel" --group=admin
```

### `settings:get`
Get a setting value from a specific group.

```bash
# Interactive mode
php artisan settings:get

# With argument
php artisan settings:get app.name

# With group
php artisan settings:get site_name --group=admin
```

### `settings:delete`
Delete a setting from a specific group.

```bash
# Interactive mode
php artisan settings:delete

# With argument
php artisan settings:delete old.setting

# With group and skip confirmation
php artisan settings:delete old_config --group=admin --force
```

### `settings:clear-cache`
Clear settings cache for a specific group or all groups.

```bash
# Clear all cache (with confirmation)
php artisan settings:clear-cache

# Clear specific group cache
php artisan settings:clear-cache --group=admin

# Skip confirmation
php artisan settings:clear-cache --force
```

### `settings:groups`
List all available groups and optionally view their settings.

```bash
# List all groups
php artisan settings:groups
```

This command will show all available groups in a table and then offer to let you select a group to view its settings interactively.

## Interactive Features

All commands use Laravel Prompts for a beautiful interactive experience:

- **Text inputs** with placeholders and validation
- **Select dropdowns** for choosing groups
- **Confirmation prompts** for destructive operations
- **Colorful output** with proper formatting

## Migration from Old Command

If you were using the old monolithic `settings` command, here's the migration guide:

| Old Command | New Command |
|-------------|-------------|
| `php artisan settings list` | `php artisan settings:list` |
| `php artisan settings set --key=x --value=y` | `php artisan settings:set x y` |
| `php artisan settings get --key=x` | `php artisan settings:get x` |
| `php artisan settings delete --key=x` | `php artisan settings:delete x` |
| `php artisan settings clear-cache` | `php artisan settings:clear-cache` |
| `php artisan settings groups` | `php artisan settings:groups` |

## Benefits of Dedicated Commands

1. **Cleaner syntax** - Each command has a focused purpose
2. **Better help** - Each command shows relevant help and examples
3. **Easier autocomplete** - Command names are more discoverable
4. **Flexible arguments** - Different commands can have different argument structures
5. **Focused functionality** - Each command does one thing well
