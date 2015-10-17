<h3><img src="<?= $this->url->dir() ?>plugins/Jabber/jabber-icon.png"/>&nbsp;Jabber (XMPP)</h3>
<div class="listing">
    <?= $this->form->label(t('Multi-user chat room'), 'jabber_room') ?>
    <?= $this->form->text('jabber_room', $values, array(), array('placeholder="myroom@conference.example.com"')) ?>

    <p class="form-help"><a href="https://github.com/kanboard/plugin-jabber" target="_blank"><?= t('Help on Jabber integration') ?></a></p>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue"/>
    </div>
</div>