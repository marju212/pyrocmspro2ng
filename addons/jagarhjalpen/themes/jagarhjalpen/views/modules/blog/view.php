

{{ post }}
<div class="page-header" id="banner">
    <div class="row">
        <div class="col-md-9 col-md-offset-3">
            <h1>{{ title }}</h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 meta">
        <div class="date text-muted">{{ helper:date timestamp=created_on }}</div>

        <div class="author">
            {{ helper:lang line="blog:written_by_label" }}
            <span><a href="{{ url:site }}user/{{ created_by:user_id }}">{{ created_by:display_name }}</a></span>
        </div>

        {{ if category }}
        <div class="category">
            <abbr title="">Category:</abbr> <a href="{{ url:site }}blog/category/{{ category:slug }}">{{ category:title }}</a>
        </div>
        {{ endif }}

        {{ if keywords }}
        <div class="keywords">
            {{ keywords }}
            <a href="{{ url:site }}blog/tagged/{{ keyword }}" class="badge">{{ keyword }}</a>
            {{ /keywords }}
        </div>
        {{ endif }}
    </div>
    <div class="col-md-9 post">
        <div class="body">
            {{ body }}
        </div>
    </div>
</div>
{{ /post }}


    <?php if (Settings::get('enable_comments')): ?>

<div id="comments" class="row">
    <div class="col-md-9 col-md-offset-3">
        <hr/>
        <div id="existing-comments">
            <h4><?php echo lang('comments:title') ?></h4>
            <?php echo $this->comments->display() ?>
        </div>

        <?php if ($form_display): ?>
            <?php echo $this->comments->form() ?>
        <?php else: ?>
        <?php echo sprintf(lang('blog:disabled_after'), strtolower(lang('global:duration:'.str_replace(' ', '-', $post[0]['comments_enabled'])))) ?>
        <?php endif ?>
    </div>
</div>

<?php endif ?>
