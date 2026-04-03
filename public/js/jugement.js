/**
 * ============================================================
 * 📦 MODULE : Jugement (Front JS)
 * VERSION FINALE + FILTRES
 * ============================================================
 */

console.log("JUGEMENT.JS Loaded !");

/* ============================================================
   🔧 VARIABLES
============================================================ */

let photo_id = null;
let photoIndex = [];
let photoPosition = {};

let overlay;
let zoomImg;

let isReady = false;
let isLoading = false;

/* ============================================================
   📸 URL IMAGE
============================================================ */

function getPhotoUrl(ean) {
    let cleanBase = base_url.replace(/\/$/, '');
    let cleanPath = photosPath.replace(/^\//, '');
    return cleanBase + "/" + cleanPath + "/" + ean + ".jpg";
}

/* ============================================================
   🧰 UTILS
============================================================ */

function setText(id, val) {
    let el = document.getElementById(id);
    if (el) el.innerText = val ?? "";
}

/* ============================================================
   📸 LOAD PHOTO
============================================================ */

function loadPhoto(id) {

    if (!id || isLoading) return;

    isLoading = true;

    console.log("LOAD PHOTO ID:", id);

    fetch(base_url + "/jugement/photo/" + id)
        .then(r => r.json())
        .then(data => {

            if (!data.photo) {
                console.error("❌ PHOTO NOT FOUND");
                isLoading = false;
                return;
            }

            photo_id = String(data.photo.photo_id ?? data.photo.id ?? "").trim();

            if (!photo_id || photo_id === "undefined") {
                console.error("❌ photo_id invalide");
                isLoading = false;
                return;
            }

            /* IMAGE */
            let img = document.getElementById("photo-active");
            if (img) img.src = getPhotoUrl(data.photo.ean);

            /* TEXT */
            setText("photo-titre", data.photo.titre);
            setText("photo-ean", data.photo.ean);
            setText("photo-numero", data.photo.passage);

            /* NOTES */
            document.querySelectorAll(".note-input").forEach(input => {
                let jugeId = input.dataset.juge;
                input.value = (data.notes && data.notes[jugeId] !== undefined)
                    ? data.notes[jugeId]
                    : "";
            });

            setTimeout(updateTotal, 0);

            setActiveThumb(photo_id);
            scrollToThumb(photo_id);
            updateCounter();

            attachZoom();

            isReady = true;
            isLoading = false;

            setTimeout(() => {
                focusBarcodeInput();
            }, 0);
        })
        .catch(err => {
            console.error("❌ FETCH ERROR", err);
            isLoading = false;
        });
}

/* ============================================================
   🎯 ACTIVE THUMB
============================================================ */

function setActiveThumb(id) {
    document.querySelectorAll(".photo-tile").forEach(el => {
        el.classList.remove("active");
    });

    let el = document.querySelector(`.photo-tile[data-id="${id}"]`);
    if (el) el.classList.add("active");
}

/* ============================================================
   📜 SCROLL
============================================================ */

function scrollToThumb(id) {
    let el = document.querySelector(`[data-id="${id}"]`);
    if (!el) return;

    el.scrollIntoView({ behavior: "smooth", block: "center" });
}

/* ============================================================
   📊 INDEX
============================================================ */

function rebuildPhotoIndex() {

    photoIndex = [];
    photoPosition = {};

    document.querySelectorAll(".photo-tile").forEach(tile => {

        if (tile.style.display === "none") return;

        let id = String(tile.dataset.id).trim();

        photoPosition[id] = photoIndex.length;
        photoIndex.push(id);
    });

    console.log("INDEX:", photoIndex);
}

/* ============================================================
   🧭 NAVIGATION
============================================================ */

function nextPhoto() {

    if (!isReady || !photo_id) return;

    let pos = photoPosition[String(photo_id)];
    let next = photoIndex[pos + 1];

    if (next) loadPhoto(next);
}

function prevPhoto() {

    if (!isReady || !photo_id) return;

    let pos = photoPosition[String(photo_id)];
    let prev = photoIndex[pos - 1];

    if (prev) loadPhoto(prev);
}

/* ============================================================
   🔢 TOTAL
============================================================ */

function updateTotal() {

    let total = 0;

    document.querySelectorAll(".note-input").forEach(input => {

        let val = input.value;
        if (!val) return;

        val = val.replace(",", ".");
        let num = parseFloat(val);

        if (!isNaN(num)) total += num;
    });

    document.getElementById("total").value = total;
}

/* ============================================================
   🔢 COMPTEUR
============================================================ */

function updateCounter() {

    if (!photo_id) return;

    let pos = photoPosition[String(photo_id)];
    if (pos === undefined) return;

    setText("photo-position", pos + 1);
    setText("photo-total", photoIndex.length);
}

/* ============================================================
   🔍 ZOOM
============================================================ */

function initZoom() {

    overlay = document.getElementById("zoomOverlay");
    zoomImg = document.getElementById("zoomImage");

    if (!overlay) return;

    overlay.addEventListener("click", () => {
        overlay.classList.remove("active");
    });
}

function attachZoom() {

    let photo = document.getElementById("photo-active");
    if (!photo) return;

    photo.onclick = () => {
        zoomImg.src = photo.src;
        overlay.classList.add("active");
    };
}

/* ============================================================
   🎯 FILTRES
============================================================ */

function applyFilters() {

    let eanFilter = document.getElementById("filter-ean").value.trim();
    let passageFilter = document.getElementById("filter-passage").value.trim();

    let showPending = document.getElementById("filter-pending").checked;
    let showPartial = document.getElementById("filter-partial").checked;
    let showDone = document.getElementById("filter-done").checked;

    document.querySelectorAll(".photo-tile").forEach(tile => {

        let ean = tile.dataset.ean;
        let passage = tile.dataset.passage;

        let show = true;

        if (eanFilter && !ean.includes(eanFilter)) show = false;
        if (passageFilter && passage !== passageFilter) show = false;

        let statusOK =
            (tile.classList.contains("pending") && showPending) ||
            (tile.classList.contains("partial") && showPartial) ||
            (tile.classList.contains("done") && showDone);

        if (!statusOK) show = false;

        tile.style.display = show ? "" : "none";
    });

    rebuildPhotoIndex();

    if (!photoIndex.includes(photo_id) && photoIndex.length > 0) {
        loadPhoto(photoIndex[0]);
    }
}

/* ============================================================
   🎯 CLAVIER
============================================================ */

function initKeyboard() {

    document.addEventListener('keydown', function(e) {

        const tag = document.activeElement.tagName;
        if (tag === "INPUT") return;

        if (e.key === "ArrowRight") {
            e.preventDefault();
            nextPhoto();
        }

        if (e.key === "ArrowLeft") {
            e.preventDefault();
            prevPhoto();
        }

        if (e.key === "Escape") {
            overlay.classList.remove("active");
        }
    });
}

/* ============================================================
   🚀 INIT
============================================================ */

document.addEventListener("DOMContentLoaded", () => {

    console.log("INIT DOM");

    document.body.tabIndex = 0;
    document.body.focus();

    initZoom();
    initKeyboard();

    document.addEventListener("click", (e) => {

        const tag = e.target.tagName;

        if (
            tag === "INPUT" ||
            tag === "TEXTAREA" ||
            tag === "SELECT" ||
            e.target.isContentEditable
        ) {
            return;
        }

        document.body.focus();
    });

    /* INPUT NOTES */
    document.querySelectorAll(".note-input").forEach(input => {
        input.addEventListener("input", updateTotal);
    });

    /* FILTRES */
    document.getElementById("filter-ean").addEventListener("input", applyFilters);
    document.getElementById("filter-passage").addEventListener("input", applyFilters);

    document.querySelectorAll(".filter-checkbox").forEach(cb => {
        cb.addEventListener("change", applyFilters);
    });

    /* CLICK TILES */
    document.querySelectorAll(".photo-tile").forEach(tile => {
        tile.onclick = () => loadPhoto(tile.dataset.id);
    });

    rebuildPhotoIndex();

    if (photoIndex.length > 0) {
        loadPhoto(photoIndex[0]);
    }
});

function focusBarcodeInput() {

    const input = document.getElementById("filter-ean");

    if (!input) return;

    // 🔥 ne pas voler le focus si utilisateur saisit déjà
    const active = document.activeElement;

    if (
        active &&
        (active.tagName === "INPUT" || active.tagName === "TEXTAREA")
    ) {
        return;
    }

    input.focus();
    input.select(); // 🔥 pratique pour scanner / retaper direct
}
