'use strict';

/**
 * WBFGoogleMap class
 *
 * @constructor
 * @param {function} $el (DOM element selected via jQuery)
 */
function WBFGoogleMap($el){
    this.$el = $el;
}

/**
 * Render the map
 *
 * @param {array} render_args
 * @returns {WBFGoogleMap}
 */
WBFGoogleMap.prototype.renderMap = function(render_args){
    var defaults_args = {
        zoom: 16,
        draggable: true,
        scrollwheel: false,
        center: new google.maps.LatLng(0, 0),
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    if(typeof args !== 'undefined'){
        for (var prop in defaults_args) {
            if (prop in render_args) { continue; }
            render_args[prop] = defaults_args[prop];
        }
    }else{
        render_args = defaults_args;
    }

    this.map = new google.maps.Map(this.$el[0],render_args);

    return this;
};

/**
 * Populates the map with markers defined in $markers_container. Markers must have .marker class.
 *
 * @param {function} $markers_container (DOM element selected via jQuery)
 * @param {boolean} clusterize (default: FALSE)
 * @returns {WBFGoogleMap}
 */
WBFGoogleMap.prototype.addMarkers = function($markers_container, clusterize){
    var $markers = $markers_container.find('.marker'),
        self = this;

    if(typeof clusterize === 'undefined'){
        clusterize = false;
    }

    if($markers.length > 0){
        this.map.markers = [];

        $markers.each(function () {
            self.addMarker(jQuery(this));
        });

        if(clusterize){
            new MarkerClusterer(this.map, this.map.markers);
        }
    }else{
        console.log('WBFGoogleMap: No markers found.');
    }

    return this;
};

/**
 * Adds a marker to the map
 *
 * @param {function} $marker (DOM element selected via jQuery)
 */
WBFGoogleMap.prototype.addMarker = function($marker){
    // var
    var latlng = new google.maps.LatLng($marker.attr('data-lat'), $marker.attr('data-lng')),
        icon   = $marker.attr("data-icon");

    // create marker
    var marker = new google.maps.Marker({
        position: latlng,
        map: this.map,
        icon: icon || undefined
    });

    this.map.markers.push(marker);

    if($marker.html()){
        var infoWindow = new google.maps.InfoWindow({
            content: $marker.html()
        });
        google.maps.event.addListener(marker, 'click', () => {
            infoWindow.open(this.map,marker);
        })
    }
};

/**
 * Center the Map within the markers
 * @returns {WBFGoogleMap}
 */
WBFGoogleMap.prototype.centerMap = function(){
    var bounds = new google.maps.LatLngBounds();

    if(typeof this.map.markers !== "undefined"){
        jQuery.each(this.map.markers, (i, marker) => {
            var latlng = new google.maps.LatLng(marker.position.lat(),marker.position.lng());
            bounds.extend(latlng);
        });

        if(this.map.markers.length === 1){
            this.map.setCenter(bounds.getCenter());
            this.map.setZoom(16);
        }else if(this.map.markers.length > 1){
            this.map.fitBounds(bounds);
        }
    }else{
        console.log("WBFGoogleMap: Unable to center the map to markers.");
    }
    return this;
};

/**
 * Binds a location search event to the map
 *
 * @param {function} $input (DOM element selected via jQuery)
 * @param {function} $button (DOM element selected via jQuery)
 * @param {integer} zoom_after_search
 * @returns {WBFGoogleMap}
 */
WBFGoogleMap.prototype.bindSearch = function($input,$button,zoom_after_search){
    let self = this;
    if(typeof zoom_after_search === 'undefined'){
        zoom_after_search = 13;
    }
    $input.keypress(function(e){
        if (e.which === 13){
            $button.trigger("click");
        }
    });
    $button.on("click",function(){
        let searchAddress = $input.val();
        if(typeof(searchAddress) !== "undefined" && searchAddress !== ""){
            let geocoder = new google.maps.Geocoder();
            geocoder.geocode({'address': searchAddress}, function(result,status){
                if(status === google.maps.GeocoderStatus.OK && typeof(self.map) !== "undefined"){
                    self.map.setCenter(result[0].geometry.location);
                    self.map.setZoom(zoom_after_search);
                }else{
                    console.log("WBFGoogleMap: Geocode was not successful for the following reason: "+status);
                }
            });
        }
    });
    return this;
};