=== Degriz Hazard ===
Contributors: degriz
Tags: woocommerce, clp, ghs, hazard, safety data sheet
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

CLP hazard information for WooCommerce products: signal word, GHS pictograms, H and P statements, safety data sheet and technical data sheet downloads.

== Description ==

For shops that sell chemical products (for example professional cleaning products). Per product, in the product editor, tab "Hazard (CLP)":

* Signal word (Nevarno / Pozor).
* GHS pictograms GHS01 to GHS09.
* Hazard statements (H, EUH) and precautionary statements (P) chosen from a built-in list with the official Slovenian texts.
* Safety data sheet (varnostni list) and technical data sheet PDFs from the media library.

Front end: a "Safety and warnings" tab on the product page with the pictograms, the signal word, the statements and download links; optionally small pictograms under the price on product cards. Variations use the data of their parent product. Bulk edit in the product list can set the signal word, add pictograms or clear the data.

Settings: WooCommerce > Hazard (CLP) (tab on/off, tab title, pictograms on cards).

= Sources =
* Statement texts: the official Slovenian wording of Regulation (EC) No 1272/2008 (CLP), consolidated text of 1 July 2026 (document "02008R1272 - SL - 01.07.2026 - 031.001"), taken from the EU Publications Office (Cellar): http://publications.europa.eu/resource/celex/02008R1272-20260701 (Accept-Language: slv, XHTML). H and EUH statements from Annex III, P statements (including the combined ones) from Annex IV part 2: 91 H, 31 EUH and 127 P statements, including the endocrine disruptor and PBT/vPvB statements EUH380, EUH381, EUH430, EUH431, EUH440, EUH441, EUH450 and EUH451 added by Delegated Regulation (EU) 2023/707. The stored copy is includes/data/statements-sl.json; its "meta" block records the source, the consolidation date and the exceptions.
* Placeholders are part of the official text and are left as they are: text in angle brackets <...> (for example H340 "<navesti način izpostavljenosti, ...>", EUH208 "<ime snovi, ki povzroča preobčutljivost>") and "..." (for example P501 "Odstraniti vsebino/posodo ...", whose conditions of use in Annex IV add "... v skladu z lokalnimi/regionalnimi/nacionalnimi/mednarodnimi predpisi (se določi)"). The supplier completes them: under WooCommerce > Hazard (CLP) > Statement texts you can enter the completed text per code.
* Exceptions inside the same document: H220, H221, H222, H230 and H231 have no language table, their text is taken from the hazard class tables of the same consolidated text; the letter variants H350i, H360F, H360D, H360FD, H360Fd, H360Df, H361f, H361d and H361fd come from the table that explains them; EUH001 is deleted in the consolidated text. Codes added by later amendments after 1 July 2026 are not in the list; add them with the degriz_hazard_statements filter. Disclaimer: the statement texts follow Regulation (EC) No 1272/2008 (CLP), consolidated text of 1 July 2026, and are provided for information only. Classification and labelling remain the responsibility of the supplier: check every hazard class, pictogram, signal word, H statement and P statement against the safety data sheet (SDS) of the product. You alone are responsible for the correctness, completeness and legal compliance of what your shop shows. This is not legal advice. The plugin is provided "as is", without warranty of any kind, and its author is not liable for any damage arising from its use.
* GHS pictograms GHS01-GHS09: the pictograms of the UN GHS (UNECE), which are in the public domain; the SVG files come from Wikimedia Commons (files "GHS-pictogram-*.svg").
* Pictogram names and the signal words are standard labels; "Nevarno" and "Pozor" are the Slovenian CLP signal words.

Compatible with HPOS and the Cart and Checkout blocks. Slovenian translation included (sl_SI).

== Installation ==

1. Upload the plugin to wp-content/plugins and activate it (WooCommerce must be active).
2. Edit a product, open the "Hazard (CLP)" tab under Product data, fill it in and update the product.

== Frequently Asked Questions ==

= The tab does not show on the product page =
It is shown only when the product has at least one hazard field filled in and "Show the safety tab" is on.

= I need a statement that is not in the list =
Add it with the degriz_hazard_statements filter (code => text, group "H" or "P").

== Changelog ==

= 1.0.2 =
* Liability disclaimer in the settings, the README and the readme (check the hazard data against the safety data sheet); the GPL licence text added. No functional change.

= 1.0.1 =
* Statement texts replaced by the official Slovenian wording of the consolidated CLP Regulation of 1 July 2026 (Annex III and IV), with all placeholders kept as in the regulation.

= 1.0.0 =
* First release.

== Upgrade Notice ==

= 1.0.2 =
Disclaimer added; no functional change.

= 1.0.1 =
Official Slovenian statement texts from the consolidated CLP Regulation.
