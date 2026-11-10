/* SHAI FX: staggered reveal, custom selects, count-up numbers, password meter, key sounds. */
(function () {
    'use strict';

    var doc = document, root = doc.documentElement;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function $all(sel, ctx) { return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel)); }

    // staggered reveal
    function reveal() {
        var targets = $all('#content > *, #content .row > [class*="col"], #content .card, #content .login-container, #content .shai-auth-card');
        var seen = [], items = targets.filter(function (el) {
            if (el.closest('.modal, .swal2-popup') || el.id === 'shaiSplash' || seen.indexOf(el) > -1) { return false; }
            seen.push(el);
            return !targets.some(function (o) { return o !== el && o.contains(el) && o.classList.contains('card'); });
        }).slice(0, 40);
        var vh = window.innerHeight, order = 0;
        var io = ('IntersectionObserver' in window) ? new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) { en.target.classList.add('fx-in'); io.unobserve(en.target); }
            });
        }, { threshold: .08 }) : null;
        items.forEach(function (el) {
            var top = el.getBoundingClientRect().top;
            el.style.setProperty('--i', Math.min(order++, 10));
            if (top < vh || !io) { el.classList.add('fx-item'); }
            else { el.classList.add('fx-pre'); io.observe(el); }
        });
    }

    // count-up numbers
    function counters() {
        if (reduce) { return; }
        $all('.stats-value, .counter, [data-count]').forEach(function (el) {
            var txt = el.textContent.trim();
            if (!/^\d{1,9}$/.test(txt)) { return; }
            var to = parseInt(txt, 10), t0 = null, dur = 900;
            if (to < 2) { return; }
            el.textContent = '0';
            function step(ts) {
                if (!t0) { t0 = ts; }
                var p = Math.min((ts - t0) / dur, 1), e = 1 - Math.pow(1 - p, 4);
                el.textContent = Math.round(to * e);
                if (p < 1) { requestAnimationFrame(step); } else { el.textContent = String(to); }
            }
            setTimeout(function () { requestAnimationFrame(step); }, 350);
        });
    }

    // custom select dropdowns
    var openSel = null;
    function closeSel(w) {
        if (!w) { return; }
        w.classList.remove('is-open');
        var t = w.querySelector('.fx-trigger');
        if (t) { t.setAttribute('aria-expanded', 'false'); }
        if (openSel === w) { openSel = null; }
    }

    function enhance(sel) {
        if (sel.dataset.fx === '1' || sel.multiple || sel.size > 1 || sel.classList.contains('no-fx')) { return; }
        if (sel.matches('.swal2-select') || sel.closest('.swal2-container, .swal2-popup, .swal2-toast, .dataTables_wrapper, .dataTables_length')) { return; }
        if (sel.hidden || sel.hasAttribute('hidden') || sel.getAttribute('aria-hidden') === 'true') { return; }
        sel.dataset.fx = '1';

        var wrap = doc.createElement('div');
        wrap.className = 'fx-select';
        sel.parentNode.insertBefore(wrap, sel);
        wrap.appendChild(sel);
        sel.tabIndex = -1;

        var trig = doc.createElement('button');
        trig.type = 'button';
        trig.className = 'fx-trigger';
        trig.setAttribute('aria-haspopup', 'listbox');
        trig.setAttribute('aria-expanded', 'false');
        trig.innerHTML = '<span class="fx-label"></span><i class="fx-chevron" aria-hidden="true"></i>';
        var menu = doc.createElement('ul');
        menu.className = 'fx-menu';
        menu.setAttribute('role', 'listbox');
        wrap.appendChild(trig);
        wrap.appendChild(menu);

        var label = trig.firstChild, active = -1, typed = '', typedAt = 0;

        function opts() { return $all('.fx-opt', menu); }

        function build() {
            menu.innerHTML = '';
            Array.prototype.forEach.call(sel.options, function (o, i) {
                var li = doc.createElement('li');
                li.className = 'fx-opt' + (o.selected ? ' is-selected' : '') + (o.value === '' ? ' is-placeholder' : '');
                li.setAttribute('role', 'option');
                li.setAttribute('data-i', i);
                li.style.setProperty('--k', Math.min(i, 12));
                if (o.disabled) { li.setAttribute('aria-disabled', 'true'); }
                li.setAttribute('aria-selected', o.selected ? 'true' : 'false');
                var sp = doc.createElement('span');
                sp.textContent = o.text;
                li.appendChild(sp);
                menu.appendChild(li);
            });
            sync();
        }

        function sync() {
            var o = sel.options[sel.selectedIndex];
            label.textContent = o ? o.text : '';
            label.classList.toggle('is-empty', !o || o.value === '');
            trig.disabled = sel.disabled;
            opts().forEach(function (li, i) {
                var on = sel.options[i] && sel.options[i].selected;
                li.classList.toggle('is-selected', !!on);
                li.setAttribute('aria-selected', on ? 'true' : 'false');
            });
        }

        function setActive(i, scroll) {
            var list = opts();
            if (!list.length) { return; }
            i = Math.max(0, Math.min(list.length - 1, i));
            list.forEach(function (li) { li.classList.remove('is-active'); });
            list[i].classList.add('is-active');
            active = i;
            if (scroll !== false && list[i].scrollIntoView) { list[i].scrollIntoView({ block: 'nearest' }); }
        }

        function nextEnabled(from, dir) {
            var n = sel.options.length, i = from;
            for (var c = 0; c < n; c++) {
                i += dir;
                if (i < 0 || i >= n) { return from; }
                if (!sel.options[i].disabled) { return i; }
            }
            return from;
        }

        function open() {
            if (sel.disabled) { return; }
            if (openSel && openSel !== wrap) { closeSel(openSel); }
            build();
            var r = trig.getBoundingClientRect();
            wrap.classList.toggle('is-up', window.innerHeight - r.bottom < 300 && r.top > 300);
            wrap.classList.add('is-open');
            Snd.open();
            trig.setAttribute('aria-expanded', 'true');
            openSel = wrap;
            setActive(sel.selectedIndex < 0 ? 0 : sel.selectedIndex);
        }

        function choose(i) {
            var o = sel.options[i];
            if (!o || o.disabled) { return; }
            var changed = sel.selectedIndex !== i;
            sel.selectedIndex = i;
            sync();
            Snd.choose();
            closeSel(wrap);
            trig.focus({ preventScroll: true });
            if (changed) {
                sel.dispatchEvent(new Event('input', { bubbles: true }));
                sel.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        trig.addEventListener('click', function () { wrap.classList.contains('is-open') ? closeSel(wrap) : open(); });
        menu.addEventListener('click', function (e) {
            var li = e.target.closest('.fx-opt');
            if (li) { choose(parseInt(li.getAttribute('data-i'), 10)); }
        });
        menu.addEventListener('pointermove', function (e) {
            var li = e.target.closest('.fx-opt');
            if (li && !li.classList.contains('is-active')) { setActive(opts().indexOf(li), false); }
        });
        trig.addEventListener('keydown', function (e) {
            var isOpen = wrap.classList.contains('is-open'), k = e.key;
            if (k === 'ArrowDown' || k === 'ArrowUp') {
                e.preventDefault();
                if (!isOpen) { open(); return; }
                setActive(nextEnabled(active, k === 'ArrowDown' ? 1 : -1));
            } else if (k === 'Home' || k === 'End') {
                if (isOpen) { e.preventDefault(); setActive(k === 'Home' ? 0 : sel.options.length - 1); }
            } else if (k === 'Enter' || k === ' ') {
                if (isOpen && !(k === ' ' && typed)) { e.preventDefault(); choose(active); }
                else if (!isOpen) { e.preventDefault(); open(); }
            } else if (k === 'Escape' || k === 'Tab') {
                if (isOpen) { closeSel(wrap); }
            } else if (k.length === 1 && !e.ctrlKey && !e.metaKey) {
                var now = Date.now();
                typed = (now - typedAt > 700 ? '' : typed) + k.toLowerCase();
                typedAt = now;
                for (var i = 0; i < sel.options.length; i++) {
                    if (!sel.options[i].disabled && sel.options[i].text.toLowerCase().indexOf(typed) === 0) {
                        if (isOpen) { setActive(i); } else { choose(i); }
                        break;
                    }
                }
            }
        });

        sel.addEventListener('change', sync);
        if (sel.form) { sel.form.addEventListener('reset', function () { setTimeout(sync, 0); }); }
        new MutationObserver(build).observe(sel, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled', 'selected'] });
        sync();
        build();
    }

    function selects() {
        $all('select').forEach(enhance);
        // selects added later (modals, ajax)
        new MutationObserver(function (list) {
            list.forEach(function (m) {
                Array.prototype.forEach.call(m.addedNodes, function (n) {
                    if (n.nodeType !== 1) { return; }
                    if (n.tagName === 'SELECT') { enhance(n); }
                    else if (n.querySelectorAll) { $all('select', n).forEach(enhance); }
                });
            });
        }).observe(doc.body, { childList: true, subtree: true });
        doc.addEventListener('pointerdown', function (e) {
            if (openSel && !openSel.contains(e.target)) { closeSel(openSel); }
        });
        window.addEventListener('resize', function () { closeSel(openSel); });
    }

    // dropdown item stagger
    function dropdowns() {
        $all('.dropdown-menu').forEach(function (m) {
            $all('.dropdown-item', m).forEach(function (it, i) { it.style.setProperty('--k', Math.min(i, 10)); });
        });
    }

    // password strength meter and show/hide
    function meter() {
        var m = doc.getElementById('pwMeter'), p = doc.getElementById('password');
        if (!m || !p) { return; }
        p.addEventListener('input', function () {
            var v = p.value, s = 0;
            if (v.length >= 10) { s++; }
            if (/[a-z]/.test(v) && /[A-Z]/.test(v)) { s++; }
            if (/\d/.test(v)) { s++; }
            if (/[^A-Za-z0-9]/.test(v) || v.length >= 16) { s++; }
            m.setAttribute('data-s', v ? s : 0);
        });
    }


    // key click sounds
    var Snd = (function () {
        var ctx = null, master = null, bank = {}, recent = {}, sequence = 0;
        var KEY = 'shai_sound', enabled = true;
        try { enabled = localStorage.getItem(KEY) !== '0'; } catch (e) {}

        var profiles = {
            down:   [.105, 175, .86, .58, .20, .019, .23],
            up:     [.070, 255, .38, .42, .24, .012, .00],
            key:    [.092, 208, .69, .65, .20, .016, .17],
            space:  [.126, 138, 1.00, .71, .14, .029, .29],
            back:   [.083, 183, .56, .56, .24, .014, .11],
            toggle: [.092, 222, .60, .54, .21, .016, .12],
            slide:  [.105, 158, .51, .71, .13, .028, .00],
            choose: [.092, 204, .61, .52, .17, .018, .13],
            firm:   [.114, 157, .82, .62, .14, .024, .22],
            soft:   [.072, 269, .37, .46, .21, .014, .06],
            heavy:  [.145, 128, 1.00, .61, .12, .031, .25],
            hover:  [.044, 326, .24, .28, .12, .009, .00]
        };

        function sample(spec, seed, variation) {
            var rate = ctx.sampleRate, length = Math.ceil(spec[0] * rate);
            var buffer = ctx.createBuffer(1, length, rate);
            var output = buffer.getChannelData(0);
            var pitch = spec[1] * (1 + (variation - 1) * .043);
            var body = spec[2], surface = spec[3], edge = spec[4], decay = spec[5], rebound = spec[6];
            var randState = seed | 0, low = 0, mid = 0, high = 0, peak = .00001;
            for (var i = 0; i < length; i++) {
                randState ^= randState << 13;
                randState ^= randState >>> 17;
                randState ^= randState << 5;
                var white = ((randState >>> 0) / 2147483648) - 1;
                high += .29 * (white - high);
                mid += .12 * (white - mid);
                low += .027 * (white - low);
                var t = i / rate;
                var onset = Math.min(1, t * 2000);
                var knock = Math.exp(-t / .0054);
                var cushion = Math.exp(-t / decay);
                var smooth = (mid - low) * cushion * surface;
                var felt = (low * .85 + mid * .35) * Math.exp(-t / (decay * 1.6)) * body;
                var top = (high - mid) * knock * edge;
                var resonance = (Math.sin(6.28318530718 * pitch * t) * .15 +
                    Math.sin(6.28318530718 * pitch * 2.37 * t + .7) * .066) *
                    Math.exp(-t / (decay * .77)) * body;
                var bottom = 0;
                if (rebound && t > .0115) {
                    var u = t - .0115;
                    bottom = ((mid - low) * .32 + Math.sin(6.28318530718 * pitch * 1.35 * u) * .075) *
                        Math.exp(-u / .009) * rebound;
                }
                var v = onset * (smooth * .66 + felt * .80 + top * .50 + resonance + bottom);
                output[i] = v;
                if (Math.abs(v) > peak) { peak = Math.abs(v); }
            }
            var level = .70 / peak;
            for (var j = 0; j < length; j++) { output[j] *= level; }
            return buffer;
        }

        function boot() {
            if (ctx) {
                if (ctx.state === 'suspended') { ctx.resume().catch(function () {}); }
                return !!ctx;
            }
            var AC = window.AudioContext || window.webkitAudioContext;
            if (!AC) { return false; }
            try {
                ctx = new AC();
                var compressor = ctx.createDynamicsCompressor();
                compressor.threshold.value = -14;
                compressor.knee.value = 7;
                compressor.ratio.value = 3;
                compressor.attack.value = .003;
                compressor.release.value = .10;
                master = ctx.createGain();
                master.gain.value = .50;
                master.connect(compressor);
                compressor.connect(ctx.destination);
                var seed = 71237;
                Object.keys(profiles).forEach(function (name) {
                    var count = name === 'key' ? 4 : name === 'down' || name === 'up' ? 3 : 1;
                    bank[name] = [];
                    for (var i = 0; i < count; i++) {
                        bank[name].push(sample(profiles[name], seed += 971, i));
                    }
                });
            } catch (e) { ctx = null; bank = {}; return false; }
            if (ctx.state === 'suspended') { ctx.resume().catch(function () {}); }
            return true;
        }

        function ready(name, gap) {
            if (!enabled || doc.hidden) { return false; }
            var now = performance.now();
            if (recent[name] && now - recent[name] < gap) { return false; }
            recent[name] = now;
            return boot();
        }

        function hit(name, volume, offset) {
            if (!ctx || !bank[name]) { return; }
            var variants = bank[name];
            var buffer = variants[(sequence++ + Math.floor(Math.random() * variants.length)) % variants.length];
            var source = ctx.createBufferSource();
            var gain = ctx.createGain();
            source.buffer = buffer;
            gain.gain.value = volume;
            source.connect(gain);
            gain.connect(master);
            source.onended = function () { source.disconnect(); gain.disconnect(); };
            source.start(ctx.currentTime + (offset || 0));
        }

        var api = {
            get on() { return enabled; },
            set: function (value) {
                enabled = !!value;
                try { localStorage.setItem(KEY, enabled ? '1' : '0'); } catch (e) {}
                if (enabled) { api.toggle(true); }
            },
            unlock: function () { if (enabled) { boot(); } },
            down: function () { if (ready('down', 26)) { hit('down', .45); } },
            up: function () { if (ready('up', 26)) { hit('up', .22); } },
            hover: function () { if (ready('hover', 230)) { hit('hover', .075); } },
            key: function (code) {
                if (!ready('key', 24)) { return; }
                var large = code === 'Space' || code === 'Enter';
                var back = code === 'Backspace' || code === 'Delete';
                hit(large ? 'space' : back ? 'back' : 'key', large ? .44 : .30);
            },
            toggle: function (on) { if (ready('toggle', 55)) { hit('toggle', on ? .35 : .29); } },
            open: function () { if (ready('open', 100)) { hit('slide', .30); hit('soft', .15, .043); } },
            close: function () { if (ready('close', 100)) { hit('soft', .25); } },
            choose: function () { if (ready('choose', 85)) { hit('choose', .35); } },
            submit: function () { if (ready('submit', 200)) { hit('firm', .42); hit('up', .16, .078); } },
            nav: function () { if (ready('nav', 250)) { hit('slide', .24); } },
            success: function () { if (ready('toast', 350)) { hit('soft', .29); hit('choose', .35, .088); } },
            error: function () { if (ready('toast', 350)) { hit('heavy', .49); hit('heavy', .32, .128); } },
            warn: function () { if (ready('toast', 350)) { hit('firm', .35); hit('soft', .21, .12); } },
            pop: function () { if (ready('pop', 200)) { hit('heavy', .46); } },
            close2: function () { if (ready('close2', 110)) { hit('up', .22); } }
        };
        return api;
    })();
    window.shaiSound = Snd;

    function sounds() {
        var press = '.btn, .catalog-btn, .mk-btn, .btn-copy, .btn-action, .shai-tg, .shai-switch-item, .nav-link, .navbar-brand, .dropdown-item, .page-link, .paginate_button, .fx-trigger, .close-btn, .swal2-styled, .shai-pw-toggle, button[type="submit"]';
        var typeSel = 'input:not([type=checkbox]):not([type=radio]):not([type=button]):not([type=submit]):not([type=file]):not([type=range]):not([type=color]):not([readonly]), textarea';

        // browsers only allow audio after a touch or click; the first one starts the engine
        ['pointerdown', 'keydown', 'touchstart'].forEach(function (ev) { doc.addEventListener(ev, Snd.unlock, { once: true, passive: true, capture: true }); });

        doc.addEventListener('pointerdown', function (e) {
            if (e.target.closest && e.target.closest('.fx-sound')) { return; }
            var el = e.target.closest && e.target.closest(press);
            if (el && !el.disabled) { Snd.down(); }
        }, { passive: true, capture: true });
        doc.addEventListener('pointerup', function (e) {
            var el = e.target.closest && e.target.closest(press);
            if (el && !el.disabled && !e.target.closest('.fx-sound')) { Snd.up(); }
        }, { passive: true, capture: true });

        if (window.matchMedia && window.matchMedia('(hover: hover)').matches) {
            doc.addEventListener('pointerover', function (e) {
                var el = e.target.closest && e.target.closest('.btn, .nav-link, .dropdown-item, .shai-switch-item');
                if (el && !el.contains(e.relatedTarget) && !el.disabled) { Snd.hover(); }
            }, { passive: true });
        }

        var physicalKeyAt = 0;
        doc.addEventListener('keydown', function (e) {
            if (e.ctrlKey || e.metaKey || e.altKey || e.key === 'Shift' || e.key === 'Control' || e.key === 'Alt' || e.key === 'Meta' || e.key === 'CapsLock' || e.key === 'Tab') { return; }
            var t = e.target;
            if (t && t.matches && t.matches(typeSel)) {
                physicalKeyAt = performance.now();
                Snd.key(e.code === 'Space' ? 'Space' : e.key === 'Enter' ? 'Enter' : e.key === 'Backspace' ? 'Backspace' : e.key === 'Delete' ? 'Delete' : 'k');
            }
        }, true);
        doc.addEventListener('beforeinput', function (e) {
            var t = e.target, inputType = e.inputType || '';
            if (!t || !t.matches || !t.matches(typeSel) || e.isComposing || performance.now() - physicalKeyAt < 70) { return; }
            if (inputType === 'insertText' || inputType === 'insertCompositionText' || inputType === 'insertLineBreak' || inputType === 'deleteContentBackward' || inputType === 'deleteContentForward') {
                Snd.key(inputType === 'insertLineBreak' ? 'Enter' : inputType.indexOf('delete') === 0 ? 'Backspace' : e.data === ' ' ? 'Space' : 'k');
            }
        }, true);

        doc.addEventListener('change', function (e) {
            var t = e.target;
            if (t && t.matches && t.matches('input[type=checkbox], input[type=radio]')) { Snd.toggle(t.checked); }
        }, true);
        doc.addEventListener('submit', function () { Snd.submit(); }, true);

        doc.addEventListener('click', function (e) {
            var a = e.target.closest && e.target.closest('a[href]');
            if (!a || a.target === '_blank' || e.metaKey || e.ctrlKey) { return; }
            var h = a.getAttribute('href') || '';
            if (!h || h.charAt(0) === '#' || h.indexOf('javascript:') === 0 || (a.host && a.host !== location.host)) { return; }
            if (a.hasAttribute('data-bs-toggle')) { return; }
            Snd.nav();
        }, true);

        // toasts, dialogs and dropdowns added after page load
        new MutationObserver(function (list) {
            list.forEach(function (m) {
                Array.prototype.forEach.call(m.addedNodes, function (n) {
                    if (n.nodeType !== 1) { return; }
                    if (n.classList.contains('custom-alert')) { n.classList.contains('danger') ? Snd.error() : n.classList.contains('warning') ? Snd.warn() : Snd.success(); }
                    else if (n.classList.contains('swal2-container') || n.classList.contains('key-popup-modal')) { Snd.pop(); }
                });
            });
        }).observe(doc.body, { childList: true, subtree: true });
        doc.addEventListener('shown.bs.dropdown', function () { Snd.open(); });
        doc.addEventListener('hidden.bs.dropdown', function () { Snd.close(); });
        doc.addEventListener('shown.bs.modal', function () { Snd.pop(); });

        // sound on/off button
        var b = doc.createElement('button');
        b.type = 'button'; b.className = 'fx-sound' + (Snd.on ? '' : ' is-off');
        b.setAttribute('aria-label', 'Sound effects'); b.setAttribute('aria-pressed', Snd.on ? 'true' : 'false'); b.title = 'Sound effects';
        function paint() { b.innerHTML = '<i class="bi ' + (Snd.on ? 'bi-volume-up-fill' : 'bi-volume-mute-fill') + '" aria-hidden="true"></i>'; b.classList.toggle('is-off', !Snd.on); b.setAttribute('aria-pressed', Snd.on ? 'true' : 'false'); }
        b.addEventListener('click', function () { Snd.set(!Snd.on); paint(); });
        paint();
        doc.body.appendChild(b);
    }

    // toast close button and countdown
    function toasts() {
        doc.addEventListener('click', function (e) {
            var btn = e.target.closest && e.target.closest('.custom-alert .close-btn');
            if (!btn) { return; }
            var box = btn.closest('.custom-alert');
            if (!box) { return; }
            e.preventDefault();
            box.classList.add('fade-out');
            Snd.close2();
            setTimeout(function () { if (box.parentNode) { box.parentNode.removeChild(box); } }, 260);
        }, true);
        // every inline toast gets a countdown bar and closes when it ends
        $all('.custom-alert').forEach(function (box) {
            var bar = doc.createElement('span');
            bar.className = 'toast-bar';
            bar.innerHTML = '<i></i>';
            box.appendChild(bar);
            var gone = function () {
                if (!box.parentNode) { return; }
                box.classList.add('fade-out');
                setTimeout(function () { if (box.parentNode) { box.parentNode.removeChild(box); } }, 260);
            };
            bar.firstChild.addEventListener('animationend', gone);
            setTimeout(gone, 6500); // fallback when animations are off
        });
    }

    // login/register switch: cover the page and keep the progress bar
    function authSwitch() {
        doc.addEventListener('click', function (e) {
            var a = e.target.closest && e.target.closest('a.shai-switch-item, a.shai-alt-link');
            if (!a || a.classList.contains('is-active') || e.metaKey || e.ctrlKey || e.shiftKey || a.target === '_blank') { return; }
            if (doc.getElementById('fxCover')) { return; }
            var ov = doc.createElement('div');
            ov.id = 'fxCover';
            ov.className = 'shai-splash fx-instant';
            ov.innerHTML = '<span class="shai-wordmark">SHAI Panel</span><div class="shai-bar"><div></div></div>';
            doc.body.appendChild(ov);
            var bar = doc.getElementById('shai-progress');
            if (bar) { bar.className = 'on'; }
            setTimeout(function () { if (ov.parentNode) { ov.parentNode.removeChild(ov); } if (bar) { bar.className = ''; } }, 8000); // don't trap the page if the next one fails to load
        }, true);
        window.addEventListener('pageshow', function () { var c = doc.getElementById('fxCover'); if (c && c.parentNode) { c.parentNode.removeChild(c); } });
    }

    // page leave
    function leave() {
        doc.addEventListener('click', function (e) {
            var a = e.target.closest && e.target.closest('a');
            if (!a || e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || a.target === '_blank') { return; }
            var h = a.getAttribute('href') || '';
            if (!h || h.charAt(0) === '#' || h.indexOf('javascript:') === 0 || (a.host && a.host !== location.host)) { return; }
            if (a.hasAttribute('download') || a.hasAttribute('data-bs-toggle') || a.classList.contains('shai-switch-item') || a.classList.contains('shai-alt-link')) { return; }
            root.classList.add('fx-leaving');
        });
        window.addEventListener('pageshow', function () { root.classList.remove('fx-leaving'); });
    }

    function init() {
        try { selects(); dropdowns(); meter(); leave(); toasts(); authSwitch(); sounds(); } catch (e) { /* ignore */ }
        // wait one frame so layout is final before measuring cards
        requestAnimationFrame(function () {
            try { reveal(); counters(); } catch (e) { /* ignore */ }
        });
    }

    if (doc.readyState === 'loading') { doc.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
