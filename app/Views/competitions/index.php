<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="main-content">
    <div class="container">

        <h1 class="page-title">Compétitions</h1>

        <!-- ========================================= -->
        <!-- 🔍 FILTRES -->
        <!-- ========================================= -->

        <div class="filters">
            <button class="filter-btn active" data-filter="all">Tous (<span id="count-all">0</span>)</button>
            <button class="filter-btn" data-filter="problems">⚠ Problèmes (<span id="count-problems">0</span>)</button>
            <button class="filter-btn" data-filter="regional">UR (<span id="count-regional">0</span>)</button>
            <button class="filter-btn" data-filter="national">National (<span id="count-national">0</span>)</button>
        </div>

        <div class="competition-list">

            <!-- IMPORT -->
            <div class="competition-card new-competition"
                onclick="window.location='<?= site_url('competitions/import') ?>'">

                <div class="bloc infos">
                    <h2 class="competition-title">+ Importer une compétition COPAINS</h2>
                    <div class="competition-date-inline">Fédération Photographique de France</div>
                </div>

                <div class="bloc stat-ur">Import serveur fédéral</div>
            </div>

            <?php $currentType = null; ?>

            <?php foreach ($competitions_list as $competition): ?>

                <?php
                $type = empty($competition['urs_id']) ? 'national' : 'regional';
                $status = $competition['status'] ?? 'empty';

                // 🔥 on ignore les compétitions totalement vides
                if (($competition['photo_count'] ?? 0) == 0) continue;

                if ($type !== $currentType):
                    $currentType = $type;
                ?>

                    <div class="section-title">
                        <?= $type === 'regional' ? '🏡 Compétitions Régionales' : '🇫🇷 Compétitions Nationales' ?>
                    </div>

                <?php endif; ?>

                <div class="competition-card
    <?= $type === 'national' ? 'comp-national' : 'comp-regional' ?>
    <?= $status === 'missing_files' ? 'comp-problem' : '' ?>
    <?= ($competition['id'] == ($activeCompetitionId ?? null)) ? 'comp-active' : '' ?>"

                    data-type="<?= $type ?>"
                    data-status="<?= $status ?>"

                    onclick="window.location='<?= base_url('competitions/' . $competition['id']) ?>'">

                    <!-- ACTION -->
                    <div class="bloc action" onclick="event.stopPropagation();">
                        <a href="<?= base_url('competitions/delete/' . $competition['id']) ?>"
                            class="btn-action btn-danger"
                            onclick="return confirm('Supprimer la compétition ?')">
                            SUPP
                        </a>
                    </div>

                    <!-- INFOS -->
                    <div class="bloc infos">
                        <span class="competition-badge">
                            <?= $type === 'national' ? 'National' : 'UR' . esc($competition['urs_id']) ?>
                        </span>

                        <span class="competition-title">
                            <?= esc($competition['nom']) ?>
                        </span>

                        <span class="meta">#<?= esc($competition['id']) ?></span>
                        <span class="meta"><?= esc($competition['date_competition']) ?></span>
                    </div>

                    <!-- DETAIL -->
                    <div class="bloc stat-nat">
                        <span><strong><?= $competition['photo_count'] ?></strong> photos</span>
                        <span><strong><?= $competition['author_count'] ?></strong> auteurs</span>
                        <span><strong><?= $competition['clubs_nat'] ?></strong> clubs</span>
                    </div>

                    <!-- UR -->
                    <div class="bloc stat-ur">
                        <span><?= $competition['clubs_ur'] ?> UR<?= esc($userUr) ?></span>
                        <span><?= $competition['clubs_nat'] ?> total</span>
                    </div>

                    <!-- FS STATUS -->
                    <div class="bloc status">
                        <?php if ($status === 'ready'): ?>
                            <span class="badge photo-ok">Photos</span>
                        <?php elseif ($status === 'missing_files'): ?>
                            <span class="badge photo-error">⚠ Photos</span>
                        <?php else: ?>
                            <span class="badge photo-off">Photos</span>
                        <?php endif; ?>
                    </div>

                </div>

            <?php endforeach; ?>

        </div>
    </div>
</div>

<!-- ========================================= -->
<!-- 🎨 CSS -->
<!-- ========================================= -->

<style>
    .filters {
        margin-bottom: 15px;
    }

    .filter-btn {
        border: none;
        background: #eee;
        padding: 6px 10px;
        margin-right: 5px;
        border-radius: 6px;
        cursor: pointer;
    }

    .filter-btn.active {
        background: #1976d2;
        color: white;
    }

    .section-title {
        margin-top: 20px;
        font-weight: bold;
        font-size: 14px;
        color: #444;
    }

    .competition-card {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
    }

    .bloc {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .bloc.infos {
        flex: 1;
    }

    .meta {
        font-size: 12px;
        color: #777;
    }

    .bloc.stat-nat {
        width: 260px;
        justify-content: center;
    }

    .bloc.stat-ur {
        width: 160px;
        justify-content: flex-end;
    }

    .bloc.status {
        width: 120px;
        justify-content: flex-end;
    }

    .comp-regional {
        border-left: 6px solid #388e3c;
        background: #eefaf0;
    }

    .comp-national {
        border-left: 6px solid #1976d2;
        background: #eef5ff;
    }

    .comp-problem {
        border-left-color: #ff9800 !important;
    }

    .photo-ok {
        background: #fff;
        border: 1px solid #000;
    }

    .photo-error {
        background: #ff9800;
        color: #fff;
    }

    .photo-off {
        background: #eee;
        color: #999;
    }
</style>

<!-- ========================================= -->
<!-- ⚡ JS -->
<!-- ========================================= -->

<script>
    const cards = document.querySelectorAll('.competition-card');

    function updateCounts() {

        let counts = {
            all: 0,
            problems: 0,
            regional: 0,
            national: 0
        };

        cards.forEach(card => {

            const type = card.dataset.type;
            const status = card.dataset.status;

            if (!type) return;

            counts.all++;

            if (type === 'regional') counts.regional++;
            if (type === 'national') counts.national++;
            if (status === 'missing_files') counts.problems++;
        });

        document.getElementById('count-all').innerText = counts.all;
        document.getElementById('count-problems').innerText = counts.problems;
        document.getElementById('count-regional').innerText = counts.regional;
        document.getElementById('count-national').innerText = counts.national;
    }

    function refreshSections() {

        document.querySelectorAll('.section-title').forEach(section => {

            let next = section.nextElementSibling;
            let visible = false;

            while (next && !next.classList.contains('section-title')) {

                if (next.style.display !== 'none') {
                    visible = true;
                    break;
                }

                next = next.nextElementSibling;
            }

            section.style.display = visible ? 'block' : 'none';
        });
    }

    document.querySelectorAll('.filter-btn').forEach(btn => {

        btn.addEventListener('click', function() {

            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.dataset.filter;

            cards.forEach(card => {

                const type = card.dataset.type;
                const status = card.dataset.status;

                let show = false;

                if (filter === 'all') show = true;
                if (filter === 'regional' && type === 'regional') show = true;
                if (filter === 'national' && type === 'national') show = true;
                if (filter === 'problems' && status === 'missing_files') show = true;

                card.style.display = show ? 'flex' : 'none';
            });

            refreshSections();
        });
    });

    updateCounts();
</script>

<?= $this->endSection() ?>