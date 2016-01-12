function Map(identifier, isSmall) {
  var self = this;

  var global = {
      geometry_type: document.getElementById("geometry_type"),
      map: null,
      active: null,
      latitude: parseFloat(document.getElementById("latitude").value),
      longitude: parseFloat(document.getElementById("longitude").value),
      zoom: parseFloat(document.getElementById("zoom").value),
      coordinates: document.getElementById(identifier),
      deleted: document.getElementById("deleted") ? document.getElementById("deleted").value == 1 : false
  };

  this.initMap = function() {
    var zoom = global.zoom;

    if (isSmall) {
      zoom -= 2;
    }

    var map = new google.maps.Map(document.querySelector("[data-map='" + identifier + "']"), {
      zoom: zoom,
      center: {lat: global.latitude, lng: global.longitude},
      mapTypeId: isSmall ? google.maps.MapTypeId.ROADMAP : google.maps.MapTypeId.TERRAIN
    });

    global.map = map;
  }

  function clear() {
    var active = global.active;

    if (active) {
      active.setMap(null);
      global.active = null;
    }
  }

  function getCoordinates() {
    var coordinates = JSON.parse(global.coordinates.value);
    var coordinates_length = coordinates.length;
    var processed = [];

    for (var i = 0; i < coordinates_length; i++) {
      var position = coordinates[i];
      var length = position.length;

      for (var j = 0; j < length; j++) {
        var coordinate = position[j];

        processed.push({
          lat: parseFloat(coordinate[1]),
          lng: parseFloat(coordinate[0])
        });
      }
    }

    return processed;
  }

  function managePoint() {
    var coordinates = getCoordinates();
    var coordinate = coordinates[0];
    var deleted = global.deleted;

    var map = global.map;
    clear();

    var data = {
        position: coordinate,
        visible: true
    };

    if (!deleted) {
      data["draggable"] = true;
    }

    var marker = new google.maps.Marker(data);

    marker.setMap(map);

    global.active = marker;

    google.maps.event.addListener(marker, 'dragend', function(evt) {
      var latitude = evt.latLng.lat().toFixed(7);
      var longitude = evt.latLng.lng().toFixed(7);

      global.coordinates.value = "[[[" + longitude + "," + latitude + "]]]";
    });
  }

  function manageLineString() {
    var map = global.map;
    var deleted = global.deleted;

    clear();

    var coordinates = getCoordinates();

    if (coordinates.length == 1) {
      coordinates.push(coordinates[0]);
    }

    var data = {
      path: coordinates,
      strokeColor: '#ff8a17',
      strokeOpacity: 1.0,
      strokeWeight: 4
    };

    if (!deleted) {
      data["editable"] = true;
    }

    var line = new google.maps.Polyline(data);

    line.setMap(map);

    global.active = line;

    google.maps.event.addListener(line, 'rightclick', function(mev) {
      if (mev.vertex != null) {
        line.getPath().removeAt(mev.vertex);
      }
    });

    google.maps.event.addListener(line.getPath(), 'insert_at', function() {
      // New point
      manageVertices(line);
    });

    google.maps.event.addListener(line.getPath(), 'remove_at', function() {
      // Point was removed
      manageVertices(line);
    });

    google.maps.event.addListener(line.getPath(), 'set_at', function() {
      // Point was moved
      manageVertices(line);
    });
  }

  function managePolygon() {
    var map = global.map;
    var deleted = global.deleted;

    clear();

    var coordinates = getCoordinates();

    if (coordinates.length == 1) {
      coordinates.push(coordinates[0]);
    }

    var data = {
      paths: coordinates,
      strokeColor: '#ff8a17',
      strokeOpacity: 0.9,
      strokeWeight: 3,
      fillColor: '#ff8a17',
      fillOpacity: 0.35
    };

    if (!deleted) {
      data["editable"] = true;
    }

    // Construct the polygon.
    var polygon = new google.maps.Polygon(data);

    polygon.setMap(map);

    global.active = polygon;

    // Loop through all paths in the polygon and add listeners
    // to them. If we just used `getPath()` then we wouldn't
    // detect all changes to shapes like donuts.
    polygon.getPaths().forEach(function(path, index) {
      google.maps.event.addListener(path, 'insert_at', function() {
        // New point
        manageVertices(polygon);
      });

      google.maps.event.addListener(path, 'remove_at', function() {
        // Point was removed
        manageVertices(polygon);
      });

      google.maps.event.addListener(path, 'set_at', function() {
        // Point was moved
        manageVertices(polygon);
      });
    });

    google.maps.event.addListener(polygon, 'dragend', function() {
      // Polygon was dragged
    });

    var deleteNode = function(mev) {
      if (mev.vertex != null) {
        polygon.getPath().removeAt(mev.vertex);
      }
    }

    google.maps.event.addListener(polygon, 'rightclick', deleteNode);
  }

  function manageVertices(shape) {
    var vertices = shape.getPath();

    var aux = [];

    for (var i = 0; i < vertices.getLength(); i++) {
      var vertex = vertices.getAt(i);

      aux.push([
        vertex.lng().toFixed(7),
        vertex.lat().toFixed(7)
      ]);
    }

    global.coordinates.value = "[[[" + aux.join("],[") + "]]]";
  }

  function geometryTypeChange(event) {
    var geometry_type = global.geometry_type.value;

    proccessGeometryType(geometry_type);
  }

  function proccessGeometryType(geometry_type) {
    switch (geometry_type) {
      case 'Point':
        managePoint();
        break;
      case 'LineString':
        manageLineString();
        break;
      case 'Polygon':
        managePolygon();
        break;
    }
  }

  this.init = function() {
    self.initMap();

    if (global.geometry_type) {
      global.geometry_type.onchange = geometryTypeChange;

      if (global.coordinates) {
        global.coordinates.oninput = geometryTypeChange;
      }

      geometryTypeChange();
    }
    else {
      var geometry_type = document.getElementById("type-" + identifier);

      if (geometry_type) {
        proccessGeometryType(geometry_type.value);
      }
    }
  }
}

var maps = document.querySelectorAll("[data-map]");
var maps_length = maps.length;
var maps_init = [];

for (var i = 0; i < maps_length; i++) {
  (function() {
    var map = maps[i];
    var isSmall = map.hasAttribute("data-small");

    map = map.getAttribute("data-map");
    map = new Map(map, isSmall);
    maps_init.push(map.init);
  })();
}

function init() {
  var length = maps_init.length;

  for (var i = 0; i < length; i++) {
    var map_init = maps_init[i];
    map_init();
  }
}
