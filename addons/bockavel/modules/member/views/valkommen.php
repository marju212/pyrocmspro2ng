{{ streams:cycle stream="article" namespace="pages" where="`id` = 10"}}
<div class="panel article">
    <div class="panel-body">
        <h1>{{ artikelrubrik }}</h1>
    </div>
    {{ if ingress }}
    <div class="panel-body">
        <b>{{ ingress }}</b>
    </div>
    {{ endif }}
    <div class="panel-body">
        {{ artikeltext }}
    </div>
</div>
{{ /streams:cycle }}

