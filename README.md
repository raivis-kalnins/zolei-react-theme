# Zolei React Theme

Version 1.6.0 packages the new React-style WordPress theme for Zolei.lv with WP BBuilder compatibility and old-site content migration helpers.

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
2. Upload and activate this theme.
3. Go to **Appearance → Zolei demo import** and click **Import / Update one-page site only, then Import packaged PDFs only when ready**.
4. Go to **Appearance → Zolei settings** to review gallery, contact details and PDF management.
5. Review **Zolei PDF files** in wp-admin and update any labels/types/months before launch.

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
