var bwdb = (function() {
    var db = null;
    var DB_NAME = 'buddhaword-offline';
    var DB_VERSION = 1;

    function open() {
        return new Promise(function(resolve, reject) {
            if (db) { resolve(db); return; }
            var req = indexedDB.open(DB_NAME, DB_VERSION);
            req.onupgradeneeded = function(e) {
                var d = e.target.result;
                if (!d.objectStoreNames.contains('tts')) {
                    d.createObjectStore('tts', { keyPath: 'hash' });
                }
                if (!d.objectStoreNames.contains('search')) {
                    d.createObjectStore('search', { keyPath: 'query' });
                }
            };
            req.onsuccess = function(e) {
                db = e.target.result;
                resolve(db);
            };
            req.onerror = function() { reject(req.error); };
        });
    }

    function put(store, key, value) {
        return open().then(function(d) {
            return new Promise(function(resolve, reject) {
                var tx = d.transaction(store, 'readwrite');
                var obj;
                if (store === 'tts') { obj = { hash: key, value: value, ts: Date.now() }; }
                else { obj = { query: key, value: value, ts: Date.now() }; }
                tx.objectStore(store).put(obj);
                tx.oncomplete = resolve;
                tx.onerror = function() { reject(tx.error); };
            });
        });
    }

    function get(store, key) {
        return open().then(function(d) {
            return new Promise(function(resolve) {
                var tx = d.transaction(store, 'readonly');
                var req = tx.objectStore(store).get(key);
                req.onsuccess = function() { resolve(req.result ? req.result.value : null); };
                req.onerror = function() { resolve(null); };
            });
        });
    }

    function getAll(store) {
        return open().then(function(d) {
            return new Promise(function(resolve, reject) {
                var tx = d.transaction(store, 'readonly');
                var req = tx.objectStore(store).getAll();
                req.onsuccess = function() { resolve(req.result || []); };
                req.onerror = function() { reject(tx.error); };
            });
        });
    }

    function clear(store) {
        return open().then(function(d) {
            return new Promise(function(resolve, reject) {
                var tx = d.transaction(store, 'readwrite');
                tx.objectStore(store).clear();
                tx.oncomplete = resolve;
                tx.onerror = function() { reject(tx.error); };
            });
        });
    }

    function clearAll() {
        return Promise.all([clear('tts'), clear('search')]);
    }

    function count(store) {
        return open().then(function(d) {
            return new Promise(function(resolve) {
                var tx = d.transaction(store, 'readonly');
                var req = tx.objectStore(store).count();
                req.onsuccess = function() { resolve(req.result); };
                req.onerror = function() { resolve(0); };
            });
        });
    }

    return {
        putTTS: function(hash, audioBlob) { return put('tts', hash, audioBlob); },
        getTTS: function(hash) { return get('tts', hash); },
        countTTS: function() { return count('tts'); },
        putSearch: function(query, results) { return put('search', query, results); },
        getSearch: function(query) { return get('search', query); },
        countSearch: function() { return count('search'); },
        clearAll: clearAll,
        getAllTTS: function() { return getAll('tts'); },
        getAllSearch: function() { return getAll('search'); }
    };
})();

function ttsHash(text, lang) {
    var s = text + '|' + lang;
    var hash = 0;
    for (var i = 0; i < s.length; i++) {
        var c = s.charCodeAt(i);
        hash = ((hash << 5) - hash) + c;
        hash = hash & hash;
    }
    return Math.abs(hash).toString(36);
}

function fetchWithCache(url, options, cacheKey, cacheStore) {
    if (navigator.onLine === false) {
        return bwdb[cacheStore](cacheKey).then(function(cached) {
            if (cached) return cached;
            throw new Error('Offline and no cached data');
        });
    }
    return fetch(url, options).then(function(resp) {
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        return resp.json().then(function(data) {
            if (data.error) throw new Error(data.error);
            bwdb['put' + cacheStore.charAt(0).toUpperCase() + cacheStore.slice(1)](cacheKey, data);
            return data;
        });
    });
}
