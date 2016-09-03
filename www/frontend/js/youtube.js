var API_KEY = 'AIzaSyDMEgyECScLdhmGq-YcNFFQ2HFawNstPCw';
var MAX_RESULT = 6;
var DEFAULT_SEARCH = 'grassland';


// Extension to get unique array
Array.prototype.unique = function(){

   	var u = {}, a = [];

   	for(var i = 0, l = this.length; i < l; ++i){

    	if(u.hasOwnProperty(this[i])) {
        	continue;
      	}

      	a.push(this[i]);
      	u[this[i]] = 1;
  	}

   	return a;
}

// Used in local storage to store the search data
var storage = {

	pushsearchkey: function(searchkey) {
		// Put this in local storage
		var searchkeys = JSON.parse(localStorage.getItem("searchkeys")) || [];

		searchkeys.push(searchkey);
		searchkeys = searchkeys.unique();

		localStorage.setItem('searchkeys', JSON.stringify(searchkeys));
	},

	getlastsearch: function() {

		var searchkeys = JSON.parse(localStorage.getItem("searchkeys")) || [DEFAULT_SEARCH];

		return searchkeys.pop();
	},

	getrecentsearches: function(count) {

		// Defaults
		count = count || 5;
		var searchkeys = JSON.parse(localStorage.getItem("searchkeys")) || [];

		// Reverse the array so we get the recent searches
		searchkeys.reverse();

		recentSearches = searchkeys.slice(0, count);

		return recentSearches.join(", ");
	}
}

// Used in rendering the thumbnails on screen
var templating = {

	render: function (data) {

		// Get the mustache template and render the video
		$.get('views/video.mst', function(template) {
		    var rendered = Mustache.render(template, data);
		    $('#results').html(rendered);
	 	});
	}
}

// Interfaces with youtube and get the relevent data
var youtube = (function() {

	// Private functions
	function makeRequest(request) {

		// Get the recent searches
		$('#recentsearch').html('Recent Searches: ' + storage.getrecentsearches());

		request.execute(function(response) {

			var data = response.result || [];

			// Store the prevous and next tokens
			$('#previous').attr('data-id', data.prevPageToken);
			$('#next').attr('data-id', data.nextPageToken);

			// Render the videos
			templating.render(data);
		});

	}


	// Public methods
	return {

		paginate: function() {

			var token = $(this).attr('data-id');
			var q = $('#query').val();
			q = q.length == 0 ? DEFAULT_SEARCH : q;

			var request = gapi.client.youtube.search.list({
				q: q,
				maxResults: MAX_RESULT,
				pageToken: token,
				part: 'snippet'
			});

			makeRequest(request);
		},

		search: function(e) {

			var q = $('#query').val();

			var request = gapi.client.youtube.search.list({
				q: q,
				maxResults: MAX_RESULT,
				part: 'snippet'
			});

			// Put this in local storage
			storage.pushsearchkey(q);

			makeRequest(request);
		},

		init: function() {

			// Set the API key
			gapi.client.setApiKey(API_KEY);

			// Load the youtube API and on promise load some default search
			gapi.client.load('youtube', 'v3').then(function(){

				// get the last searched
				var recommendation = storage.getlastsearch();

				var request = gapi.client.youtube.search.list({
					q: recommendation,
					maxResults: MAX_RESULT,
					part: 'snippet'
				});

				makeRequest(request);
			});
		}
	}

})();

// Environment setup and event bindings on load
$(function(){

	gapi.load("client", youtube.init);

	// Bind the events
	$(document).on('keyup', '#query', function(e) {
		// Ignore anything keyboard events other than enter
		if(e.keyCode == 13)
			$('#search').click();
	});

	$(document).on('click', '#search', youtube.search);
	$(document).on('click', '#next', youtube.paginate);
	$(document).on('click', '#previous', youtube.paginate);

});