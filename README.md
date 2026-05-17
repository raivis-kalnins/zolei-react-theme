# Zolei React Theme

Custom WordPress theme for Zolei.lv, built in the same direction as the supplied Soroptimist project.

## Included

- React headless homepage only (`front-page.php` + `assets/js/headless-home.js`).
- Dynamic REST payload: `/wp-json/zolei/v1/home`.
- WordPress admin settings: Appearance → Zolei settings.
- Built-in content importer: Appearance → Zolei demo import.
- Polylang-aware LV/EN pages and labels.
- Bootstrap 5.3 styling and WP BBuilder-compatible starter page content.
- 12-month tournament tab calendar with current month opened by PHP `current_time('n')`.
- Gallery slider using editable Zolei.lv image URLs.
- Bundled Zolei.lv logo and theme custom logo support.

## Recommended plugins

- Polylang for Latvian/English routing.
- WP BBuilder for editable Bootstrap/Gutenberg block layouts.

## Install

1. Upload `zolei-react-theme.zip` in WordPress → Appearance → Themes.
2. Activate the theme.
3. Activate Polylang and WP BBuilder if needed.
4. Run Appearance → Zolei demo import.
5. Edit global homepage data in Appearance → Zolei settings.


## v1.0.4 additions
- Polylang LV-first / EN translated demo pages and demo news posts.
- Header language switcher with flags when Polylang is active.
- AJAX header search across pages, posts and Zolei PDF files.
- Styled search results template and 404 page with search.
- Editable PDF/results content type with Media Library PDF upload and `[zolei_results]` shortcode.


## v1.0.5
- Expanded default gallery import with Zolei.lv foto-galerijas images.
- Homepage gallery now has a slider, load-more photo grid and lightbox.
- News cards no longer fall back to the old banner; Elite Cup uses a tournament table image.
