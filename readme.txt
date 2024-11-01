=== Sunray Author Manager ===
Contributors: mattkressel
Donate link: http://sunraycomputer.com/plugins/
Tags: author, writing, writer, bibliography, cover, slider
Requires at least: 4.6
Tested up to: 6.4.2
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A versatile plugin for writers to highlight their work, with a carousel slider and bibliography.

== Description ==

Sunray Author Manager is a versatile plugin for writers, allowing them to highlight their work in multiple formats.

The plugin can display covers of an author's publications in a responsive carousel slider. The plugin can also display an author's sorted bibliography.

There are many configurable options, including slider image size, slider speed, sorting by title and date, ascending and descending, and sorting of reprints.

All of the options are also accessible via simple shortcodes to include in your WordPress themes.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/sunray-author-manager` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Author Manager->Settings screen to configure the plugin
4. Add your Stories via Author Manager->Stories->Add A Story
5. Add one or more shortcodes to your WordPress posts and pages

== Frequently Asked Questions ==

= Is this plugin responsive? =

Yes, the plugin is responsive.

= Where do I put reviews of my stories and books? =

Add them in the story post content using the WordPress editor. They will appear under the book/story details.

= What do I put in the WordPress editor area? =

Any content you want displayed under your story/book details.

= How do I show only stories or books in the slider and bibliography?

See the shortcode options below.

= Can I link to Amazon using my Affiliates Tag? =

Yes. Enter your affiliates tag in the Settings page. Links to Amazon will append your affiliate tag.

= What does "Sort reprints" do? =

This option controls whether or not reprinted books/stories are indented under the original book/story in the bibliography.

= What does the "Date headings" feature do? =

With this feature enabled, publications will be separated by year under each year heading.

= Why are my slider cover images varying width? =

This is to prevent the cropping of images and to make sure the images fit within the specified height.

= Why is my slider not appearing? =

Remove any bold (<strong></strong>) or italic (<em></em>) tags from your shortcode. They can sometimes interfere with the Slick slider rendering.

= What are the shortcodes and their options? =

**[sam_slider]**

Use the above shortcode where you want the carousel slider of covers to appear. The full options are:

orderby="title|date"

Order covers by title or date. Default: by date.

order="asc|desc"

Order ascending or descending. Default: descending.

type="book|story"

Show the book or story slider, or both. Separate by a comma. Default: both.

post_ids="1,5,7,..."

Only show the books or stories with these listed post IDs. Separate each ID with commas. This overrides all sorting, reprint, and "Show in slider" options. Posts are displayed in the order specified.

speed="8"

Control the delay of the slider, in seconds.

height="250"

Control the height of the slider, in pixels.

Full example:

**[sam_slider orderby="title" order="asc" type="book,story" speed="7" height="400"]**

This will display the cover carousel slider for books and stories, ordered by title and sorted ascending, with a seven second delay per slide, and the slider will be 400 pixels high.

**[sam_biblio]**

Use the above shortcode where you want the bibliography to appear. The full options are:

orderby="title|date"

Order bibliography by title or date. Default: by date.

order="asc|desc"

Order ascending or descending. Default: descending.

sort_reprints="1|0"

Whether or not to group reprints under the original story as a sub item, or list them at the top level. Default: reprints are sorted.

date_headings="1|0"

Whether or not to organize by year and show year headings. Default: do not show date headings.

type="book|story"

Show the book or story slider, or both. Separate by a comma. Default: both.

Full example:

**[sam_biblio orderby="date" order="desc" sort_reprints="1" type="book,story"]**

This will display the full bibliography for books and stories, sorted by date, showing the newest first. Reprints will be listed under the original publication as sub-items.

= I'm curious about your work. Where can I read it? =

I write science fiction and fantasy. You can find free copies of my work at https://www.matthewkressel.net/ or @mattkressel.

== Screenshots ==

1. An example of the cover carousel slider.
2. The cover carousel slider with the third item hovered over.
3. The bibliography, with reprints sorted.
4. A single story page.
5. The settings page.
6. The story edit page, showing publication details.

== Changelog ==

= 1.0.18 =
* Fix ISBN-13 links beginning with 979 

= 1.0.17 =
* Fix Amazon bookstore link duplication bug
* Add shortcode instructions to settings page

= 1.0.15 =
* CSS and stylesheet updates

= 1.0.14 =
* Updated Amazon URLs to search via ISBN and allow for affiliate tags

= 1.0.13 =
* Fix bug show italics or quotes for book or story

= 1.0.12 =
* Fix bug audiobook links not saving

= 1.0.11 =
* Deprecated like_escape function

= 1.0.10 =
* Remove unused where clause

= 1.0.9 =
* Fix settings options being ignored

= 1.0.8 =
* Bugfixes

= 1.0.7 =
* Fix bug on books slug settings

= 1.0.6 =
* Date headings
* Better reprint handling
* Ability to change URL slug for custom post types
* Many bugfixes!

= 1.0.5 =
* Bookstore links moved to story/book entry page
* Added custom bookstore option
* Bookstore links open in new tab

= 1.0.4 =
* Bookstore links preference to publication name over title

= 1.0.3 =
* Added bookstore links option
* Changed paragraph breaking on single pages so editor content appears under details, instead of under image

= 1.0.2 =
* Bugfix

= 1.0.1 =
* Added Publisher field
* Added manual slider controls for post IDs, height, and speed

= 1.0.0 =
* Added books!
* Changed dashboard icon
* Added shortcode option to show books, stories, or both
* Added option to hide cover from slider

= 0.9.6 =
* Fix overlay centering bug
* Add customizable text for URL links

= 0.9.5 =
* Cosmetic updates

= 0.9.4 =
* Hides slideshow until loaded

= 0.9.3 =
* Uses "large" thumbnail instead of full size for faster loading

= 0.9.2 =
* Fix verify URL bug that prevented saving of URLs

= 0.9.1 =
* Fix reprint sorting bug

= 0.9 =
* First release.

== Upgrade Notice ==

= 0.9.2 =
* Crticial fix of verify URL bug that prevented saving of URLs

= 0.9.1 =
* Critical fix to reprint sorting bug

= 0.9 =
* First release.
