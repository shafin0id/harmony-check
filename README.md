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
3. Go to **Tools → Harmony Check** to see your report

## What It Checks For

Currently monitors for:

- **Multiple caching plugins** - WP Super Cache, W3 Total Cache, WP Rocket, etc.
- **Multiple SEO plugins** - Yoast, Rank Math, All in One SEO, etc.
- **Stacked page builders** - Elementor + Divi running simultaneously
- **WooCommerce + security conflicts** - Overly aggressive firewall rules breaking checkout
- **Jetpack feature duplication** - Running Jetpack plus standalone versions of its features
- **Multiple form builders** - Contact Form 7, WPForms, Ninja Forms all active at once

This isn't an exhaustive list. It's just the common stuff that shows up repeatedly in support tickets.

## How It Works

1. Checks your active plugins against a predefined list of conflict patterns
2. Shows admin notices if it finds something worth mentioning
3. Logs findings to `debug.log` if `WP_DEBUG` is enabled
4. Provides a report page at **Tools → Harmony Check**

Notices are dismissible and won't nag you forever.

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
