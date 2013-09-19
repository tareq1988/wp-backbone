<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package _bootstraps
 * @package _bootstraps - 2013 1.0
 */
get_header();
?>

<div id="primary" class="content-area span8">
    <div id="content" class="site-content" role="main">

        Loading...

    </div><!-- #content .site-content -->
</div><!-- #primary .content-area -->

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

<script type="text/javascript">
    function post_link(post) {
        return '#/' + post.get('link').replace(wedevsBackbone.base, '');
    }

    jQuery(function($) {

        jQuery.fn.center = function () {
            // this.css("position","absolute");
            this.css("top", Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) +
                                                        $(window).scrollTop()) + "px");
            this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) +
                                                        $(window).scrollLeft()) + "px");
            return this;
        }

        _.templateSettings = {
            evaluate : /\{\[([\s\S]+?)\]\}/g,
            interpolate : /\{\{([\s\S]+?)\}\}/g
            // escape: /\{\{-(.+?)\}\}/g
        };
        // var api = wp.api = wp.api || {};
        // console.log(api);

        var PubSub = {};
        _.extend(PubSub, Backbone.Events);

        var homeView = Backbone.View.extend({
            el: '#content',

            initialize: function() {
                // console.log('initializing home view');
            },

            fetchPosts: function(page_id) {
                var self = this;

                this.collection = new wp.api.collections.Posts();
                this.collection.fetch({
                    data: {page: page_id},
                    success: function(a, b, c) {
                        self.totalPages = c.xhr.getResponseHeader('X-WP-TotalPages');
                        self.total = c.xhr.getResponseHeader('X-WP-Total');
                        self.currentPage = page_id;

                        // console.log(totalPages, total);
                        self.render();
                    }
                });
            },

            render: function() {
                var template = $('#home-view').html();

                template = _.template(template, {
                    posts: this.collection.models,
                    pages: this.totalPages,
                    total: this.total,
                    currentPage: this.currentPage
                });

                $(this.el).html(template);
            }
        });

        var singlePostView = Backbone.View.extend({
            el: '#content',

            initialize: function() {
                console.log('initializing single post view');
            },

            fetchPost: function(post_id) {
                self = this;

                this.model = new wp.api.models.Post({ 'ID': post_id });
                this.model.fetch({
                    success: function() {
                        self.render();
                    }
                });
            },

            render: function() {
                var template = $('#single-post-view').html();
                template = _.template(template, {
                    post: this.model
                });

                $(this.el).html(template);
                PubSub.trigger('post:single', this.model);
            }

        });

        var Comment = Backbone.Model.extend({
            idAttribute: "ID",

            defaults: {
                ID: 0,
                post: 0,
                contnt: '',
                parent: 0,
                date: '',
                links: {},
                author: {
                    name: '',
                    URL: '',
                    avatar: ''
                }
            }
        });

        var Comments = Backbone.Collection.extend({
            model: Comment,
            url: function() {
                return wpApiOptions.base + '/posts/' + this.post_id + '/comments';
            },

            initialize: function(post_id) {
                this.post_id = post_id;
            }
        });

        var CommentView = Backbone.View.extend({
            el: 'article #comments',

            initialize: function() {
                PubSub.once('post:single', this.initComments, this);
            },

            initComments: function(model) {
                var self = this;

                this.collection = new Comments(model.id);
                this.collection.fetch({
                    success: function() {
                        self.render();
                    }
                });
            },

            render: function() {
                var template = $('#comments-view').html();
                template = _.template(template, { comments: this.collection.models });
                $('article #comments').html(template);
            }
        });

        var AppRouter = Backbone.Router.extend({
            routes: {
                '/': 'home',
                'posts/:id/*slug': 'singlePost',
                'p/:id': 'paged',
                '*actions': 'home'
            },

            singlePost: function(post_id, slug) {
                new singlePostView().fetchPost(post_id);
                new CommentView();
            },

            home: function() {
                new homeView().fetchPosts(1);
            },

            paged: function(page_id) {
                new homeView().fetchPosts(page_id);
            }

        });

        new AppRouter();
        Backbone.history.start();

        $('.loading').center();

        $.ajaxSetup({
            beforeSend: function(jqXHR){
                $('.loading').show();
            },
            complete: function() {
                $('.loading').hide();
            }
        });

    });
</script>

<?php get_footer(); ?>