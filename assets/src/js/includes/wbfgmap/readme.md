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
		'title' => 'Marker #1'
	],
	[
		'address' => 'Baz',
		'lat' => '...',
		'lng' => '...',
		'title' => 'Marker #2'
	],
	[
	    'address' => 'Foobarbaz',
	    'lat' => '...',
	    'lng' => '...',
        'title' => 'Marker #3'
    ]
];
?>
<div class="markers" style="display: none;">
    <?php foreach($markers as $marker): ?>
        <?php
        $address = $marker['address'];
        $lat = (float) $marker['lat'];
        $lng = (float) $marker['lng'];
        ?>
        <div class="marker" data-lat="<?php echo $lat; ?>" data-lng="<?php echo $lng; ?>" data-title="<?php echo get_the_title(); ?>">
            <h6><a href="<?php the_permalink() ?>"><?php the_title() ?></a></h6>
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
<input type="text" class="form-control" placeholder="Inserisci una city" value="" name="gmap-map-search-address" data-gmap-map-search-field />
<button class="btn btn-primary" name="reseller-map-search-submit" data-gmap-map-search-button>Search</button>
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
