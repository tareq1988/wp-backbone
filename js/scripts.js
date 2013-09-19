function post_link(post) {
    return '#/' + post.get('link').replace(wedevsBackbone.base, '');
}

(function($){

    $(function() {

        $.fn.center = function () {
            this.css("top", Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) + $(window).scrollTop()) + "px");
            this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) + $(window).scrollLeft()) + "px");

            return this;
        }

        var scrollToTop = function() {
            $('html, body').animate({
                scrollTop: $("#masthead").offset().top
            }, 100);
        };

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
                scrollToTop();
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
                scrollToTop();
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
})(jQuery);

