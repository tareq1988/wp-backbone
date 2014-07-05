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
                // console.log('initializing single post view');
            },

            events: {
                'submit #commentform': 'submitComment'
            },

            submitComment: function(e) {
                e.preventDefault();

                var errors = [],
                    self = this,
                    hasError = false,
                    form = $(e.currentTarget),
                    comment = form.find('textarea#comment').val();

                // remove previous errors
                form.find('.error').remove();
                var invalid = ['.comment-form-author', '.comment-form-email', '.comment-form-comment'];
                $.each(invalid, function(i, item) {
                    $(item).removeClass('invalid');
                });

                if (wedevsBackbone.loggedin === 'no') {
                    var email = form.find('input#email').val();
                    var author = form.find('input#author').val();

                    if (author === '' ) {
                        hasError = true;
                        form.find('.comment-form-author')
                            .addClass('invalid')
                            .append('<div class="error">Error: please type a comment.</div>');
                    }

                    if( email === '' ) {
                        hasError = true;
                        form.find('.comment-form-email')
                        .addClass('invalid')
                        .append('<div class="error">Error: please type a comment.</div>');
                    }
                }

                if (comment === '' ) {
                    hasError = true;
                    form.find('.comment-form-comment')
                    .addClass('invalid')
                    .append('<div class="error">Error: please type a comment.</div>')
                }

                if (!hasError) {
                    var data = form.serialize() + '&action=wpbb_new_comment';
                    $.post(wedevsBackbone.ajaxurl, data, function(resp) {
                        if (resp.success === false) {
                            alert(resp.data);
                        } else {
                            // re render the comment view
                            new CommentView().initComments(self.model);
                        }
                    });
                }
            },

            fetchPost: function(post_id, type) {
                self = this;
                self.type = type;

                this.model = new wp.api.models.Post({ 'ID': post_id });
                this.model.fetch({
                    success: function() {
                        self.render();
                    }
                });
            },

            render: function() {
                if (this.type === 'post') {
                    var template = $('#single-post-view').html();
                } else {
                    var template = $('#single-page-view').html();
                }

                template = _.template(template, {
                    post: this.model
                });

                $(this.el).html(template);
                PubSub.trigger('post:single:' + this.type, this.model);
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
                return WP_API_Settings.root + '/posts/' + this.post.id + '/comments';
            },

            comparator: function(comment) {
                return comment.get('date');
            },

            initialize: function(post) {
                this.post = post;
            }
        });

        var CommentView = Backbone.View.extend({

            initialize: function() {
                this.el = '#comments';

                PubSub.once('post:single:post', this.initComments, this);
            },

            initComments: function(model) {
                var self = this;
                self.post = model;

                this.collection = new Comments(model);
                this.collection.fetch({
                    success: function() {
                        self.render();
                    }
                });
            },

            render: function() {
                var template = $('#comments-view').html();
                template = _.template(template, {
                    comments: this.collection.models,
                    post: this.post
                });

                $(this.el).html(template);
            }
        });

        var AppRouter = Backbone.Router.extend({
            routes: {
                '/': 'home',
                'posts/:id/*slug': 'singlePost',
                'page/:id/*slug': 'singlePage',
                'page/:id/*slug/*slug': 'singlePage',
                'p/:id': 'paged',
                '*actions': 'home'
            },

            singlePost: function(post_id, slug) {
                new singlePostView().fetchPost(post_id, 'post');
                new CommentView();
            },

            singlePage: function(post_id) {
                new singlePostView().fetchPost(post_id, 'page');
            },

            home: function() {
                new homeView().fetchPosts(1);
            },

            paged: function(page_id) {
                new homeView().fetchPosts(page_id);
            }

        });

        var App = new AppRouter();
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

