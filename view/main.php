<div class="main">
    <header>
        <div class="inf title">
            <?= $this->cfg['siteName'] ?>
        </div>
    </header>
    <section>
        <div class="content">
            <?= $this->body() ?>
        </div>
    </section>
    <footer>
        <div class="inf">
            &copy; &quot;<?= $this->cfg['siteName'] ?>&quot;, <?= $this->str('createDate', 2019) ?>
        </div>
    </footer>
</div>