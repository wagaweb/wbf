# WBF Google Map Wrapper

A class to easily render a Google Map into a container with clusterized markers and location search feature.

## Usage

- Activate Google Map Javascript API 
- Activate Google Map Geocoding API (if you need to use the location search feature).
- Register your API key to WBF:

```php
add_filter('wbf/js/libs/google_map/api', function($key){
	$key = 'your-api-key';
	return $key;
});
```

- Enqueue the script

```php
add_action("wp_enqueue_scripts", function(){
	wp_enqueue_script('wbfgmap');
});
```

- Create the HTML

```php
<?php
$markers = [
	[
		'address' => 'Foobar',
		'lat' => '...',
		'lng' => '...',
		'title' => 'Marker #1',
        'link' => '...'
	],
	[
		'address' => 'Baz',
		'lat' => '...',
		'lng' => '...',
		'title' => 'Marker #2',
        'link' => '...'
	],
	[
	    'address' => 'Foobarbaz',
	    'lat' => '...',
	    'lng' => '...',
        'title' => 'Marker #3',
        'link' => '...'
    ]
];
?>
<div class="markers" style="display: none;">
    <?php foreach($markers as $marker): ?>
        <?php
        $address = $marker['address'];
        $lat = (float) $marker['lat'];
        $lng = (float) $marker['lng'];
        $title = $marker['title'];
        $link = $marker['link'];
        ?>
        <div class="marker" data-lat="<?php echo $lat; ?>" data-lng="<?php echo $lng; ?>" data-title="<?php echo $title; ?>">
            <!-- This HTML (if present) will inserted into an infowindow -->
            <h6><a href="<?php echo $link; ?>"><?php echo $title; ?></a></h6>
            <p class="address"><?php echo $address; ?></p>
        </div>
    <?php endforeach; ?>
</div>
<div class="google-map"></div><!-- .google-map -->
```

- And the Javascript

```html
<script type="text/javascript">
    jQuery(window).on('load', function(){
        var gmap = new WBFGoogleMap(jQuery('.google-map'));
        gmap.renderMap().addMarkers(jQuery('.markers')).centerMap();
    });
</script>
```

If you want to use the location search feature:

- Add input and button

```html
<input type="text" class="form-control" placeholder="Insert a location" value="" name="gmap-map-search-address" data-gmap-map-search-field />
<button class="btn btn-primary" name="gmap-map-search-submit" data-gmap-map-search-button>Search</button>
```

- Edit the Javascript

```html
<script type="text/javascript">
    jQuery(window).on('load', function(){
        var gmap = new WBFGoogleMap(jQuery('.google-map'));
        gmap.renderMap().addMarkers(jQuery('.markers')).centerMap().bindSearch(jQuery('[data-gmap-map-search-field]'),jQuery('[data-gmap-map-search-button]'));
    });
</script>
```

## Methods

`renderMap(<array render_args>)`

Renders a map into the container specified during object instantiation. `render_args` can be undefined. The default values are:

```javascript
    var defaults_args = {
        zoom: 16,
        draggable: true,
        scrollwheel: false,
        center: new google.maps.LatLng(0, 0),
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
```

`addMarkers(<jQuery element: container>,<bool: clusterize>)`

Add the markers specified into the container to the map. If clusterize is TRUE, then they will be grouped when the zoom is far enough.

Markers must have `.marker` class (see the code above).

`bindSearch(<jQuery element: input>,<jQuery element: button>,<integer zoom_after_search>)`

Bind a input\button pair to make search queries on the map. `zoom_after_search` dictates the map zoom when the searched location is found (default to 13).