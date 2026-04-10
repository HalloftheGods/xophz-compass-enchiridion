# Xophz Enchiridion Library

> **Category:** Wizard's Tower · **Version:** 0.0.1

A library of magical scripts and snippets ready for use on your site.

## Description

**Enchiridion Library** is a code snippet manager for COMPASS. It provides a safe, organized way to add custom PHP, JS, and CSS snippets to your site without editing theme files — including a curated recipe library of pre-built solutions.

### Core Capabilities

- **Snippet Management** – Create, edit, and toggle custom code snippets via a Custom Post Type.
- **Safe Execution** – Snippets are executed through a controlled executor class to prevent fatal errors.
- **Recipe Library** – Pre-built snippet recipes for common WordPress customizations.
- **Code Categories** – Organize snippets by type and purpose.

## Requirements

- **Xophz COMPASS** parent plugin (active)
- WordPress 5.8+, PHP 7.4+

## Installation

1. Ensure **Xophz COMPASS** is installed and active.
2. Upload `xophz-compass-enchiridion` to `/wp-content/plugins/`.
3. Activate through the Plugins menu.
4. Access via the My Compass dashboard → **Enchiridion**.

## PHP Class Map

| Class | File | Purpose |
|---|---|---|
| `Xophz_Compass_Enchiridion` | `class-xophz-compass-enchiridion.php` | Core plugin hooks |
| `Xophz_Compass_Enchiridion_Post_Type` | `class-xophz-compass-enchiridion-post-type.php` | Custom Post Type for snippets |
| `Xophz_Compass_Enchiridion_Executor` | `class-xophz-compass-enchiridion-executor.php` | Safe snippet execution engine |
| `Xophz_Compass_Enchiridion_Recipes` | `class-xophz-compass-enchiridion-recipes.php` | Pre-built snippet recipe library |

## Frontend Routes

| Route | View | Description |
|---|---|---|
| `/enchiridion` | Dashboard | Snippet library with search, toggle, and editor |

## Changelog

### 0.0.1

- Initial release with snippet CPT, executor, and recipe library
