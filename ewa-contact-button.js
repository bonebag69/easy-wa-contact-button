(function () {
  function fmtIT(dateStr) {
    const d = new Date(dateStr);
    if (Number.isNaN(d.getTime())) return '';
    try { return new Intl.DateTimeFormat('it-IT').format(d); } catch (_) { return ''; }
  }

  function setMinCheckout(ciEl, coEl) {
    coEl.min = ciEl.value || ciEl.min || '';
    if (coEl.value && coEl.value <= coEl.min) coEl.value = '';
  }

  function buildMessage(mode, tpl, title, fields) {
    let txt = tpl || '';
    const map = { '{titolo}': title };

    if (mode === 'excursion') {
      map['{date}'] = fmtIT(fields.date);
      map['{participants}'] = fields.participants;
    } else {
      map['{checkin}']  = fmtIT(fields.ci);
      map['{checkout}'] = fmtIT(fields.co);
      map['{ospiti}']   = fields.guests;
      map['{data}']     = map['{checkin}'];
      map['{persone}']  = fields.guests;
    }

    Object.keys(map).forEach(ph => {
      txt = txt.split(ph).join(map[ph] ?? '');
    });

    return `Richiesta da pagina: "${title}" - ${txt}`;
  }

  function attach(container) {
    const mode   = container.dataset.mode;
    const number = container.dataset.number || (window.EWA_CONTACT && EWA_CONTACT.defaultNumber) || '';
    const btn  = container.querySelector('.ewa-contact__send');
    const err  = container.querySelector('.ewa-contact__error');

    if (mode === 'excursion') {
      const date  = container.querySelector('input[type="date"]');
      const parts = container.querySelector('select');
      if (window.EWA_CONTACT && EWA_CONTACT.defaultDate) date.value = EWA_CONTACT.defaultDate;

      btn.addEventListener('click', () => {
        err.style.display = 'none'; err.textContent = '';
        if (!date.value)  { err.textContent = EWA_CONTACT.i18n.validation.date_required; err.style.display = 'block'; return; }
        if (!parts.value) { err.textContent = EWA_CONTACT.i18n.validation.participants_req; err.style.display = 'block'; return; }

        const msg = buildMessage('excursion', EWA_CONTACT.tplExc, document.title, { date: date.value, participants: parts.value });
        window.open(`https://wa.me/${number}?text=${encodeURIComponent(msg)}`, '_blank', 'noopener');
      });
    } else {
      const ci     = container.querySelector('#' + container.id + '-ci');
      const co     = container.querySelector('#' + container.id + '-co');
      const guests = container.querySelector('#' + container.id + '-guests');

      if (window.EWA_CONTACT && EWA_CONTACT.defaultDate) ci.value = EWA_CONTACT.defaultDate;
      setMinCheckout(ci, co);
      ci.addEventListener('change', () => setMinCheckout(ci, co));

      btn.addEventListener('click', () => {
        err.style.display = 'none'; err.textContent = '';
        if (!ci.value)      { err.textContent = EWA_CONTACT.i18n.validation.checkin_required;  err.style.display = 'block'; return; }
        if (!co.value)      { err.textContent = EWA_CONTACT.i18n.validation.checkout_required; err.style.display = 'block'; return; }
        if (co.value <= ci.value) { err.textContent = EWA_CONTACT.i18n.validation.checkout_after;   err.style.display = 'block'; return; }
        if (!guests.value)  { err.textContent = EWA_CONTACT.i18n.validation.guests_required;   err.style.display = 'block'; return; }

        const msg = buildMessage('bnb', EWA_CONTACT.tplBnb, document.title, { ci: ci.value, co: co.value, guests: guests.value });
        window.open(`https://wa.me/${number}?text=${encodeURIComponent(msg)}`, '_blank', 'noopener');
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.ewa-contact').forEach(attach);
  });
})();
