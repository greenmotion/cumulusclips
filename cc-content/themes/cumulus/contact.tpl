<?php View::Header(); ?>

<h1><?=Language::GetText('contact_header')?></h1>

<?php if ($error_msg): ?>
    <div id="error"><?=$error_msg?></div>
<?php elseif ($success): ?>
    <div id="success"><?=$success?></div>
<?php endif; ?>


<div class="block">

    <p><?=Language::GetText('contact_text')?></p>

    <form action="<?=HOST?>/contact/" method="post" id="contact-form">

        <div class="row">
            <label for="name" class="<?=(!empty ($Errors['name'])) ? 'errors' : ''?>"><?=Language::GetText('name')?>:</label>
            <input class="text" type="text" name="name" value="<?=($error_msg && $name) ? htmlspecialchars ($name) : ''?>" />
        </div>

        <div class="row">
            <label for="email" class="<?=(!empty ($Errors['email'])) ? 'errors' : ''?>"><?=Language::GetText('email')?>:</label>
            <input class="text" type="text" name="email" value="<?=($error_msg && $email) ? $email : ''?>" />
        </div>
        
        <div class="row">
            <label for="message" class="<?=(!empty ($Errors['message'])) ? 'errors' : ''?>"><?=Language::GetText('message')?>:</label>
            <textarea name="message" class="text"><?=($error_msg && $message) ? htmlspecialchars ($message) : ''?></textarea>
        </div>

        <div class="row-shift">
            <input type="hidden" value="TRUE" name="submitted" />
            <input class="button" type="submit" name="button" value="<?=Language::GetText('contact_button')?>" />
        </div>
        
    </form>

</div>

<?php View::Footer(); ?>