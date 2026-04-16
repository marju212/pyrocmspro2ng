<div id="respond">

    <?php echo form_open("comments/create/{$module}", 'id="commentform"') ?>

    <noscript><?php echo form_input('d0ntf1llth1s1n', '', 'style="display:none"') ?></noscript>

    <h4><?php echo lang('comments:your_comment') ?></h4>

    <?php echo form_hidden('entry', $entry_hash) ?>

    <?php if (!$current_user): ?>
    <!--
    
        <p class="comment-notes">Your email address will not be published. Required fields are marked <span class="required">*</span></p>							
-->
        <p class="comment-form-author">
            <label for="name"><?php echo lang('comments:name_label') ?></label> <span class="required">*</span>
            <input id="author" name="name" type="text" value="<?php echo $comment['name'] ?>" size="30" aria-required="true">
        </p>
<p class="comment-form-email">
        <label for="email"><?php echo lang('global:email') ?></label> <span class="required">*</span>
        <input id="email" name="email" type="text" value="<?php echo $comment['email'] ?>" size="30" aria-required="true">
        </p>
<p class="comment-form-url">
        <label for="website"><?php echo lang('comments:website_label') ?></label>
        <input id="url" name="website" type="text" value="<?php echo $comment['website'] ?>" size="30">
        </p>
    <?php endif ?>
        <p class="comment-form-comment">
    <label for="comment"><?php echo lang('comments:message_label') ?></label>
    <textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"><?php echo $comment['comment'] ?></textarea>
</p>							 					

<input name="submit" type="submit" id="submit" value="<?php echo lang('comments:send_label') ?>" class="button white">
</p>



						


<?php echo form_close(); ?>
</div>    
