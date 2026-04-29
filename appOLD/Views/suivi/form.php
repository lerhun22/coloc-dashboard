<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<style>
    .form-box {
        max-width: 900px;
        background: #f9fafb;
        padding: 20px;
        border-radius: 8px;
        font-size: 16px;
    }

    .form-group {
        margin-bottom: 14px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 4px;
    }

    input,
    select,
    textarea {
        width: 100%;
        padding: 8px;
        font-size: 16px;
    }

    textarea {
        min-height: 80px;
    }

    textarea.large {
        min-height: 140px;
    }

    .row-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
    }

    .row-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
</style>


<div class="main-content">
    <div class="container">

        <h2>Edition suivi</h2>

        <form method="post" action="<?= site_url('suivi/save') ?>" class="form-box">

            <input type="hidden" name="id" value="<?= $item['id'] ?? '' ?>">


            <div class="row-3">

                <div class="form-group">

                    <label>Categorie</label>

                    <select name="categorie">

                        <?php
                        $list = ['idee', 'evolution', 'organisation', 'communication', 'bug'];
                        foreach ($list as $v):
                        ?>

                            <option value="<?= $v ?>" <?= (($item['categorie'] ?? '') == $v) ? 'selected' : '' ?>>
                                <?= $v ?>
                            </option>

                        <?php endforeach ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>Acteur</label>

                    <select name="acteur">

                        <?php
                        $list = ['ur', 'fede', 'commissaire', 'juge', 'dev'];
                        foreach ($list as $v):
                        ?>

                            <option value="<?= $v ?>" <?= (($item['acteur'] ?? '') == $v) ? 'selected' : '' ?>>
                                <?= $v ?>
                            </option>

                        <?php endforeach ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>Priorite</label>

                    <select name="priorite">

                        <?php
                        $list = ['basse', 'normal', 'haute'];
                        foreach ($list as $v):
                        ?>

                            <option value="<?= $v ?>" <?= (($item['priorite'] ?? '') == $v) ? 'selected' : '' ?>>
                                <?= $v ?>
                            </option>

                        <?php endforeach ?>

                    </select>

                </div>

            </div>



            <div class="row-3">

                <div class="form-group">

                    <label>Statut</label>

                    <select name="statut">

                        <?php
                        $list = ['ouvert', 'test', 'valide', 'refuse', 'clos'];
                        foreach ($list as $v):
                        ?>

                            <option value="<?= $v ?>" <?= (($item['statut'] ?? '') == $v) ? 'selected' : '' ?>>
                                <?= $v ?>
                            </option>

                        <?php endforeach ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>Impact</label>

                    <select name="impact_systeme">

                        <?php
                        $list = ['coloc', 'copain', 'coloc+copain', 'pte', 'global'];
                        foreach ($list as $v):
                        ?>

                            <option value="<?= $v ?>" <?= (($item['impact_systeme'] ?? '') == $v) ? 'selected' : '' ?>>
                                <?= $v ?>
                            </option>

                        <?php endforeach ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>Cout</label>

                    <select name="cout">

                        <?php
                        $list = ['faible', 'moyen', 'eleve', 'inconnu'];
                        foreach ($list as $v):
                        ?>

                            <option value="<?= $v ?>" <?= (($item['cout'] ?? '') == $v) ? 'selected' : '' ?>>
                                <?= $v ?>
                            </option>

                        <?php endforeach ?>

                    </select>

                </div>

            </div>



            <div class="row-3">

                <div class="form-group">

                    <label>Decision</label>

                    <select name="decision">

                        <?php
                        $list = ['a_etudier', 'retenu', 'refuse', 'plus_tard', 'en_test', 'valide'];
                        foreach ($list as $v):
                        ?>

                            <option value="<?= $v ?>" <?= (($item['decision'] ?? '') == $v) ? 'selected' : '' ?>>
                                <?= $v ?>
                            </option>

                        <?php endforeach ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>Version</label>

                    <select name="version">

                        <?php
                        $list = ['CI4', 'COLOC', 'COPAIN', 'v1', 'v2'];
                        foreach ($list as $v):
                        ?>

                            <option value="<?= $v ?>" <?= (($item['version'] ?? '') == $v) ? 'selected' : '' ?>>
                                <?= $v ?>
                            </option>

                        <?php endforeach ?>

                    </select>

                </div>


                <div class="form-group">

                    <label>Saison</label>

                    <select name="saison">

                        <?php
                        $list = ['2025', '2026', '2027'];
                        foreach ($list as $v):
                        ?>

                            <option value="<?= $v ?>" <?= (($item['saison'] ?? '') == $v) ? 'selected' : '' ?>>
                                <?= $v ?>
                            </option>

                        <?php endforeach ?>

                    </select>

                </div>

            </div>


            <div class="form-group">
                <label>Reunion</label>
                <input name="reunion" value="<?= $item['reunion'] ?? '' ?>">
            </div>



            <div class="form-group">
                <label>Quoi</label>
                <input name="quoi" value="<?= $item['quoi'] ?? '' ?>">
            </div>



            <div class="form-group">
                <label>Details</label>
                <textarea class="large" name="details"><?= $item['details'] ?? '' ?></textarea>
            </div>


            <div class="form-group">
                <label>Analyse</label>
                <textarea class="large" name="analyse"><?= $item['analyse'] ?? '' ?></textarea>
            </div>


            <div class="row-2">

                <div class="form-group">
                    <label>Benefice</label>
                    <textarea name="benefice"><?= $item['benefice'] ?? '' ?></textarea>
                </div>

                <div class="form-group">
                    <label>Risque</label>
                    <textarea name="risque"><?= $item['risque'] ?? '' ?></textarea>
                </div>

            </div>


            <div class="form-group">
                <label>Contrainte</label>
                <textarea name="contrainte"><?= $item['contrainte'] ?? '' ?></textarea>
            </div>


            <button>Sauvegarder</button>

        </form>

    </div>
</div>

<?= $this->endSection() ?>
