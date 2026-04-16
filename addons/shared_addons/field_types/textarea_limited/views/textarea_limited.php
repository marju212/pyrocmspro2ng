<?php echo $textarea ?>

<script type="text/javascript">
    var options = {
        'maxCharacterSize': <?= $character_limit ?>,
        'originalStyle': 'originalDisplayInfo',
        'warningStyle': 'warningDisplayInfo',
        'warningNumber': 40,
        'displayFormat': '#input <?= lang('streams:textarea_limited.info_characters') ?> | #left <?= lang('streams:textarea_limited.info_characters_left') ?> | #words <?= lang('streams:textarea_limited.info_words') ?>'
    };
    $('<?= "#{$id}" ?>').textareaCount(options);
</script>