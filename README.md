# Chat2Book Button

Un plugin WordPress leggero che apre **la chat (es. WhatsApp)** con un messaggio precompilato per richieste rapide (B&B, escursioni, musei, noleggi).

## Caratteristiche
- Due modalità via shortcode:
  - **bnb**: check-in, check-out, ospiti
  - **excursion**: data, partecipanti
- Template messaggi personalizzabili (placeholders)
- Validazioni lato client, multi-istanza, i18n
- Nessun tracciamento, nessun dato salvato lato server

## Installazione
Scarica lo ZIP della release (es. `chat2book-button-v1.0.2.zip`) e installalo da *Plugin → Aggiungi nuovo → Carica plugin*.

## Attributi
- `mode`: `bnb` oppure `excursion`
- `number`: opzionale, formato internazionale senza “+” (es. `393...`)

## Placeholders
- B&B: `{checkin}`, `{checkout}`, `{ospiti}`, `{data}`, `{persone}`, `{titolo}`
- Escursioni: `{date}`, `{participants}`, `{titolo}`

## Note legali
*WhatsApp è un marchio registrato di Meta Platforms, Inc. Questo plugin non è affiliato, sponsorizzato né approvato da Meta o da WhatsApp.*

## Uso
Shortcode:
```html
[c2book-button mode="bnb"]
[c2book-button mode="excursion"]
[c2book-button number="393123456789" mode="bnb"]

##Licenza
GPL-2.0-or-later
