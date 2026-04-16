                                	
{{ if posts }}

{{ posts }}



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
    <p>{{ preview }} <a href="{{ url }}">{{ helper:lang line="blog:read_more_label" }}</a></p>
</div>




{{ /posts }}

{{ pagination }}

{{ else }}

{{ helper:lang line="blog:currently_no_posts" }}

{{ endif }}