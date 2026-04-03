<?= view('layout/header') ?>

<div class="container">

    <h1>Ajouter une idée</h1>

    <form method="post" action="<?= site_url('suivi/store') ?>">

        Catégorie<br>
        <input name="categorie"><br><br>

        Acteur<br>
        <input name="acteur"><br><br>

        Quoi<br>
        <input name="quoi"><br><br>

        Détails<br>
        <textarea name="details"></textarea><br><br>

        Priorité<br>
        <select name="priorite">
            <option>normal</option>
            <option>haute</option>
            <option>basse</option>
        </select>

        <br><br>

        <button type="submit">Enregistrer</button>

    </form>

</div>
