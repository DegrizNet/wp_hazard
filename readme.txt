=== Degriz Hazard ===
Contributors: degriz
Tags: woocommerce, clp, ghs, hazard, safety data sheet
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.0
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
* Statement texts: official Slovenian wording of Regulation (EC) No 1272/2008 (CLP), as published by the European Commission in the SAMANCTA database, page "Stavki H/P" (https://ec.europa.eu/taxation_customs/dds2/SAMANCTA/SL/Safety/HP_SL.htm), version 1.1 of 9 August 2019: 62 H statements, 22 EUH statements, 118 P statements. The stored copy is includes/data/statements-sl.json. Newer additions to CLP after that version are not in the list; add them with the degriz_hazard_statements filter, and check the wording against the current CLP text on EUR-Lex before relying on it. The list is abbreviated by its publisher in places: P statements with a blank to be filled in end with "..." (P230, P264, P320, P321, P411, P413, P501 and the combined ones), statements with "/..." keep that placeholder, and the H340, H341, H350, H351, H360, H361, H370-H373 texts lack the bracketed "state the route of exposure" part. The official complete wording is in Annexes III and IV of the Slovenian CLP Regulation; I could not retrieve it (EUR-Lex was unavailable), so the plugin shows the list as published and lets you enter the complete text per code under WooCommerce > Hazard (CLP) > Statement texts. The plugin is a display tool: classification and labelling remain the responsibility of the supplier.
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

= 1.0.0 =
* First release.

== Upgrade Notice ==

= 1.0.0 =
First release.
