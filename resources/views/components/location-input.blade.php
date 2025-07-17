@props([
    'name' => 'location',
    'value' => '',
    'placeholder' => 'Enter location in Digos City',
    'required' => false,
    'id' => null
])

@php
    $inputId = $id ?? 'location-input-' . uniqid();
@endphp

<div class="location-input-wrapper" data-location-input>
    <div class="form-group">
        <input 
            type="text" 
            name="{{ $name }}" 
            id="{{ $inputId }}"
            class="form-control location-search-input" 
            placeholder="{{ $placeholder }}"
            value="{{ $value }}"
            autocomplete="off"
            {{ $required ? 'required' : '' }}
            {{ $attributes }}
        >
        
        <!-- Hidden fields for coordinates -->
        <input type="hidden" name="{{ $name }}_latitude" class="location-latitude">
        <input type="hidden" name="{{ $name }}_longitude" class="location-longitude">
        <input type="hidden" name="{{ $name }}_address" class="location-address">
        
        <!-- Dropdown for suggestions -->
        <div class="location-suggestions" style="display: none;">
            <ul class="list-group position-absolute w-100" style="z-index: 1000; max-height: 200px; overflow-y: auto;">
                <!-- Suggestions will be populated here -->
            </ul>
        </div>
    </div>
    
    <!-- Optional: Show selected location on map -->
    <div class="location-map" style="display: none; height: 200px; margin-top: 10px;" id="{{ $inputId }}-map">
        <!-- Map will be rendered here -->
    </div>
</div>

@push('styles')
<style>
.location-input-wrapper {
    position: relative;
}

.location-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 4px 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.location-suggestions .list-group-item {
    cursor: pointer;
    border: none;
    border-bottom: 1px solid #eee;
    padding: 10px 15px;
}

.location-suggestions .list-group-item:hover {
    background-color: #f8f9fa;
}

.location-suggestions .list-group-item:last-child {
    border-bottom: none;
}

.location-map {
    border: 1px solid #ddd;
    border-radius: 4px;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeLocationInput('{{ $inputId }}');
});

function initializeLocationInput(inputId) {
    const wrapper = document.querySelector(`#${inputId}`).closest('[data-location-input]');
    const input = wrapper.querySelector('.location-search-input');
    const suggestions = wrapper.querySelector('.location-suggestions');
    const suggestionsList = suggestions.querySelector('ul');
    const latInput = wrapper.querySelector('.location-latitude');
    const lngInput = wrapper.querySelector('.location-longitude');
    const addressInput = wrapper.querySelector('.location-address');
    
    let searchTimeout;
    let mapboxConfig = null;
    
    // Load Mapbox configuration
    fetch('/api/location/config')
        .then(response => response.json())
        .then(config => {
            mapboxConfig = config;
        })
        .catch(error => console.error('Failed to load Mapbox config:', error));
    
    // Search functionality
    input.addEventListener('input', function() {
        const query = this.value.trim();
        
        if (query.length < 2) {
            hideSuggestions();
            return;
        }
        
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchPlaces(query);
        }, 300);
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!wrapper.contains(e.target)) {
            hideSuggestions();
        }
    });
    
    function searchPlaces(query) {
        fetch(`/api/location/search?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.suggestions && data.suggestions.length > 0) {
                    showSuggestions(data.suggestions);
                } else {
                    hideSuggestions();
                }
            })
            .catch(error => {
                console.error('Location search error:', error);
                hideSuggestions();
            });
    }
    
    function showSuggestions(places) {
        suggestionsList.innerHTML = '';
        
        places.forEach(place => {
            const li = document.createElement('li');
            li.className = 'list-group-item';
            li.innerHTML = `
                <div class="fw-bold">${place.name || place.place_name}</div>
                <small class="text-muted">${place.place_name || place.full_address}</small>
            `;
            
            li.addEventListener('click', () => {
                selectPlace(place);
            });
            
            suggestionsList.appendChild(li);
        });
        
        suggestions.style.display = 'block';
    }
    
    function hideSuggestions() {
        suggestions.style.display = 'none';
    }
    
    function selectPlace(place) {
        const coordinates = place.geometry ? place.geometry.coordinates : 
                          place.coordinates ? [place.coordinates.longitude, place.coordinates.latitude] : null;
        
        if (coordinates) {
            // Validate that the location is within Digos City bounds
            if (mapboxConfig && !isWithinDigosCity(coordinates[0], coordinates[1])) {
                alert('Please select a location within Digos City, Davao del Sur only.');
                return;
            }
            
            // Validate that the place name contains Digos City or Davao del Sur
            const placeName = (place.place_name || place.full_address || '').toLowerCase();
            if (!placeName.includes('digos') && !placeName.includes('davao del sur')) {
                alert('Please select a location within Digos City, Davao del Sur only.');
                return;
            }
            
            input.value = place.name || extractLocationName(place.place_name);
            lngInput.value = coordinates[0];
            latInput.value = coordinates[1];
            addressInput.value = place.place_name || place.full_address;
            
            hideSuggestions();
            
            // Trigger change event
            input.dispatchEvent(new Event('change'));
        }
    }
    
    function isWithinDigosCity(longitude, latitude) {
        if (!mapboxConfig || !mapboxConfig.digos_bounds) return true;
        
        const bounds = mapboxConfig.digos_bounds;
        return longitude >= bounds.southwest[0] && 
               longitude <= bounds.northeast[0] && 
               latitude >= bounds.southwest[1] && 
               latitude <= bounds.northeast[1];
    }
    
    function extractLocationName(placeName) {
        if (!placeName) return '';
        
        // Split by comma and get the first meaningful part
        const parts = placeName.split(',');
        let name = parts[0].trim();
        
        // If it's just a number or very short, try the next part
        if (name.length < 3 || /^\d+$/.test(name)) {
            name = parts[1] ? parts[1].trim() : name;
        }
        
        return name;
    }
}
</script>
@endpush