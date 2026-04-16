<div class="panel article">
    <div class="panel-body">
        <h1> {{ title }} </h1>
    </div>
    <div class="panel-body">
        <b> {{ingress}} </b>
    </div>
    <div class="panel-body">
        {{ wysiwyg }}
    </div>
    <div class="panel-body panel-footer-article">
        <ul class="article-author">
            <li>Publicerad {{ helper:date timestamp=publish_date }}</li>
            <li>Av {{ created_by:display_name }}</li>
        </ul>

        <div class="pull-right fb-like" data-href="http://www.svenskagetavelsforbundet.se/" data-layout="button_count" data-action="like" data-show-faces="false" data-share="true"></div>
    </div>


</div>
