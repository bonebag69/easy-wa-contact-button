# Easy WA Contact Button

Un plugin WordPress leggero che apre **WhatsApp** con un messaggio precompilato per richieste rapide (B&B, escursioni, musei, noleggi).

## Caratteristiche
- Modalità **bnb** (check-in, check-out, ospiti)
- Modalità **excursion** (data, partecipanti)
- Template messaggi personalizzabili (placeholders)
- Validazioni lato client, multi-istanza, i18n

## Installazione
Scarica lo ZIP del repository (**Code → Download ZIP**) e installalo da *Plugin → Aggiungi nuovo → Carica plugin*.

## Uso
Shortcode:
[ewa-contact-button mode="bnb"]
[ewa-contact-button mode="excursion"]
[ewa-contact-button number="393123456789" mode="bnb"]

**Attributi**
- `mode`: `bnb` oppure `excursion`
- `number`: opzionale, formato internazionale senza “+” (es. `393...`)

**Placeholders**
- B&B: `{checkin}`, `{checkout}`, `{ospiti}`, `{data}`, `{persone}`, `{titolo}`
- Escursioni: `{date}`, `{participants}`, `{titolo}`

## Note legali
*WhatsApp è un marchio registrato di Meta Platforms, Inc. Questo plugin non è affiliato, sponsorizzato né approvato da Meta o da WhatsApp.*
