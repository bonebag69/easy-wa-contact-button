(function () {
  function byId(id) { return document.getElementById(id); }

  function formatDateISOToLocale(d) {
    try { return new Date(d).toLocaleDateString('it-IT'); }
    catch (e) { return d; }
  }

  function buildMessage(mode, inst) {
    var title = byId(inst + '-title') ? byId(inst + '-title').value : document.title;
    var tpl = (mode === 'excursion') ? (C2BOOK_CONTACT.tplExc || '') : (C2BOOK_CONTACT.tplBnb || '');
    var map = { '{titolo}': title };

    if (mode === 'excursion') {
      var date = byId(inst + '-date')?.value || C2BOOK_CONTACT.defaultDate;
      var parts = byId(inst + '-participants')?.value || '';
      map['{date}'] = formatDateISOToLocale(date);
      map['{participants}'] = parts;
    } else {
      var ci = byId(inst + '-checkin')?.value || C2BOOK_CONTACT.defaultDate;
      var co = byId(inst + '-checkout')?.value || '';
      var osp = byId(inst + '-guests')?.value || '';
      map['{checkin}'] = formatDateISOToLocale(ci);
      map['{checkout}'] = formatDateISOToLocale(co);
      map['{ospiti}'] = osp;
      map['{data}'] = map['{checkin}'];    // compat
      map['{persone}'] = osp;              // compat
    }

    Object.keys(map).forEach(function (ph) {
      tpl = (tpl || '').split(ph).join(map[ph]);
    });

    return 'Richiesta da pagina: "' + title + '" - ' + tpl;
  }

  function validate(mode, inst) {
    var v = C2BOOK_CONTACT.i18n.validation;
    if (mode === 'excursion') {
      if (!byId(inst + '-date')?.value) { alert(v.date_required); return false; }
      if (!byId(inst + '-participants')?.value) { alert(v.participants_req); return false; }
      return true;
    }
    var ci = byId(inst + '-checkin')?.value;
    var co = byId(inst + '-checkout')?.value;
    var g  = byId(inst + '-guests')?.value;
    if (!ci) { alert(v.checkin_required); return false; }
    if (!co) { alert(v.checkout_required); return false; }
    if (new Date(co) <= new Date(ci)) { alert(v.checkout_after); return false; }
    if (!g) { alert(v.guests_required); return false; }
    return true;
  }

  function init() {
    document.querySelectorAll('.c2book-container').forEach(function (box) {
      var inst = box.getAttribute('data-c2book-instance');
      var mode = box.getAttribute('data-c2book-mode');

      var def = C2BOOK_CONTACT.defaultDate;
      var dEl = document.getElementById(inst + (mode === 'excursion' ? '-date' : '-checkin'));
      if (dEl && !dEl.value) dEl.value = def;

      box.querySelector('.c2book-send')?.addEventListener('click', function () {
        var number = (box.getAttribute('data-c2book-number') || C2BOOK_CONTACT.defaultNumber || '').replace(/\D+/g, '');
        if (!validate(mode, inst)) return;

        var message = buildMessage(mode, inst);
        var url = 'https://wa.me/' + number + '?text=' + encodeURIComponent(message);
        window.open(url, '_blank');
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
