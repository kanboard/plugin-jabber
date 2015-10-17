<h3><img src="<?= $this->url->dir() ?>plugins/Jabber/jabber-icon.png"/>&nbsp;Jabber (XMPP)</h3>
<div class="listing">
    <?= $this->form->label(t('Jabber Id'), 'jabber_jid') ?>
    <?= $this->form->text('jabber_jid', $values) ?>

    <p class="form-help"><a href="https://github.com/kanboard/plugin-jabber" target="_blank"><?= t('Help on Jabber integration') ?></a></p>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue"/>
    </div>
</div>