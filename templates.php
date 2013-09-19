<script type="text/tmpl" id="home-view">
    <div class="post-list">

         {[ if(posts.length) { ]}
            {[ _.each(posts, function(post){ ]}

                <article class="post">
                    <h2>
                        <a data-id="{{ post.get('ID') }}" data-url="internal" href="{{ post_link( post ) }}">{{ post.get("title") }}</a>
                    </h2>

                    <div class="entry-meta">

                    </div>

                    <div class="entry-content">{{ post.get('content') }}</div>
                </article>

            {[ }); ]}

        {[ } else { ]}

            <h2>No posts found</h2>

        {[ } ]}

        {[ if ( pages > 1 ) { ]}
            <div class="pagination pagination-centered"><ul>
            {[ for (var i = 1; i <= pages; i++) { ]}

                {[ className = (i == currentPage) ? ' class="active"' : '' ]}

                <li{{ className }}><a href="#/p/{{ i }}">{{ i }}</a></li>

            {[ } ]}

            </div></ul>

        {[ } ]}
    </div>
</script>

<script type="text/tmpl" id="single-post-view">
    <div class="post-list">
        <article class="post">
            <h2>{{ post.get("title") }}</h2>

            <div class="entry-meta">
                Written by {{ post.get('author').get('name') }}
                on {{ post.get('date') }}
            </div>

            <div class="entry-content">{{ post.get('content') }}</div>

            <div id="comments">Loading...</div>
        </article>
    </div>
</script>

<script type="text/tmpl" id="comments-view">
    {[ if (comments.length) { ]}
        <h2 id="comments-title">{{ comments.length }} comments!</h2>

            <ol class="commentlist">
            {[ _.each(comments, function(comment){ ]}

                <li id="li-comment-{{ comment.get('ID') }}">
                    <article class="comment">
                        <footer>
                            <div class="comment-author vcard">
                                <div class="comment-avatar"><img class="avatar" src="{{ comment.get('author').avatar }}" alt="avatar" /></div>

                                <cite class="fn">{{ comment.get('author').name }} </cite> <span class="says">says:</span>
                            </div>
                        </footer>

                        <div class="comment-content">{{ comment.get('content') }}</div>
                    </article>
                </li>

            {[ }); ]}
            </ol>

            </div></ul>

    {[ } else { ]}
        <h2 id="comments-title">No comments found!</h2>
    {[ } ]}
</script>