<?php

View::AddMeta ('register:host', HOST);
View::AddMeta ('register:theme', THEME);
View::AddJs ('username.js');
View::AddCss ('username_validation.css');
View::Header();

?>

<h1><?=Language::GetText('register_header')?></h1>

<?php if ($success): ?>
    <div id="success"><?=$success?></div>
<?php elseif ($error_msg): ?>
    <div id="error"><?=$error_msg?></div>
<?php endif; ?>

<div class="block">

    <p><strong><?=Language::GetText('join_today')?></strong><br /><br />
    <?=Language::GetText('register_text')?></p>

</div>

        
<div class="block" id="register-form" style="padding-left:40px;">

    <form action="<?=HOST?>/register/" method="post">

        <p class="row-shift"><?=Language::GetText('asterisk')?></p>

        <div class="row">
            <label class="<?=(isset ($Errors['email'])) ? 'errors' : ''?>">*<?=Language::GetText('email')?>:</label>
            <input name="email" type="text" class="text" value="<?=(isset ($Errors, $data['email'])) ? $data['email'] : ''?>" />
        </div>

        <div class="row-shift">
            <?=Language::GetText('username_req')?>:<br /><br />
            <strong>CumulusClips.com/ members/ [USERNAME]</strong>
        </div>

        <div class="row">
            <label class="<?=(isset ($Errors['username'])) ? 'errors' : ''?>">*<?=Language::GetText('username')?>:</label>
            <input name="username" type="text" class="text" id="username" value="<?=(isset ($Errors, $data['username'])) ? $data['username']:''?>" maxlength="30" />
            <br /><span id="status"></span>
        </div>

        <div class="row">
            <label class="<?=(isset ($Errors['password']) || isset($Errors['match'])) ? 'errors' : ''?>">*<?=Language::GetText('password')?>:</label>
            <input name="password" type="password" class="text" value="" />
        </div>

        <div class="row-shift">
            <input name="terms" type="checkbox" id="terms" value="Agree" />
            <label for="terms">*<?=Language::GetText('agree_terms', array ('link' => HOST . '/terms/'))?></label>
        </div>

        <div class="row-shift">
            <input type="hidden" name="submitted" value="TRUE" />
            <input class="button" type="submit" name="button" value="<?=Language::GetText('register_button')?>" />
        </div>

    </form>

</div>

<?php View::Footer(); ?>