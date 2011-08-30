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
	
	<!-- BASIC STURCTURE -->
	<header>
		<h1>Appky.sk Mobile</h1>
	</header>
	<section id="main-content"></section>
	<footer>
		<p>By <a href="http://michalvalasek.com" title="Homepage">Michal Valášek</a> for appky.sk</p>
	</footer>
	
	
	<!-- TEMPLATES -->
	
	<script type="text/template" id="template_IndexView">
		<div id="app-list">
			<% _.each(apps, function(app) { %>
				<div class="app-list-item" id="app-<%= app.application_id %>">
					<div class="app-image">
						<img src="<%= app.pic_url %>" width="100" />
					</div>
					<div class="app-info">
						<h3><%= app.title %></h3>
						<p><%= new Array(parseInt(app.stars) + 1).join("★") %><%= Array(5-parseInt(app.stars) + 1).join("☆") %></p>
						<p><%= app.category_name %></p>
						<p>By <%= app.company %></p>
					</div>
					<div class="clearfix"></div>
				</div>
			<%});%>
		</div>
		<button class="load-more wide">Load more</button>
	</script>
	
	<script type="text/template" id="template_AppDetailsView">
		<div class="app-details">
			<div class="app-image">
				<img src="<%= pic_url %>" width="100" />
			</div>
			<h2><%= title %></h2>
			<p><strong>Kategória:</strong> <%= category_name %></p>
			<p><strong>Autor:</strong> <%= company %></p>
			<p><strong>Hodnotenie používateľov:</strong> <%= new Array(parseInt(stars) + 1).join("★") %><%= Array(5-parseInt(stars) + 1).join("☆") %></p>
			<div class="app-description">
				<p class="app-description-label">Popis aplikácie:</p>
				<%= description %>
			</div>
		</div>
		<button class="back wide">Späť</button>
	</script>
	
	<!-- Javascripts -->
	<script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
	<script src="http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.1.7/underscore-min.js"></script>
	<script src="http://cdnjs.cloudflare.com/ajax/libs/backbone.js/0.5.1/backbone-min.js"></script>
	<script>
	(function($){
		
		/* Models and Collections */
		
		App = Backbone.Model.extend({
			initialize: function(options) {
				console.log('App:initialize');
				if (options.application_id) {
					this.loadById(options.application_id);
				}
			},
			loadById: function(application_id) {
				var self = this;
				$.getJSON('proxy.php?method=getApplicationDetails&id='+application_id, function(resp) {
					self.set(resp);
				});
			}
		});
		
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
				console.log('AppCollection:loadMore: original length = '+self.length);
				$.getJSON(this.url, function(resp) {
					self.add(self.parse(resp));
					self.trigger("refresh");
					console.log('AppCollection:loadMore: current length = '+self.length);
				});
				this.url = tmp;
			},
			refresh: function() {
				console.log('AppCollection:refresh');
				this.trigger("refresh");
			}
		});
		


		/* Views */
		
		var AppDetailsView = Backbone.View.extend({
			template: _.template($('#template_AppDetailsView').html()),
			initialize: function() {
				_.bindAll(this,"render","handleBack");
				this.model.bind("change", this.render);
				//app_router.navigate("/a/"+app_id);
			},
			events: {
				'click .back': 'handleBack'
			},
			render: function() {
				$(this.el).html(this.template(this.model.toJSON()));
				return this;
			},
			handleBack: function() {
				history.go(-1);
				//window.location = "#/";
			}
		});
		
		var IndexView = Backbone.View.extend({
			template: _.template($('#template_IndexView').html()),
			initialize: function() {
				_.bindAll(this,"render","handleMore","handleShow");
				this.collection.bind("refresh", this.render);
			},
			events: {
				'click .load-more': 'handleMore',
				'click .app-list-item': 'handleShow'
			},
			render: function() {
				$(this.el).html(this.template( {apps: this.collection.toJSON()} ));
				return this;
			},
			handleMore: function() {
				this.collection.loadMore();
			},
			handleShow: function(evt) {
				var app_id = evt.currentTarget.id.substr(4);
				window.location = "#/a/"+app_id;
			}
		});
		


		/* Routers */
		
		var AppRouter = Backbone.Router.extend({
			initialize: function(){
				applications = new AppCollection;
				index_view = new IndexView({collection: applications, el: '#main-content'});
				
				applications.loadMore(); // Load first page of apps
			},
			routes: {
				"/a/:id": "applicationDetails",
				"/": "index",
				"": "index" // Backbone will try match the route above first
	        },
	        applicationDetails: function( id ) {
				var app = new App({application_id:id});
				var detailsView = new AppDetailsView({model: app, el: '#main-content'});
			},
			index: function() {
				//var view = new Index2View({collection: applications, el: '#main-content'});
				index_view.render();
			}
	    });
		var app_router = new AppRouter;
		Backbone.history.start();
				
	})(jQuery);
	</script>
</body>
</html>