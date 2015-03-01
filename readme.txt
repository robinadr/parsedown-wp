=== Parsedown for WordPress ===

contributors: rob1n
Tags: markdown, formatting, posting, writing, markup
Tested up to: 4.1.1
Stable tag: 0.2
License: MIT
License URI: http://opensource.org/licenses/MIT

This plugin processes your posts and comments using the Parsedown library. It is a direct replacement for PHP Markdown Extra by Michel Fortin.

== Description ==

**Got a bug report or want to contribute?** Please do so on the [GitHub issue tracker](https://github.com/robinadr/parsedown-wp/issues) or [submit a pull request](https://github.com/robinadr/parsedown-wp/pulls).

[Parsedown](http://parsedown.org) is an efficient, modern PHP implementation of the [Markdown](http://daringfireball.net/projects/markdown/) syntax originally developed by John Gruber. This plugin runs your posts and comments through the [Parsedown Extra](https://github.com/erusev/parsedown-extra) version, which has additional features that are part of the [Markdown Extra](https://michelf.ca/projects/php-markdown/extra/) extension by Michel Fortin.

Michel wrote the [original Markdown plugin for WordPress](https://michelf.ca/projects/php-markdown/classic/), and this aims to be a 100% compatible drop-in replacement for that. Michel's original plugin is no longer supported as of February 1, 2014.

So **why Parsedown?** It's [faster](http://parsedown.org/speed), [more consistent](http://parsedown.org/consistency) and is being [actively developed](https://github.com/erusev/parsedown).

Development of this WordPress plugin [takes place over on GitHub](https://github.com/robinadr/parsedown-wp). If you have any support requests, I do monitor the support forums on here, or you can [contact me directly](http://robinadr.com/contact).

== Installation ==

1. Upload the `parsedown-wp` folder to the `wp-content/plugins/` directory
2. Activate the plugin through the Plugins menu in the WordPress admin

That's it. All your posts and comments are now being processed by Parsedown.

== Changelog ==

= 0.2 =

Fixed unintended behavior of converting HTML special characters. WordPress does this automatically, and converting makes curly quotes not work.

= 0.1 =

Initial release.