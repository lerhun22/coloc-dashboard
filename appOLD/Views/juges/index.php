<h1>Gestion des juges</h1>

<a href="/juges/create" class="btn-primary">+ Nouveau juge</a>

<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Statut</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($judges as $judge): ?>
            <tr>
                <td><?= esc($judge['nom']) ?></td>
                <td>
                    <?= $judge['competitions_id'] == 0 ? 'Libre' : 'Affecté' ?>
                </td>
                <td>
                    <?php if ($judge['competitions_id'] == 0): ?>
                        <form method="post" action="/juges/<?= $judge['id'] ?>/delete"
                              onsubmit="return confirm('Confirmer ?')">
                            <button type="submit" class="btn-danger">Supprimer</button>
                        </form>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>