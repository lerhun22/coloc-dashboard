<?php

if (! function_exists('renderImages')) {

    function renderImages(array $images, string $label): void
    {
        if (empty($images)) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Prépare galerie lightbox
        |--------------------------------------------------------------------------
        */
        $gallery = array_map(
            fn($img) =>
            $img['photo_url']
                ?? $img['thumb_url']
                ?? '',
            $images
        );

        $json = htmlspecialchars(
            json_encode($gallery),
            ENT_QUOTES,
            'UTF-8'
        );

        echo "<div class='jugement-grid'>";

        foreach ($images as $i => $img) {

            $src = $img['photo_url']
                ?? $img['thumb_url']
                ?? '';

            if (!$src) {
                continue;
            }

            $title = esc(
                $img['titre'] ?? 'Sans titre'
            );

            $competition = esc(
                $img['competition_nom']
                    ?? ''
            );

            echo "<div class='jugement-card'>";

            /*
            |--------------------------------------------------------------------------
            | vignette
            |--------------------------------------------------------------------------
            */
            echo "
            <img
                src='{$src}'
                class='jugement-img'
                onclick='openLightboxList($json,$i)'
            >";

            /*
            |--------------------------------------------------------------------------
            | contenu carte
            |--------------------------------------------------------------------------
            */
            echo "<div class='jugement-content'>";

            echo "
                <div class='jugement-title'>
                    {$title}
                </div>
            ";

            echo "
                <div class='jugement-meta'>
                    {$label} • {$competition}
                </div>
            ";

            /*
            |--------------------------------------------------------------------------
            | notes jury
            |--------------------------------------------------------------------------
            */
            echo "<div class='badge-notes'>";

            foreach ($img['notes_array'] ?? [] as $note) {

                $color = '#f39c12';

                if ($note >= 16) {
                    $color = '#27ae60';
                } elseif ($note <= 8) {
                    $color = '#e74c3c';
                }

                echo "
                    <span style='background:{$color}'>
                        {$note}
                    </span>
                ";
            }

            echo "</div>"; // badge-notes
            echo "</div>"; // content
            echo "</div>"; // card
        }

        echo "</div>"; // grid
    }
}
