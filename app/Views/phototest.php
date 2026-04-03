<!DOCTYPE html>
<html>
<head>
    <title>Phototest</title>

    <style>
        body {
            background: #111;
            color: #fff;
            font-family: Arial;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 10px;
            padding: 20px;
        }

        img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
    </style>
</head>

<body>

<h2 style="padding:20px;">
    <?= $folder ?> (<?= count($images) ?> images)
</h2>

<div class="grid">

<?php foreach ($images as $img): ?>

    <img src="<?= base_url('uploads/competitions/' . $folder . '/photos/' . $img) ?>">

<?php endforeach; ?>

</div>

</body>
</html>