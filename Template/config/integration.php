<h3><img src="<?= $this->url->dir() ?>plugins/Jabber/jabber-icon.png"/>&nbsp;Jabber (XMPP)</h3>
<div class="listing">
    <?= $this->form->label(t('XMPP server address'), 'jabber_server') ?>
    <?= $this->form->text('jabber_server', $values, array(), array('placeholder="tcp://myserver:5222"')) ?>
    <p class="form-help"><?= t('The server address must use this format: "tcp://hostname:5222"') ?></p>

    <?= $this->form->label(t('Jabber domain'), 'jabber_domain') ?>
    <?= $this->form->text('jabber_domain', $values, array(), array('placeholder="example.com"')) ?>

    <?= $this->form->label(t('Username'), 'jabber_username') ?>
    <?= $this->form->text('jabber_username', $values, array()) ?>

    <?= $this->form->label(t('Password'), 'jabber_password') ?>
    <?= $this->form->password('jabber_password', $values, array()) ?>

    <?= $this->form->label(t('Jabber nickname for Kanboard'), 'jabber_nickname') ?>
    <?= $this->form->text('jabber_nickname', $values, array()) ?>

    <p class="form-help"><a href="https://github.com/kanboard/plugin-jabber" target="_blank"><?= t('Help on Jabber integration') ?></a></p>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue"/>
    </div>
</div>