{{ post }}

<div class="post">					
    <h3><a href="{{ url }}">{{ title }}</a></h3>
    <div class="meta">
        <div class="date"><span><span class="icon general">i</span> 
                {{ helper:lang line="blog:posted_label" }}
                {{ helper:date timestamp=created_on }}</span>
        

        <span class="comments"> <span class="icon social_misc">w</span><a href="" title="">2 Comments</a></span>  
        <span class="author"> <span class="icon social_misc">x</span>{{ helper:lang line="blog:written_by_label" }} <a href="{{ user:username user_id=created_by }}">{{ user:display_name user_id=created_by }}</a></span>
        </div>
        {{ if category }}
        <div class="category">
          <span class="icon social_misc">t</span>  {{ helper:lang line="blog:category_label" }}:
            <span><a href="blog/category/{{ category:slug }}">{{ category:title }}</a></span>
        </div>
        {{ endif }}

        {{ if keywords }}
        <div class="keywords">
            {{ keywords }}
            <span><a href="blog/tagged/{{ name }}">{{ name }}</a></span>
            {{ /keywords }}
        </div>
        {{ endif }}

    </div>
   

	<div class="body">
		{{ body }}
	</div>

</div>

{{ /post }}

<?php if (Settings::get('enable_comments')): ?>

<div id="comments">

	<div id="existing-comments">
		<h4><?php echo lang('comments:title') ?></h4>
		<?php echo $this->comments->display() ?>
	</div>

	<?php if ($form_display): ?>
		<?php echo $this->comments->form() ?>
	<?php else: ?>
	<?php echo sprintf(lang('blog:disabled_after'), strtolower(lang('global:duration:'.str_replace(' ', '-', $post->comments_enabled)))) ?>
	<?php endif ?>
</div>




<?php endif ?>
