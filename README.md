# Harmony Check

A WordPress plugin that detects common plugin conflicts based on real support experience.

## What It Does

Harmony Check quietly monitors your active plugins and alerts you if you're running combinations that commonly cause problems. Think of it as a second pair of eyes looking at your plugin setup.

It doesn't:
- Automatically disable plugins
- Modify your database
- Make changes to your site
- Guarantee anything will work

It just points out patterns that, in real-world support work, tend to lead to headaches.

## Why This Exists

After years of helping people troubleshoot WordPress sites, you start to see patterns. Multiple caching plugins. Three different SEO plugins all fighting each other. Page builders stacked on top of each other.

Most of these issues are preventable if you just know what to look for. That's what this plugin does.

## Installation

1. Upload the `harmony-check` folder to `/wp-content/plugins/`
2. Activate through the WordPress admin
3. Go to the **Harmony Check** menu in your WordPress sidebar to see your report

## What It Checks For

Currently monitors for **14 different conflict patterns** across multiple categories:

### Performance & Caching
- **Multiple page caching plugins** - WP Super Cache, W3 Total Cache, WP Rocket, LiteSpeed Cache, Comet Cache, Hyper Cache
- **Double minification** - Autoptimize, Fast Velocity Minify, W3 Total Cache, WP Rocket, SG Optimizer
- **Multiple image optimizers** - Smush, Imagify, EWWW, ShortPixel, TinyPNG

### SEO & Analytics
- **Multiple SEO plugins** - Yoast, Rank Math, All in One SEO, The SEO Framework, SmartCrawl
- **Double analytics tracking** - Multiple Google Analytics plugins running simultaneously

### Security & SSL
- **HTTPS enforcement conflicts** - Really Simple SSL, Wordfence, iThemes Security, SG Optimizer
- **Multiple firewalls** - Wordfence, iThemes Security, All In One WP Security, NinjaFirewall

### Page Builders & Performance
- **Stacked page builders** - Elementor, Divi, Beaver Builder, Oxygen running together

### Backups & Email
- **Multiple backup solutions** - UpdraftPlus, BackWPup, BackupWordPress, Duplicator
- **Multiple SMTP plugins** - WP Mail SMTP, Post SMTP, Easy WP SMTP, FluentSMTP

### E-commerce
- **WooCommerce PayPal conflicts** - Old PayPal Express vs new PayPal Payments plugin

### Known Bad Combinations
- **Jetpack feature duplication** - Jetpack plus standalone Akismet or VaultPress
- **Object cache conflicts** - Redis and Memcached plugins both active

### Debug Log Analysis
- **Fatal PHP errors** - Automatically scans debug.log for recent fatal errors
- **Database errors** - Detects WordPress database connection issues

This isn't an exhaustive list. It's the common stuff that shows up repeatedly in support tickets.

## How It Works

1. Checks your active plugins (including network-activated plugins on Multisite) against a predefined list of conflict patterns
2. Analyzes your `debug.log` file for recent fatal errors and database issues (if WP_DEBUG is enabled)
3. Shows color-coded admin notices based on severity (critical = red, warning = yellow, info = blue)
4. Logs findings to `debug.log` if `WP_DEBUG` is enabled
5. Provides a detailed report page at the **Harmony Check** menu (with wrench/tools icon)

Notices are dismissible and won't nag you forever. The plugin is **multisite-compatible** and works on both single-site and network installations.

## Limitations

- **Only checks plugins** - Doesn't know about theme conflicts or must-use plugins
- **Not comprehensive** - Can't detect every possible conflict
- **Plugin slug matching** - Looks for common folder names, might miss renamed plugins
- **No guarantee** - Just because nothing is flagged doesn't mean everything is perfect
- **Static ruleset** - Conflict patterns are hardcoded, not dynamically discovered

## Future Ideas

Things that might get added later:

- Ability to add custom conflict rules
- Detection of outdated plugins with known issues
- Check for missing dependencies (e.g., WooCommerce extensions without WooCommerce)
- Performance impact scoring based on plugin combinations
- Export report as PDF for sharing with developers

No promises on timeline. This is a side project.

## Technical Details

- **Requires**: WordPress 5.8+, PHP 7.4+
- **Admin only**: Zero frontend impact
- **No database**: Conflict rules are defined in code
- **Simple architecture**: 3 classes, no frameworks
- **Namespaced**: Uses PHP namespaces to avoid conflicts (ironic, right?)

## Contributing

If you want to suggest additional conflict patterns, open an issue with:
1. The plugins involved
2. What breaks when they're used together
3. How common this issue is in your experience

Bonus points if it's based on actual support experience, not hypothetical scenarios.

## Author

Made by **Shafinoid** based on real support headaches.

## License

GPL v2 or later. Use it, modify it, share it.

## Disclaimer

This plugin is provided as-is. It's a tool to help you spot potential issues, not a replacement for proper testing and monitoring. Always backup your site before making plugin changes.
