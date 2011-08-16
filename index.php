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
	
	<section id="main-content">
		<div id="applications"></div>
		<button class="load-more">Load more</button>
	</section>
	
	<footer>
		<p>By Michal Valasek for appky.sk</p>
	</footer>
	
	<script type="text/template" id="template_defaultAppView">
		<div class="app-image">
			<img src="<%= pic_url %>" width="100" />
		</div>
		<div class="app-info">
			<h3><%= title %></h3>
			<p><%= company %></p>
		</div>
		<div class="clearfix"></div>
	</script>
	
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
	<script src="http://ajax.cdnjs.com/ajax/libs/underscore.js/1.1.4/underscore-min.js"></script>
	<script src="http://ajax.cdnjs.com/ajax/libs/backbone.js/0.3.3/backbone-min.js"></script>
	<script>
	(function($){
		
		var API_KEY = '<?=$_SERVER["APPKY_API_KEY"]?>';
		
		App = Backbone.Model.extend({});
		
		AppCollection = Backbone.Collection.extend({
			model: App,
			page_size: 3,
			url: 'http://192.168.1.13/appky_yii/api/getLatestApplications/?key='+API_KEY,
			//url: 'http://localhost/appky_yii/api/getLatestApplications/?key='+API_KEY,
			parse: function(response) {
				return response.applications;
			},
			loadMore: function() {
				var url = (_.isFunction(this.url)) ? this.url() : this.url;
				var tmp = this.url;
				this.url = url + '&offset=' + this.length + '&limit=' + this.page_size;
				var self = this;
				Backbone.sync('read', this, function(resp) {
					self.add(self.parse(resp));
				});
				this.url = tmp;
			}
		});
		
		DefaultAppView = Backbone.View.extend({
			tagName: 'div',
			className: 'app-preview',
			template: _.template($('#template_defaultAppView').html()),
			events: {
				'click': 'handleShowDetails'
			},
			render: function() {
				$(this.el).html(this.template(this.model.toJSON()));
				return this;
			},
			handleShowDetails: function() {
				alert('App Details!');
			}
		});
	    
		AppkyAppView = Backbone.View.extend({
			el: $('body'),
			
			initialize: function(options) {
				var p = this;
				options || (options = {});

				this.collection = new AppCollection();

				this.collection.bind('add', function(app){ p.addOne(app, this); });
				this.collection.bind('refresh', function(){ p.addAll(this); });
				this.collection.bind('all', function(){ p.render(this); });

				this.collection.loadMore();
			},
			
			events: {
				'click .app-preview': 'handleShowDetails',
				'click .load-more': 'handleLoadMore'
			},
			
			addOne: function(app, collection) {
				var view = new DefaultAppView({model: app});
				var app_el = $(view.render().el);

				$('#applications').append(app_el);
			},
			
			addAll: function(collection) {
				var p = this;
				collection.each(function(tweet) {
					p.addOne(tweet, collection);
				});
			},
			
			render: function() {},
			
			handleLoadMore: function() {
				this.collection.loadMore();
			}
		});
	
		appky = new AppkyAppView();
				
	})(jQuery);
	</script>
</body>
</html>