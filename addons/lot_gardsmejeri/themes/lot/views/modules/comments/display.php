<?php if ($comments): ?>

    <?php foreach ($comments as $item): ?>

        <ol>
            <li id="comment-1">
                 <?php echo gravatar($item->user_email, 60) ?>
            
               
       
             <div class="comment">
                <h5><a href="#" rel="external nofollow" class="url"><?php echo $item->user_name ?></a></h5>
                   
                
               <span class="date"><?php echo format_date($item->created_on) ?></span>
                 
               <p>
                   
              
                    <?php if (Settings::get('comment_markdown') and $item->parsed): ?>
                        <?php echo $item->parsed ?>
                    <?php else: ?>
                        <?php echo nl2br($item->comment) ?>
                    <?php endif ?>
                         </p>
           </div>
            </li>
        </ol>
    <?php endforeach ?>

<?php else: ?>
    <p><?php echo lang('comments:no_comments') ?></p>
<?php endif ?>

