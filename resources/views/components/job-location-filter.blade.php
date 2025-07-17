@props([
    'currentLocation' => '',
    'radius' => 10
])

<div class="location-filter-wrapper">
    <div class="row g-3">
        <!-- Location Search -->
        <div class="col-md-8">
            <label for="location-filter" class="form-label">Location</label>
            <x-location-input 
                name="location_filter" 
                :value="$currentLocation" 
                placeholder="Search jobs near..."
                id="location-filter"
                class="form-control"
            />
        </div>
        
        <!-- Radius Filter -->
        <div class="col-md-4">
            <label for="radius-filter" class="form-label">Within (km)</label>
            <select name="radius" id="radius-filter" class="form-select">
                <option value="5" {{ $radius == 5 ? 'selected' : '' }}>5 km</option>
                <option value="10" {{ $radius == 10 ? 'selected' : '' }}>10 km</option>
                <option value="15" {{ $radius == 15 ? 'selected' : '' }}>15 km</option>
                <option value="25" {{ $radius == 25 ? 'selected' : '' }}>25 km</option>
                <option value="50" {{ $radius == 50 ? 'selected' : '' }}>50 km</option>
            </select>
        </div>
    </div>
    
    <!-- Current Location Button -->
    <div class="mt-3">
        <button type="button" class="btn btn-outline-primary btn-sm" id="use-current-location">
            <i class="fas fa-location-arrow me-1"></i> Use Current Location
        </button>
        <small class="text-muted ms-2">Find jobs near you</small>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const useLocationBtn = document.getElementById('use-current-location');
    const locationInput = document.getElementById('location-filter');
    const latInput = document.querySelector('input[name="location_filter_latitude"]');
    const lngInput = document.querySelector('input[name="location_filter_longitude"]');
    
    useLocationBtn.addEventListener('click', function() {
        if (navigator.geolocation) {
            useLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Getting location...';
            useLocationBtn.disabled = true;
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    // Reverse geocode to get address
                    fetch(`/api/location/reverse-geocode?lat=${lat}&lng=${lng}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.features && data.features.length > 0) {
                                const place = data.features[0];
                                locationInput.value = place.place_name;
                                if (latInput) latInput.value = lat;
                                if (lngInput) lngInput.value = lng;
                                
                                // Trigger search
                                locationInput.dispatchEvent(new Event('change'));
                            }
                        })
                        .catch(error => {
                            console.error('Reverse geocoding failed:', error);
                            alert('Could not get your location address. Please enter manually.');
                        })
                        .finally(() => {
                            useLocationBtn.innerHTML = '<i class="fas fa-location-arrow me-1"></i> Use Current Location';
                            useLocationBtn.disabled = false;
                        });
                },
                function(error) {
                    console.error('Geolocation error:', error);
                    alert('Could not access your location. Please check your browser settings.');
                    useLocationBtn.innerHTML = '<i class="fas fa-location-arrow me-1"></i> Use Current Location';
                    useLocationBtn.disabled = false;
                }
            );
        } else {
            alert('Geolocation is not supported by this browser.');
        }
    });
});
</script>
@endpush