/* LU3G Login Brand – interfaccia della pagina delle impostazioni.
   - Aspetto: i due riquadri cambiano le schede visibili e l'anteprima.
   - Anteprima: titolo, pulsante, loghi, colori e font si aggiornano mentre si modificano i campi.
   - Font: select con ricerca su tutti i Google Fonts (assets/data/google-fonts.json), filtro per categoria, tastiera
     (frecce, Invio, Esc). Nell'anteprima il font si carica da Google solo qui, in wp-admin: la pagina di accesso usa
     la copia scaricata sul sito al salvataggio.
   - "Modifiche non salvate" nella barra in basso. */
(function () {
	var root = document.querySelector('.lu3g-admin');
	if (!root || !window.LU3GLogin) return;
	var $ = function (s, r) { return (r || root).querySelector(s); };
	var $$ = function (s, r) { return Array.prototype.slice.call((r || root).querySelectorAll(s)); };
	var opt = LU3GLogin.opt, pv = $('#lu3g-pv');
	var field = function (key) { return root.querySelector('[name="' + opt + '[' + key + ']"]'); };
	var checked = function (key) { var e = root.querySelector('[name="' + opt + '[' + key + ']"]:checked'); return e ? e.value : ''; };

	/* ---------- font: caricamento per l'anteprima ---------- */
	var loaded = {};
	function loadFont(family) {
		if (!family || loaded[family]) return;
		loaded[family] = true;
		var l = document.createElement('link');
		l.rel = 'stylesheet';
		l.href = 'https://fonts.googleapis.com/css2?family=' + encodeURIComponent(family).replace(/%20/g, '+') + ':wght@400;700&display=swap';
		l.onerror = function () { // alcuni font non hanno il 700: riprovo con il solo peso base
			var r = document.createElement('link'); r.rel = 'stylesheet';
			r.href = 'https://fonts.googleapis.com/css2?family=' + encodeURIComponent(family).replace(/%20/g, '+') + '&display=swap';
			document.head.appendChild(r);
		};
		document.head.appendChild(l);
	}
	function stack(family, fallback) { return family ? '"' + family + '", ' + fallback : fallback; }

	/* ---------- anteprima ---------- */
	function update() {
		var layout = checked('layout') || 'report';
		root.setAttribute('data-layout', layout);
		pv.setAttribute('data-layout', layout);
		$('.lu3g-pv__title').textContent = (field('title') && field('title').value) || 'Accedi al sito';
		$('.lu3g-pv__btn').textContent = layout === 'report' ? ((field('button') && field('button').value) || 'Accedi al pannello') : 'Accedi';

		var siteFont = (field('font') && field('font').value || '').replace(/"/g, '');
		var head = field('font_head').value.trim(), body = field('font_body').value.trim();
		loadFont(head); loadFont(body);
		var defHead = layout === 'report' ? '"Manrope Variable", sans-serif' : stack(siteFont, 'sans-serif');
		var defBody = layout === 'report' ? '"DM Sans Variable", sans-serif' : stack(siteFont, 'sans-serif');
		pv.style.setProperty('--pv-head', head ? stack(head, 'sans-serif') : defHead);
		pv.style.setProperty('--pv-body', body ? stack(body, 'sans-serif') : defBody);
		$$('.lu3g-font').forEach(function (f) {
			var v = $('.lu3g-combo__input', f).value.trim();
			var isHead = f.getAttribute('data-key') === 'font_head';
			var def = isHead ? defHead : defBody;
			// il suggerimento dice quale font si usa se il campo resta vuoto
			$('.lu3g-combo__input', f).placeholder = 'Predefinito: ' + (layout === 'report' ? (isHead ? 'Manrope' : 'DM Sans')
				: (siteFont ? 'font del sito (' + siteFont + ')' : 'font di sistema'));
			$('.lu3g-font__sample', f).style.fontFamily = v ? stack(v, 'sans-serif') : def;
		});

		var lu3gOn = layout === 'report' || (field('lu3g') && field('lu3g').checked);
		var dark = checked('logo_tone') === 'dark';
		$('.lu3g-pv__lu3g').style.display = lu3gOn ? '' : 'none';
		$('.lu3g-pv__lu3g').style.backgroundImage = 'url("' + (layout === 'sito' && dark ? LU3GLogin.lu3gDark : LU3GLogin.lu3g) + '")';
		var logo = field('logo') ? field('logo').value : '';
		$('.lu3g-pv__site').style.backgroundImage = logo ? 'url("' + logo.replace(/"/g, '%22') + '")' : 'none';
		var logos = $('.lu3g-pv__logos');
		if (layout === 'sito') {
			var bg = field('bg').value, acc = field('accent').value;
			pv.style.background = 'radial-gradient(120% 80% at 50% 0%, ' + bg + ' 0%, ' + shade(bg, 0.38) + ' 70%)';
			$('.lu3g-pv__btn').style.background = acc;
			$('.lu3g-pv__btn').style.color = lum(acc) > 0.45 ? shade(bg, 0.38) : '#fff';
			logos.style.background = dark ? '#fff' : 'none';
			logos.style.padding = dark ? '10px 14px' : '0';
			logos.style.borderRadius = dark ? '10px' : '0';
		} else {
			pv.style.background = '';
			$('.lu3g-pv__btn').style.background = ''; $('.lu3g-pv__btn').style.color = '';
			logos.style.background = ''; logos.style.padding = ''; logos.style.borderRadius = '';
		}
		$$('.lu3g-color').forEach(function (c) { $('code', c).textContent = $('input', c).value; });
	}
	function rgb(h) { h = h.replace('#', ''); return [0, 2, 4].map(function (i) { return parseInt(h.substr(i, 2), 16) || 0; }); }
	function shade(h, t) { return '#' + rgb(h).map(function (v) { return ('0' + Math.round(v * (1 - t)).toString(16)).slice(-2); }).join(''); }
	function lum(h) {
		var c = rgb(h).map(function (v) { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); });
		return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
	}

	/* ---------- select dei font ---------- */
	var catalog = [], cat = '', names = { s: 'Sans serif', r: 'Serif', d: 'Display', h: 'Calligrafici', m: 'Monospace' };
	fetch(LU3GLogin.fonts).then(function (r) { return r.json(); }).then(function (d) { catalog = d.fonts || []; }).catch(function () {});

	$$('.lu3g-chip').forEach(function (b) {
		b.addEventListener('click', function () {
			cat = b.getAttribute('data-cat');
			$$('.lu3g-chip').forEach(function (x) { x.classList.toggle('is-on', x === b); });
			$$('.lu3g-combo').forEach(function (c) { if (!$('.lu3g-combo__list', c).hidden) render(c); });
		});
	});

	function render(combo) {
		var input = $('.lu3g-combo__input', combo), list = $('.lu3g-combo__list', combo);
		var q = input.value.trim().toLowerCase(), out = [];
		for (var i = 0; i < catalog.length && out.length < 60; i++) {
			var f = catalog[i];
			if (cat && f[1] !== cat) continue;
			if (q && f[0].toLowerCase().indexOf(q) === -1) continue;
			out.push(f);
		}
		list.innerHTML = '';
		if (!out.length) {
			var e = document.createElement('li'); e.className = 'lu3g-combo__empty'; e.textContent = catalog.length ? 'Nessun font trovato' : 'Caricamento dell\'elenco…';
			list.appendChild(e);
		}
		out.forEach(function (f, i) {
			var li = document.createElement('li');
			li.setAttribute('role', 'option');
			li.id = input.id + '-opt-' + i;
			li.dataset.family = f[0];
			li.innerHTML = '<span></span><small></small>';
			li.firstChild.textContent = f[0];
			li.lastChild.textContent = names[f[1]] + (f[3] ? ' · variabile' : '');
			li.addEventListener('mousedown', function (ev) { ev.preventDefault(); choose(combo, f[0]); });
			li.addEventListener('mouseenter', function () { hoverFont(li, f[0]); });
			list.appendChild(li);
		});
		list.hidden = false;
		input.setAttribute('aria-expanded', 'true');
		combo._index = -1;
	}
	var hoverTimer;
	function hoverFont(li, family) {
		clearTimeout(hoverTimer);
		hoverTimer = setTimeout(function () { loadFont(family); li.firstChild.style.fontFamily = stack(family, 'sans-serif'); }, 150);
	}
	function close(combo) {
		var list = $('.lu3g-combo__list', combo);
		list.hidden = true;
		$('.lu3g-combo__input', combo).setAttribute('aria-expanded', 'false');
		$('.lu3g-combo__input', combo).removeAttribute('aria-activedescendant');
	}
	function choose(combo, family) {
		var input = $('.lu3g-combo__input', combo);
		input.value = family;
		close(combo);
		input.dispatchEvent(new Event('input', { bubbles: true }));
	}
	function move(combo, step) {
		var items = $$('li[role="option"]', $('.lu3g-combo__list', combo));
		if (!items.length) return;
		combo._index = (combo._index + step + items.length) % items.length;
		items.forEach(function (li, i) { li.setAttribute('aria-selected', i === combo._index ? 'true' : 'false'); });
		var cur = items[combo._index];
		cur.scrollIntoView({ block: 'nearest' });
		$('.lu3g-combo__input', combo).setAttribute('aria-activedescendant', cur.id);
		hoverFont(cur, cur.dataset.family);
	}

	$$('.lu3g-combo').forEach(function (combo) {
		var input = $('.lu3g-combo__input', combo);
		input.addEventListener('focus', function () { render(combo); });
		input.addEventListener('input', function (ev) { if (ev.isTrusted) render(combo); });
		input.addEventListener('blur', function () { setTimeout(function () { close(combo); }, 120); });
		input.addEventListener('keydown', function (ev) {
			var list = $('.lu3g-combo__list', combo);
			if (ev.key === 'ArrowDown') { ev.preventDefault(); if (list.hidden) render(combo); move(combo, 1); }
			else if (ev.key === 'ArrowUp') { ev.preventDefault(); move(combo, -1); }
			else if (ev.key === 'Enter' && !list.hidden) {
				var cur = $$('li[role="option"]', list)[combo._index];
				if (cur) { ev.preventDefault(); choose(combo, cur.dataset.family); }
			} else if (ev.key === 'Escape') { close(combo); }
		});
		$('.lu3g-combo__clear', combo).addEventListener('click', function () { choose(combo, ''); input.focus(); });
	});

	/* ---------- modifiche e salvataggio ---------- */
	var form = $('#lu3g-form'), dirty = $('#lu3g-dirty');
	form.addEventListener('input', function () { dirty.hidden = false; update(); });
	form.addEventListener('change', function () { dirty.hidden = false; update(); });
	form.addEventListener('submit', function () { dirty.hidden = true; });
	update();
})();
