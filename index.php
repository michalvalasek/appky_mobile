<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	
	<title>Appky Mobile</title>
	
	<meta name="description" content="Mobilná verzia webu appky.sk">
	<meta name="author" content="Michal Valášek">
	
	<!-- Mobile viewport optimization http://goo.gl/b9SaQ -->
	<meta name="HandheldFriendly" content="True">
	<meta name="MobileOptimized" content="320"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<!--iOS web app, deletable if not needed -->
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<link rel="apple-touch-startup-image" href="img/l/splash.png">
	
	<!-- Mobile IE allows us to activate ClearType technology for smoothing fonts for easy reading -->
	<meta http-equiv="cleartype" content="on">
	
	<!-- Main Stylesheet -->
	<link rel="stylesheet" href="css/style.css?v=1">
</head>
<body>
	
	<header>
		<h1>Appky.sk Mobile</h1>
	</header>
	
	<div id="test"></div>
	
	<section id="main-content">
	</section>
	
	<footer>
		<p>By <a href="http://michalvalasek.com" title="Homepage">Michal Valášek</a> for appky.sk</p>
	</footer>
	
	<script type="text/template" id="template_AppListView">
		<div id="app-list"></div>
		<button class="load-more">Load more</button>
	</script>
	
	<script type="text/template" id="template_AppListItemView">
			<div class="app-image">
				<img src="<%= pic_url %>" width="100" />
			</div>
			<div class="app-info">
				<h3><%= title %></h3>
				<p><%= new Array(parseInt(stars) + 1).join("★") %><%= Array(5-parseInt(stars) + 1).join("☆") %></p>
				<p><%= category_name %></p>
				<p>By <%= company %></p>
			</div>
			<div class="clearfix"></div>
	</script>
	
	<script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
	<script src="http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.1.7/underscore-min.js"></script>
	<script src="http://cdnjs.cloudflare.com/ajax/libs/backbone.js/0.5.1/backbone-min.js"></script>
	<script>
	(function($){
		
		App = Backbone.Model.extend({});
		
		AppCollection = Backbone.Collection.extend({
			model: App,
			page_size: 3,
			url: 'proxy.php?method=getLatestApplications',
			parse: function(response) {
				return response.applications;
			},
			loadMore: function() {
				var url = (_.isFunction(this.url)) ? this.url() : this.url;
				var tmp = this.url;
				this.url = url + '&offset=' + this.length + '&limit=' + this.page_size;
				var self = this;
				$.getJSON(this.url, function(resp) {
					self.add(self.parse(resp));
					self.trigger("refresh");
				});
				this.url = tmp;
			}
		});
		
		var IndexView = Backbone.View.extend({
			template: _.template($('#template_AppListView').html()),
			initialize: function() {	
				_.bindAll(this,"render","handleLoadMore");
				this.collection.loadMore();
			},
			events: {
				'click .load-more': 'handleLoadMore'
			},
			render: function(){
				$(this.el).html(this.template());
				var listView = new AppListView({collection: this.collection});
				$('#app-list').html(listView.el);
			},
			handleLoadMore: function() {
				this.collection.loadMore();
			}
		});
				
		var AppListView = Backbone.View.extend({
			//template: _.template($('#template_AppListView').html()),
			//tagName: 'div',
			//id: 'app-list',
			initialize: function() {
				_.bindAll(this,"render");
				this.collection.bind("refresh", this.render);
			},
			render: function() {
				$(this.el).empty();
				var els = [];
				this.collection.each(function(model){
					var view = new AppListItemView({model: model});
					els.push(view.render().el);
				});
				$(this.el).append(els);
				
				return this;
			}
		});
		
		var AppListItemView = Backbone.View.extend({
			tagName: 'div',
			className: 'app-list-item',
			template: _.template($('#template_AppListItemView').html()),
			initialize: function() {
				_.bindAll(this,"render","handleClick");
			},
			events: {
				'click': 'handleClick'
			},
			render: function() {
				$(this.el).html(this.template(this.model.toJSON()));
				//$(this.el).html('<p>test</p>');
				return this;
			},
			handleClick: function(){
				alert('Show details: '+this.model.get('application_id'));
			}
		});
		
		var AppDetailsView = Backbone.View.extend({});
		
		var AppRouter = Backbone.Router.extend({
			initialize: function(){
				applications = new AppCollection;
			},
			routes: {
	            "/application/view/:id": "applicationDetails",
	            "": "index" // Backbone will try match the route above first
	        },
	        applicationDetails: function( id ) {
				var detailsView = new AppDetailsView();
			},
	        index: function( actions ){
				var indexView = new IndexView({collection: applications, el: '#main-content'});
				indexView.render();
	        }
	    });
		var app_router = new AppRouter;
		Backbone.history.start();
				
	})(jQuery);
	</script>
</body>
</html>