=== Chat2Book Button ===
Contributors: marcobellu
Tags: contact, booking, chat, tourism, button
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add a lightweight contact/booking button that opens WhatsApp with a prefilled message. Perfect for B&Bs, tours, museums, rentals.

== Description ==
**Chat2Book Contact Button** lets visitors contact you on WhatsApp with prefilled details (dates, guests, participants) instead of filling long forms. Ideal for small hospitality and activities where messaging is faster than email.

**Features**
- Shortcode with two modes:
  - **bnb**: check-in, check-out, guests
  - **excursion**: date, participants
- Customizable message templates with placeholders
- Multiple instances on the same page (unique IDs)
- Client-side validation (dates and required fields)
- No tracking, no data stored on the server
- Translation-ready (IT/EN included)

**What it doesn’t do**
This is not a full booking engine: no payments, no availability calendars. It simply opens WhatsApp with a ready-to-send message.

**Privacy**
The plugin doesn’t store or transmit personal data to your server. The message is composed client-side and opened via `wa.me`.

**Trademark notice**
WhatsApp is a trademark of Meta Platforms, Inc. This plugin is not affiliated with, sponsored or approved by Meta or WhatsApp.

== Installation ==
1. Upload the `easy-wa-contact-button` folder to `/wp-content/plugins/`, or upload the ZIP via *Plugins → Add New*.
2. Activate **Chat2Book Contact Button** in *Plugins → Installed*.
3. Go to *Settings → Chat2Book Contact* and set your default WhatsApp number and message templates.
4. Insert the shortcode in a page or post.

== Usage ==
**Shortcodes**
[c2book-button mode="bnb"]
[c2book-button mode="excursion"]
[c2book-button number="393123456789" mode="bnb"]


**Attributes**
- `mode` (required): `bnb` or `excursion`
- `number` (optional): overrides the default number (use international format without “+”, e.g. `393...`)

**Placeholders in templates**
- **B&B**: `{checkin}`, `{checkout}`, `{ospiti}`, `{data}`, `{persone}`, `{titolo}`
- **Excursions**: `{date}`, `{participants}`, `{titolo}`

== Frequently Asked Questions ==
= Is this official WhatsApp software? =
No. It’s not affiliated with or approved by WhatsApp/Meta.

= Do I need WhatsApp Business? =
No. It works with both WhatsApp and WhatsApp Business.

= Gutenberg/Elementor support? =
Yes. Use the shortcode block or a shortcode widget.

= Multiple forms on the same page? =
Yes. Each instance is isolated and uses unique IDs.

= Number format? =
International format without the `+`. The plugin sanitizes it to digits.

= GDPR concerns? =
The plugin itself does not collect/store personal data. The chat opens in WhatsApp; the user sends the message there.

= Desktop vs mobile? =
On desktop it opens WhatsApp Desktop or WhatsApp Web (if available). On mobile it opens the app.

== Screenshots ==
1. B&B form (check-in/out and guests)
2. Excursion form (date and participants)
3. Settings page

== Changelog ==
= 1.0.0 =
* Initial public release: shortcodes for bnb/excursion, message templates, basic validation, multiple instances, i18n.

== Upgrade Notice ==
= 1.0.0 =
Initial release.
