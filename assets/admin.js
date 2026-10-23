/* LU3G Login Brand – "Analizza il sito".
   Apre la homepage in un iframe nascosto (stesso dominio) e legge lo stile come viene mostrato davvero,
   qualunque sia il tema o il page builder:
   - logo: logo personalizzato / logo Elementor / immagine o SVG nell'header che porta alla home;
   - tono del logo: chiaro o scuro, misurato sui pixel (decide se serve il riquadro bianco);
   - fondo: il colore scuro che occupa più superficie (header, footer, sezioni);
   - accento: il colore dei pulsanti (o, in mancanza, il colore saturo più usato);
   - font: quello dei titoli, con i fogli di stile che lo caricano. */
(function () {
	var $ = function (id) { return document.getElementById(id); };
	var btn = $('lu3g-analyze'), status = $('lu3g-status');
	if (!btn) return;

	/* ---------- colori ---------- */
	function parse(c) {
		var m = /rgba?\(([\d.]+),\s*([\d.]+),\s*([\d.]+)(?:,\s*([\d.]+))?\)/.exec(c || '');
		if (!m) return null;
		var a = m[4] === undefined ? 1 : +m[4];
		return a < 0.9 ? null : [+m[1], +m[2], +m[3]];
	}
	function hex(c) { return '#' + c.map(function (v) { return ('0' + Math.round(v).toString(16)).slice(-2); }).join(''); }
	function lum(c) {
		var v = c.map(function (x) { x /= 255; return x <= 0.03928 ? x / 12.92 : Math.pow((x + 0.055) / 1.055, 2.4); });
		return 0.2126 * v[0] + 0.7152 * v[1] + 0.0722 * v[2];
	}
	function sat(c) {
		var mx = Math.max.apply(null, c) / 255, mn = Math.min.apply(null, c) / 255, l = (mx + mn) / 2;
		return mx === mn ? 0 : (mx - mn) / (1 - Math.abs(2 * l - 1));
	}
	function darken(c, t) { return c.map(function (v) { return v * (1 - t); }); }

	function analyzeColors(doc, win) {
		var area = {}, buttons = {};
		var vw = win.innerWidth;
		var els = doc.body.querySelectorAll('*');
		for (var i = 0; i < els.length && i < 6000; i++) {
			var e = els[i], cs = win.getComputedStyle(e);
			if (cs.display === 'none' || cs.visibility === 'hidden' || +cs.opacity === 0) continue;
			var r = e.getBoundingClientRect();
			var w = Math.min(r.width, vw), h = Math.min(r.height, 3000);
			if (w < 2 || h < 2) continue;
			var bg = parse(cs.backgroundColor);
			if (bg) { var k = hex(bg); area[k] = (area[k] || 0) + w * h; }
			if (e.matches('.elementor-button, .e-button-base, .wp-block-button__link, .wp-element-button, button, .button, .btn, input[type=submit], a[class*="btn"], a[class*="button"]')) {
				[bg, parse(cs.borderTopColor)].forEach(function (c) {
					if (c && sat(c) > 0.3 && lum(c) > 0.05 && lum(c) < 0.9) { var k2 = hex(c); buttons[k2] = (buttons[k2] || 0) + 1; }
				});
			}
		}
		var list = Object.keys(area).map(function (k) {
			var c = k.match(/\w\w/g).map(function (x) { return parseInt(x, 16); });
			return { hex: k, c: c, area: area[k], l: lum(c), s: sat(c) };
		});
		// fondo: il colore scuro (non nero puro di testo) con più superficie
		var dark = list.filter(function (x) { return x.l < 0.2; }).sort(function (a, b) { return b.area - a.area; })[0];
		// accento: il colore dei pulsanti più ricorrente, altrimenti il colore saturo con più superficie
		var acc = Object.keys(buttons).filter(function (k) { return !dark || k !== dark.hex; })
			.sort(function (a, b) { return buttons[b] - buttons[a]; })[0];
		if (!acc) {
			var s = list.filter(function (x) { return x.s > 0.35 && x.l > 0.08 && x.l < 0.85 && (!dark || x.hex !== dark.hex); })
				.sort(function (a, b) { return b.area - a.area; })[0];
			acc = s && s.hex;
		}
		if (!dark) {
			// nessuna sezione scura: scurisco il colore di accento
			var base = acc ? acc.match(/\w\w/g).map(function (x) { return parseInt(x, 16); }) : [29, 35, 39];
			dark = { hex: hex(darken(base, 0.7)) };
		}
		return { bg: dark.hex, accent: acc || '#2271b1' };
	}

	/* ---------- logo ---------- */
	function findLogo(doc, home) {
		var header = doc.querySelector('.elementor-location-header, header, [role="banner"], #masthead, .site-header') || doc.body;
		var sel = ['.custom-logo', '.elementor-widget-theme-site-logo img', '.site-logo img', '[class*="logo"] img', 'img[class*="logo"]',
			'img[alt*="logo" i]', 'a[href="' + home + '"] img', 'a[href="' + home + '"] svg', 'img', 'svg'];
		for (var i = 0; i < sel.length; i++) {
			var list = header.querySelectorAll(sel[i]);
			for (var j = 0; j < list.length; j++) {
				var el = list[j], r = el.getBoundingClientRect();
				if (r.width >= 24 && r.height >= 16 && !el.closest('nav, .menu, [class*="toggle"], [class*="hamburger"]')) return el;
			}
		}
		return null;
	}
	function svgData(svg, win) {
		var c = svg.cloneNode(true);
		if (!c.getAttribute('xmlns')) c.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
		// i colori che arrivano dal CSS della pagina li scrivo nell'SVG, altrimenti fuori dal sito sparirebbero
		var src = svg.querySelectorAll('*'), dst = c.querySelectorAll('*');
		for (var i = 0; i < src.length; i++) {
			var cs = win.getComputedStyle(src[i]);
			if (cs.fill && cs.fill !== 'none') dst[i].setAttribute('fill', cs.fill);
			if (cs.stroke && cs.stroke !== 'none') dst[i].setAttribute('stroke', cs.stroke);
		}
		var xml = new XMLSerializer().serializeToString(c);
		return 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(xml)));
	}
	/** Luminosità media dei pixel visibili del logo (0..1). */
	function tone(url) {
		return new Promise(function (res) {
			var img = new Image();
			img.crossOrigin = 'anonymous';
			img.onload = function () {
				try {
					var W = 120, H = Math.max(1, Math.round(W * (img.naturalHeight || 1) / (img.naturalWidth || 1)));
					var cv = document.createElement('canvas'); cv.width = W; cv.height = H;
					var cx = cv.getContext('2d'); cx.drawImage(img, 0, 0, W, H);
					var d = cx.getImageData(0, 0, W, H).data, sum = 0, n = 0;
					for (var i = 0; i < d.length; i += 4) if (d[i + 3] > 128) { sum += lum([d[i], d[i + 1], d[i + 2]]); n++; }
					res(n ? sum / n : null);
				} catch (e) { res(null); }
			};
			img.onerror = function () { res(null); };
			img.src = url;
		});
	}

	/* ---------- font ---------- */
	function findFont(doc, win) {
		var h = doc.querySelector('h1, h2, .elementor-heading-title') || doc.body;
		var fam = win.getComputedStyle(h).fontFamily.split(',')[0].replace(/["']/g, '').trim();
		var generic = /^(serif|sans-serif|system-ui|-apple-system|blinkmacsystemfont|arial|helvetica|helvetica neue|georgia|times new roman|verdana|segoe ui)$/i;
		if (!fam || generic.test(fam)) return { font: '', css: [] };
		var css = [];
		for (var i = 0; i < doc.styleSheets.length; i++) {
			var ss = doc.styleSheets[i], rules;
			try { rules = ss.cssRules; } catch (e) { continue; }
			for (var j = 0; j < rules.length; j++) {
				var r = rules[j];
				if (r.type === 5 && r.style.getPropertyValue('font-family').replace(/["']/g, '').trim().toLowerCase() === fam.toLowerCase()) {
					if (ss.href && css.indexOf(ss.href) < 0) css.push(ss.href);
					break;
				}
			}
		}
		// Google Fonts caricati dal sito
		doc.querySelectorAll('link[href*="fonts.googleapis.com"]').forEach(function (l) {
			if (l.href.indexOf(encodeURIComponent(fam).replace(/%20/g, '+')) > -1 || l.href.indexOf(fam.replace(/ /g, '+')) > -1) css.push(l.href);
		});
		return { font: '"' + fam + '"', css: css.slice(0, 4) };
	}

	/* ---------- analisi ---------- */
	function analyze() {
		btn.disabled = true;
		status.textContent = 'Analisi della homepage in corso…';
		var f = document.createElement('iframe');
		f.setAttribute('aria-hidden', 'true');
		f.style.cssText = 'position:fixed;left:-10000px;top:0;width:1440px;height:900px;border:0';
		document.body.appendChild(f);
		var done = false;
		var fail = setTimeout(function () { if (!done) finish(null, 'La homepage non risponde: riprova o imposta i valori a mano.'); }, 25000);
		f.onload = function () { setTimeout(run, 1500); };   // tempo per font, immagini e script del tema
		f.src = LU3GLogin.home + (LU3GLogin.home.indexOf('?') > -1 ? '&' : '?') + 'lu3g-analisi=' + Date.now();

		function run() {
			var doc, win;
			try { doc = f.contentDocument; win = f.contentWindow; if (!doc || !doc.body) throw 0; }
			catch (e) { return finish(null, 'Non riesco a leggere la homepage (dominio diverso o blocco di sicurezza).'); }
			var col = analyzeColors(doc, win);
			var fnt = findFont(doc, win);
			var el = findLogo(doc, LU3GLogin.home);
			var logo = '';
			if (el) logo = el.tagName.toLowerCase() === 'svg' ? svgData(el, win) : (el.currentSrc || el.src);
			(logo ? tone(logo) : Promise.resolve(null)).then(function (t) {
				finish({ logo: logo, tone: t === null ? null : (t > 0.35 ? 'light' : 'dark'), bg: col.bg, accent: col.accent, font: fnt.font, css: fnt.css });
			});
		}
		function finish(res, err) {
			done = true; clearTimeout(fail); f.remove(); btn.disabled = false;
			if (!res) { status.textContent = err; return; }
			if (res.logo) $('lu3g-logo').value = res.logo;
			if (res.tone) document.querySelector('input[name="' + LU3GLogin.opt + '[logo_tone]"][value="' + res.tone + '"]').checked = true;
			$('lu3g-bg').value = res.bg;
			$('lu3g-accent').value = res.accent;
			$('lu3g-font').value = res.font;
			$('lu3g-font-css').value = res.css.join('\n');
			$('lu3g-analyzed').value = new Date().toISOString().slice(0, 16).replace('T', ' ');
			status.textContent = 'Fatto: controlla i valori qui sotto e premi Salva.' + (res.logo ? '' : ' Logo non trovato: sceglilo dalla libreria.');
			// aggiorna l'anteprima della pagina (assets/admin-ui.js ascolta "change" sul modulo)
			var form = document.getElementById('lu3g-form');
			if (form) form.dispatchEvent(new Event('change', { bubbles: true }));
		}
	}
	btn.addEventListener('click', analyze);

	/* ---------- scelta del logo dalla libreria ---------- */
	var media = $('lu3g-media'), frame;
	if (media && window.wp && wp.media) media.addEventListener('click', function () {
		frame = frame || wp.media({ title: 'Logo del sito', library: { type: 'image' }, multiple: false });
		frame.off('select').on('select', function () {
			var a = frame.state().get('selection').first().toJSON();
			$('lu3g-logo').value = a.url;
			$('lu3g-logo').dispatchEvent(new Event('input', { bubbles: true }));
		});
		frame.open();
	});
})();
