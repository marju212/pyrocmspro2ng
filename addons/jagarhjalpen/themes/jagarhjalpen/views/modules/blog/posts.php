<div class="page-header" id="banner">
    <div class="row">
        <div class="col-lg-12">
            <h1>Blog</h1>
            <p class="lead"></p>
        </div>
    </div>
</div>

{{ if posts }}

	{{ posts }}

		<div class="post">

			<h3><a href="{{ url }}">{{ title }}</a></h3>

			<div class="meta">

				<div class="date text-muted">{{ helper:date timestamp=created_on }}</div>
            	
				{{ if category }}
				<div class="category">
					<a href="{{ url:site }}blog/category/{{ category:slug }}">{{ category:title }}</a>
				</div>
				{{ endif }}

			</div>

			<div class="preview">
				{{ preview }}
			</div>

			<p><a href="{{ url }}" class="btn btn-default pull-right">{{ helper:lang line="blog:read_more_label" }}</a></p>
            <br/>
            <hr/>
		</div>

	{{ /posts }}

	{{ pagination }}

{{ else }}
	
	{{ helper:lang line="blog:currently_no_posts" }}

{{ endif }}