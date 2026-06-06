var swPath = self.location.pathname;
var BASE = swPath.substring(0, swPath.lastIndexOf("/") + 1);

function p(url) {
  return BASE + url.replace(/^\//, "");
}

var CACHE = "buddhaword-v8";
var STATIC_CACHE = "buddhaword-static-v8";
var ASSETS_CACHE = "buddhaword-assets-v8";
var API_CACHE = "buddhaword-api-data";
var OLD_API_PATTERN = "buddhaword-api-v";

var PRECACHE_STATIC = [
  "/",
  "/favorites",
  "/book",
  "/video",
  "/calendar",
  "/about",
  "/css/style.css",
  "/css/sweetalert2.min.css",
  "/manifest.php",
  "/offline.html",
  "/buddhaword.png",
  "/images/Icon-192.png",
  "/images/Icon-512.png",
  "/icons/Icon-512.png",
  "/assets/fonts/PhetsarathOT.ttf",
  "/assets/fonts/PhetsarathOT.woff2",
  "/assets/fonts/NotoSerifLao.ttf",
  "/assets/fonts/NotoSerifLao.woff2",
  "/assets/images/logo.png",
  "/assets/images/logo_shared.png",
  "/assets/icons/sutra.png",
  "/assets/icons/favorites.png",
  "/assets/icons/book.png",
  "/assets/icons/vdo.png",
  "/assets/icons/calendar.png",
  "/assets/icons/about.png",
  "/images/sutra/ກຳ.jpg",
  "/sutra/ກຳ",
  "/images/sutra/ຂັນ 5.jpg",
  "/sutra/ຂັນ 5",
  "/images/sutra/ຄາຣະວາດຊັ້ນເລີດ.jpg",
  "/sutra/ຄາຣະວາດຊັ້ນເລີດ",
  "/images/sutra/ຄູ່ສົມຣົດ.jpg",
  "/sutra/ຄູ່ສົມຣົດ",
  "/images/sutra/ຈິດ ມະໂນ ວິນຍານ.jpg",
  "/sutra/ຈິດ ມະໂນ ວິນຍານ",
  "/images/sutra/ຕາມຮອຍທຳ.jpg",
  "/sutra/ຕາມຮອຍທຳ",
  "/images/sutra/ທາງສາຍກາງ.jpg",
  "/sutra/ທາງສາຍກາງ",
  "/images/sutra/ທານ.jpg",
  "/sutra/ທານ",
  "/images/sutra/ທຳມະຊາດ.jpg",
  "/sutra/ທຳມະຊາດ",
  "/images/sutra/ທໍາໃນທີສຸດ.jpg",
  "/sutra/ທໍາໃນທີສຸດ",
  "/images/sutra/ທໍາໃນທ່າມກາງ.jpg",
  "/sutra/ທໍາໃນທ່າມກາງ",
  "/images/sutra/ທໍາໃນເບື້ອງຕົ້ນ.jpg",
  "/sutra/ທໍາໃນເບື້ອງຕົ້ນ",
  "/images/sutra/ປະຕິຈະສະມຸບາດ.jpg",
  "/sutra/ປະຕິຈະສະມຸບາດ",
  "/images/sutra/ພຣະຣັດຕະນະໄຕຣ.jpg",
  "/sutra/ພຣະຣັດຕະນະໄຕຣ",
  "/images/sutra/ພຣະສູດທີ່ຕ້ອງຮູ້.jpg",
  "/sutra/ພຣະສູດທີ່ຕ້ອງຮູ້",
  "/images/sutra/ພຣົມວິຫານ 4.jpg",
  "/sutra/ພຣົມວິຫານ 4",
  "/images/sutra/ພຸດທະປະຫວັດ.jpg",
  "/sutra/ພຸດທະປະຫວັດ",
  "/images/sutra/ພົບພູມ.jpg",
  "/sutra/ພົບພູມ",
  "/images/sutra/ມັກວິທີທີ່ງ່າຍ.jpg",
  "/sutra/ມັກວິທີທີ່ງ່າຍ",
  "/images/sutra/ວິໄນສົງ.jpg",
  "/sutra/ວິໄນສົງ",
  "/images/sutra/ສະຕຣີ.jpg",
  "/sutra/ສະຕຣີ",
  "/images/sutra/ສະຕິປະຖານ 4.jpg",
  "/sutra/ສະຕິປະຖານ 4",
  "/images/sutra/ສະມະຖະ ແລະ ວິປັດສະນາ.jpg",
  "/sutra/ສະມະຖະ ແລະ ວິປັດສະນາ",
  "/images/sutra/ສະມາທິ.jpg",
  "/sutra/ສະມາທິ",
  "/images/sutra/ສັງໂຍດ.jpg",
  "/sutra/ສັງໂຍດ",
  "/images/sutra/ສັດ.jpg",
  "/sutra/ສັດ",
  "/images/sutra/ສິນ.jpg",
  "/sutra/ສິນ",
  "/images/sutra/ອະກຸສົນ.jpg",
  "/sutra/ອະກຸສົນ",
  "/images/sutra/ອະຣິຍະສັດ 4.jpg",
  "/sutra/ອະຣິຍະສັດ 4",
  "/images/sutra/ອານາປານະສະຕິ.jpg",
  "/sutra/ອານາປານະສະຕິ",
  "/images/sutra/ອານິສົງ.jpg",
  "/sutra/ອານິສົງ",
  "/images/sutra/ອິທິບາດ 4.jpg",
  "/sutra/ອິທິບາດ 4",
  "/images/sutra/ອິນຊີສັງວອນ.jpg",
  "/sutra/ອິນຊີສັງວອນ",
  "/images/sutra/ເດຣະສານວິຊາ.jpg",
  "/sutra/ເດຣະສານວິຊາ",
  "/images/sutra/ແກ່ ເຈັບ ຕາຍ.jpg",
  "/sutra/ແກ່ ເຈັບ ຕາຍ",
  "/images/sutra/ໂສດາບັນ.jpg",
  "/sutra/ໂສດາບັນ",
  "/js/offline.js",
];

self.addEventListener("install", function (event) {
  event.waitUntil(
    caches
      .open(STATIC_CACHE)
      .then(function (cache) {
        return Promise.allSettled(
          PRECACHE_STATIC.map(function (u) {
            return cache.add(p(u)).catch(function (err) {
              console.warn("[SW] Precache failed for " + p(u), err);
            });
          }),
        );
      })
      .then(function () {
        return self.skipWaiting();
      }),
  );
});

self.addEventListener("activate", function (event) {
  event.waitUntil(
    caches
      .keys()
      .then(function (keys) {
        return Promise.all(
          keys
            .filter(function (key) {
              return (
                key !== CACHE &&
                key !== STATIC_CACHE &&
                key !== ASSETS_CACHE &&
                key !== API_CACHE &&
                key.indexOf(OLD_API_PATTERN) !== 0
              );
            })
            .map(function (key) {
              return caches.delete(key);
            }),
        );
      })
      .then(function () {
        return self.clients.claim();
      }),
  );
});

self.addEventListener("message", function (event) {
  if (event.data && event.data.type === "CACHE_API_DATA") {
    var payload = event.data.payload;
    if (payload && payload.url && payload.data) {
      caches.open(API_CACHE).then(function (cache) {
        var response = new Response(JSON.stringify(payload.data), {
          headers: { "Content-Type": "application/json" },
        });
        cache.put(payload.url, response);
      });
    }
  }
  if (event.data && event.data.type === "SKIP_WAITING") {
    self.skipWaiting();
  }
});

self.addEventListener("fetch", function (event) {
  var request = event.request;
  var url = new URL(request.url);

  if (url.origin !== self.location.origin) return;
  if (request.method !== "GET") {
    if (url.pathname.includes("/api/tts/")) {
      event.respondWith(fetch(request));
    }
    return;
  }
  if (url.pathname.includes("/api/proxy-notion")) return;

  if (
    request.headers.get("Accept") &&
    request.headers.get("Accept").includes("text/html")
  ) {
    event.respondWith(
      fetch(request)
        .then(function (response) {
          if (response && response.status === 200) {
            var clone = response.clone();
            caches.open(CACHE).then(function (cache) {
              cache.put(request, clone);
            });
          }
          return response;
        })
        .catch(function () {
          return caches.match(request).then(function (cached) {
            if (cached) return cached;
            return caches.match(p("/offline.html"));
          });
        }),
    );
    return;
  }

  if (url.pathname.includes("/api/check-update")) {
    event.respondWith(
      fetch(request).catch(function () {
        return new Response('{"version":0}', {
          headers: { "Content-Type": "application/json" },
        });
      }),
    );
    return;
  }

  if (url.pathname.includes("/api/")) {
    event.respondWith(
      fetch(request)
        .then(function (response) {
          if (response && response.status === 200) {
            var clone = response.clone();
            caches.open(API_CACHE).then(function (cache) {
              cache.put(request, clone);
            });
          }
          return response;
        })
        .catch(function () {
          return caches.match(request);
        }),
    );
    return;
  }

  if (
    request.destination === "image" ||
    request.destination === "font" ||
    request.destination === "style" ||
    request.destination === "script" ||
    url.pathname.match(
      /\.(png|jpg|jpeg|gif|webp|svg|ico|ttf|woff|woff2|css|js|json)$/,
    )
  ) {
    event.respondWith(
      caches.match(request).then(function (cached) {
        if (cached) return cached;
        return fetch(request).then(function (response) {
          if (response && response.status === 200) {
            var clone = response.clone();
            caches.open(ASSETS_CACHE).then(function (cache) {
              cache.put(request, clone);
            });
          }
          return response;
        });
      }),
    );
    return;
  }
});
