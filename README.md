# Zolei React Theme

Version 3.4.0 packages the launch-ready React-style WordPress theme for Zolei.lv with WP BBuilder compatibility, old-site content migration helpers, mobile tournament improvements and production integrations.


## v3.4.0 compact Turnīri + fast PDF workflow

- Replaces the one-CPT-per-tournament admin workflow with one compact **Turnīri** screen: 12 month tabs, inline editing, drag/drop ordering, duplicate/delete and bulk paste.
- Seeds the calendar from the packaged zolei.lv snapshot first, preserves missing historical month/year groups from the database, and can sync all 12 live month pages from **zolei.lv** in one click. The frontend automatically uses the current available year instead of falling to an empty calendar.
- Keeps the old `zolei_tournament` post type hidden only for rollback/migration compatibility; daily tournament editing no longer uses CPT posts.
- Hides the raw `zolei_pdf` admin list and adds **Turnīri → PDF faili** with multi-file drag/drop upload. PDF records are created automatically in the background so the existing results/rating/archive frontend remains compatible.
- Fixes date parsing so score notation such as `10/28/1` is not mistaken for a date, preserves known source days for date-less rows, and keeps manual drag/drop edits from being overwritten by later syncs.

## v3.3.0 dynamic tournaments + theme-side homepage editing

- v3.3 originally introduced editable tournament records; v3.4 supersedes that CPT workflow with the compact month-by-month manager above.
- Imports the packaged legacy month calendar into editable tournament records once, so future monthly updates are no longer tied to the hard-coded JSON file.
- Adds **Appearance -> Homepage editor** with bilingual LV/EN controls for hero, quick links, calendar, news, gallery, contact, partner and information copy, plus hero/partner images, links, news count and default calendar year.
- Adds a theme-only Gutenberg compatibility registration for `wpbb/hero` when WP BBuilder does not register that block on this site. WP BBuilder itself is not changed.
- The live React homepage reads Hero, CTA and Dynamic Form attributes from the saved front-page blocks, while explicit Homepage editor values can override them.
- Keeps the v3.2 AVIF, image path repair, SEO/performance and hCaptcha fixes.



## v3.1.1 hCaptcha + mobile tournament fix

- The React contact form now automatically uses **WP BBuilder → Dynamic Forms → hCaptcha** when hCaptcha is enabled there and both keys are saved.
- Zolei control panel hCaptcha fields remain available only as an optional override, so keys do not need to be duplicated.
- Server-side hCaptcha verification uses the same resolved configuration as the frontend widget.
- Mobile tournament rows now use a fixed compact date-number column, keeping every event description aligned and readable.
- Very narrow screens use a slightly smaller date block and hCaptcha is prevented from forcing horizontal page overflow.

## What is included

- Single-page React/headless homepage with centered section headings, softer typography, improved spacing and a cleaner one-page layout.
- Softer typography using Nunito Sans and lighter font weights.
- WP BBuilder block starter content for homepage and imported pages.
- Old Zolei.lv WordPress export manifests in `assets/data/`:
  - `zolei-content.json` — old pages/posts packaged from the export.
  - `zolei-pdfs.json` — all PDF attachment records found in the export.
- `zolei_pdf` admin post type for editable PDF/document records.
- Appearance → Zolei control panel: directory-based PDF manager for old `wp-content/uploads` folders, upload/delete actions, demo PDF import, automatic compression and LV/EN section labels.
- `[zolei_results]` shortcode with type/year/search filters.
- `[zolei_calendar]` shortcode for monthly tournament content.

## Recommended install flow

1. Install and activate WP BBuilder.
2. Upload and activate this theme, then open **Turnīri** once. The compact manager seeds itself from the packaged zolei.lv snapshot/database history and attempts a live zolei.lv sync.
3. Go to **Appearance → Zolei demo import** and click **Import / Update one-page site only, then Import packaged PDFs only when ready**.
4. Go to **Appearance → Zolei settings** to review gallery, contact details and PDF management.
5. Use **Turnīri → PDF faili** for fast multi-PDF drag/drop upload; the raw PDF CPT screen is hidden.

## PDF migration notes

By default the PDF records point to old-site source URLs. This is fastest for a first demo. In Theme Settings you can enable/download PDFs into the new Media Library when preparing final production migration.


## v1.4.0 one-page mode

- Public site is now one React headless page with anchor sections instead of many generated pages.
- Old website pages/months are imported into editable `One-page sections` admin records.
- Extra generated pages from earlier demo imports are moved to Trash on import.
- PDF manager remains admin-side and can attach PDF groups to each one-page section.
- Navigation uses anchor links like `/#calendar`, `/#rules`, `/#results`, `/#ratings`, `/#protocols`, `/#archive`, `/#gallery`, and `/#contact`.


## v1.4.0 PDF section fix

- Public site remains one React/headless page.
- PDF sections are controlled in one place: Appearance -> Zolei settings -> PDF section manager.
- LV/EN fields translate only section labels/text. PDF files are shared and unchanged for both languages.
- Old-site PDF manifest now includes more PDF links from the WordPress export and classifies them into Results, Ratings, Rules, Protocols, and Archive groups.
- Broken/empty PDF blocks now show a clear admin hint instead of looking broken.


## Version 1.4.0 updates

- Packaged the uploaded old `wp-content/uploads` PDF directories inside the theme under `assets/old-uploads`.
- Demo import now copies all packaged PDFs into the real WordPress `wp-content/uploads` tree, preserving old directories such as `rezultati`, `reitingi`, `nolikumi`, `protokoli`, and `arhivs`.
- `Appearance > Zolei settings > PDF file manager` now has separated directory options per PDF group, plus upload and delete controls.
- English translation changes only labels and admin text; PDF files and records remain shared across languages.

## v1.5.0 automatic PDF compression

- Demo-import PDFs and new admin PDF uploads now run through automatic Ghostscript compression when enabled.
- Default quality is `ebook`, which keeps readable/good quality while reducing file size.
- The theme only replaces the file when the compressed PDF is smaller; otherwise the original is kept.
- Settings are in **Appearance > Zolei settings > Automatic PDF compression**. You can enable/disable compression, choose quality, and set the Ghostscript binary path if the server does not expose `gs`.
- Uploaded PDFs still go into the selected old-site directory under `wp-content/uploads`, and LV/EN keep using the same PDF file.


## v1.6.0 frontend/admin polish

- Reworked the admin area into **Appearance → Zolei control panel**.
- PDF management is now grouped by old-site directories: `rezultati`, `reitingi`, `nolikumi`, `protokoli`, `arhivs`.
- Each directory card has upload, optional subdirectory, year/month, edit and delete controls.
- Demo import still adds all packaged PDFs first and keeps the old `wp-content/uploads/...` paths.
- Front-page section titles are centered above content, with softer Nunito Sans weights and improved margins/paddings.
- Added bilingual AJAX contact form with hCaptcha site/secret settings.
- Gallery lightbox and PDF AJAX-style search/filter UI are kept on the one-page React frontend.
- LV/EN translations are added for main section titles and form labels; PDFs remain shared between languages.


## v1.8 notes
- First demo import no longer imports PDFs, so the initial demo is fast and avoids timeout issues.
- Packaged PDFs can be imported later with a separate admin button.
- Frontend spacing, typography, card styles, icons and gentle animations were tightened for a cleaner one-page design.


## v1.9.0
- Added a Jaunumi / News blog section with exactly 3 front-page posts.
- Demo import creates Latvian and English demo posts about Zolei.lv when Polylang is available.
- Blog cards include image, title, excerpt and a 63.lv-style lightbox with share/copy controls.


## v2.0.0
- Full-width classic card-themed hero background.
- 1720px max layout/grid polish.
- Cleaner PDF names, Open button label, improved select arrows.
- WhatsApp, Facebook, X and copy sharing in footer and news lightbox.


## v2.7.0 polish
- PDF sections now show 6 items first and load 3 more at a time, with 3-card desktop rows.
- Hero background no longer uses the old photo; it uses a classic green playing-card pattern.
- News cards use three distinct packaged images with no overlay text on the image.
- Gallery thumbnails are generated larger and higher quality, with higher-quality AVIF when supported.
- Archive info box aligns beside the PDF cards.


## 3.1.0 launch-readiness update (2026-10-01)

- Re-synced the 12-month tournament calendar from the supplied live `zolei.lv` database backup (80 published event rows; live page edit dates preserved in the calendar data file).
- Reworked the mobile calendar into large, readable month accordions; the current month opens by default and `?month=<slug>` opens a requested month.
- Restored the client-supplied Latvian Zolīte Federation logo in the hero on an explicit white card surface.
- Added safe GA4 / Google Tag Manager / Search Console fields in **Appearance → Zolei control panel**. GTM takes precedence over direct GA4 to avoid duplicate pageviews.
- Added basic canonical, meta description, Open Graph, Twitter card and Organization JSON-LD output when no major SEO plugin is active.
- Added legacy month URL redirects to the homepage calendar so old `/janvaris/` … `/decembris/` links do not land on 404 after cutover.
- Updated mobile event cards and document links for easier scanning and tapping.
