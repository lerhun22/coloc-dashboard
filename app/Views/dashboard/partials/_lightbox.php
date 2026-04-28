<div id="lightbox" class="lightbox">
    <span
        class="lightbox-close"
        onclick="closeLightbox()">
        ✕
    </span>

    <div
        class="lightbox-nav left"
        onclick="prevImage()">
        ‹
    </div>

    <img id="lightbox-img">

    <div
        class="lightbox-nav right"
        onclick="nextImage()">
        ›
    </div>
</div>

<script>
    let gallery = [];
    let current = 0;
    let autoPlay = null;

    function openLightboxList(
        list,
        index
    ) {
        gallery = list;
        current = index;

        updateLightbox();

        document.getElementById(
            'lightbox'
        ).style.display = 'flex';
    }

    function updateLightbox() {
        document.getElementById(
            'lightbox-img'
        ).src = gallery[current];
    }

    function nextImage() {
        current =
            (current + 1) % gallery.length;

        updateLightbox();
    }

    function prevImage() {
        current =
            (current - 1 + gallery.length) %
            gallery.length;

        updateLightbox();
    }

    function closeLightbox() {
        document.getElementById(
            'lightbox'
        ).style.display = 'none';

        clearInterval(autoPlay);
    }
</script>