# Easy WA Contact Button
Add a lightweight contact/booking button that opens WhatsApp with a prefilled message. Perfect for B&Bs, tours, museums, rentals.

## Features
- Two modes via shortcode:
  - **bnb**: check-in, check-out, guests
  - **excursion**: date, participants
- Customizable message templates with placeholders
- Multiple instances on the same page
- Client-side validation (dates, required fields)
- Translation-ready (`languages/`)

## Usage
Shortcodes:
```html
[ewa-contact-button mode="bnb"]
[ewa-contact-button mode="excursion"]
[ewa-contact-button number="393123456789" mode="bnb"]
```

Attributes:
- `mode`: `bnb` or `excursion`
- `number`: optional override (international, no `+`)

## Dev
```bash
composer install
vendor/bin/phpcs
vendor/bin/phpcbf
```
Release:
```bash
git tag v1.0.0 && git push origin v1.0.0
```

## Legal
WhatsApp is a trademark of Meta Platforms, Inc. This project is not affiliated with, sponsored or approved by Meta/WhatsApp.

## License
GPL-2.0-or-later
```
