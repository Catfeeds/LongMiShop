(function() {
    var h = window,
    m = document;
    var c = m.documentElement,
    a = h.navigator.userAgent,
    k = c.querySelector('meta[name="viewport"]'),
    n = 0,
    d = 0,
    o = 100,
    f = {},
    e;
    if (!n && !d) {
        var j = a.match(/android/gi),
        i = a.match(/iphone/gi),
        l = h.devicePixelRatio;
        if (i) {
            if (l >= 3 && (!n || n >= 3)) {
                n = 3;

            } else {
                if (l >= 2 && (!n || n >= 2)) {
                    n = 2;

                } else {
                    n = 1;

                }

            }

        } else {
            n = 1;

        }
        d = 1 / n;

    }
    c.setAttribute("data-dpr", n);
    if (k) {
        var g = k.getAttribute("content").match(/initial\-scale=([\d\.]+)/);
        if (g) {
            d = parseFloat(g[1]);
            n = parseInt(1 / d);

        }

    }
    if (!k) {
        k = m.createElement("meta");
        k.setAttribute("name", "viewport");
        k.setAttribute("content", "initial-scale=" + d + ", maximum-scale=" + d + ", minimum-scale=" + d + ", user-scalable=no");
        if (c.firstElementChild) {
            c.firstElementChild.appendChild(k);

        } else {
            var b = m.createElement("div");
            b.appendChild(k);
            m.write(b.innerHTML);

        }

    }
    refreshRem = function() {
        var p = c.clientWidth;
        if (!p) {
            return;

        }
        f.fontSize = 100 * (p / 320);
        var z = f.fontSize;
        z = z >= 240 ? 240: z;
        c.style.fontSize = z + "px";

    };
    h.addEventListener("resize", 
    function() {
        clearTimeout(e);
        e = setTimeout(refreshRem, 300);

    },
    false);
    h.addEventListener("pageshow", 
    function(p) {
        if (p.persisted) {
            clearTimeout(e);
            e = setTimeout(refreshRem, 300);

        }

    },
    false);
    refreshRem();
    h.QNR = h.QNR || {};
    QNR.Flexible = f;

})();