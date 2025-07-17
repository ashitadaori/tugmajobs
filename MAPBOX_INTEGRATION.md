# Mapbox Integration for TugmaJobs

This document outlines the complete Mapbox integration implemented for TugmaJobs, specifically configured for Digos City, Davao del Sur.

## Overview

The integration provides location-based features including:
- Job location input with autocomplete
- Geographic job search and filtering
- Distance-based job recommendations
- Location validation within Digos City boundaries

## Setup Instructions

### 1. Get Mapbox Tokens

1. Sign up at [mapbox.com](https://mapbox.com)
2. Go to your Account page
3. Create a new access token with these scopes:
   - `styles:read`
   - `fonts:read`
   - `datasets:read`
   - `geocoding:read`
4. Copy your public and secret tokens

### 2. Configure Environment

Add these variables to your `.env` file:

```env
# Mapbox Configuration
MAPBOX_PUBLIC_TOKEN=pk.your_public_token_here
MAPBOX_SECRET_TOKEN=sk.your_secret_token_here
```

### 3. Run Database Migration

```bash
php artisan migrate
```

This adds location fields to the jobs table:
- `address` - Full address string
- `latitude` - Decimal coordinates
- `longitude` - Decimal coordinates  
- `barangay` - Barangay name
- `city` - City (defaults to "Digos City")
- `province` - Province (defaults to "Davao del Sur")

### 4. Test Integration

Run the test command to verify everything is working:

```bash
php artisan mapbox:test
```

## Features Implemented

### 1. Location Input Component

**File:** `resources/views/components/location-input.blade.php`

A reusable Blade component that provides:
- Real-time location search with autocomplete
- Coordinate capture (latitude/longitude)
- Address validation within Digos City
- Clean, responsive UI

**Usage:**
```blade
<x-location-input 
    name="job_location" 
    placeholder="Enter location in Digos City"
    required
/>
```

### 2. Mapbox Service Class

**File:** `app/Services/MapboxService.php`

Provides methods for:
- `geocodeAddress($address)` - Convert address to coordinates
- `reverseGeocode($lng, $lat)` - Convert coordinates to address
- `searchPlaces($query)` - Search for places within Digos City
- `calculateDistance($lat1, $lng1, $lat2, $lng2)` - Calculate distance between points
- `isWithinDigosCity($lng, $lat)` - Validate coordinates are within city bounds

### 3. Location API Controller

**File:** `app/Http/Controllers/LocationController.php`

API endpoints:
- `GET /api/location/search?q={query}` - Search places
- `GET /api/location/geocode?address={address}` - Geocode address
- `GET /api/location/reverse-geocode?lat={lat}&lng={lng}` - Reverse geocode
- `GET /api/location/config` - Get frontend configuration

### 4. Enhanced Job Model

**File:** `app/Models/Job.php`

New methods:
- `getFullAddress()` - Get formatted full address
- `hasCoordinates()` - Check if job has valid coordinates
- `distanceFrom($lat, $lng)` - Calculate distance from given point
- `scopeWithinDistance($query, $lat, $lng, $distance)` - Find jobs within radius
- `scopeInDigosCity($query)` - Filter jobs within Digos City

### 5. Job Location Filter Component

**File:** `resources/views/components/job-location-filter.blade.php`

Provides:
- Location-based job search
- Radius filtering (5km to 50km)
- "Use Current Location" functionality
- Integration with job search forms

## Configuration

### Digos City Boundaries

The system is configured with these geographic boundaries:

```php
'digos_bounds' => [
    'southwest' => [125.3, 6.7],
    'northeast' => [125.5, 6.9],
    'bbox' => '125.3,6.7,125.5,6.9'
]
```

### Default Settings

```php
'default_center' => [
    'lng' => 125.4,
    'lat' => 6.8
],
'default_zoom' => 13
```

## Usage Examples

### 1. Job Creation with Location

The job creation form now includes location input:

```blade
<x-location-input 
    name="location" 
    placeholder="Search for location in Digos City, Davao del Sur"
    required
    id="job-location"
/>
```

### 2. Location-Based Job Search

```php
// Find jobs within 10km of coordinates
$jobs = Job::withinDistance($latitude, $longitude, 10)
    ->active()
    ->get();

// Find jobs in Digos City
$jobs = Job::inDigosCity()
    ->active()
    ->get();
```

### 3. Distance Calculation

```php
$job = Job::find(1);
$userLat = 6.7545;
$userLng = 125.3578;

$distance = $job->distanceFrom($userLat, $userLng);
echo "Job is {$distance} km away";
```

## Frontend Integration

### Required Assets

The layout includes Mapbox GL JS:

```html
<!-- CSS -->
<link href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css' rel='stylesheet' />

<!-- JavaScript -->
<script src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js'></script>
```

### JavaScript API Usage

```javascript
// Get Mapbox configuration
fetch('/api/location/config')
    .then(response => response.json())
    .then(config => {
        // Use config.public_token, config.default_center, etc.
    });

// Search for places
fetch('/api/location/search?q=Poblacion')
    .then(response => response.json())
    .then(data => {
        // Handle search results
    });
```

## Security Considerations

1. **API Keys**: Public token is safe for frontend use, secret token is server-side only
2. **Rate Limiting**: Mapbox has usage limits - monitor your usage
3. **Input Validation**: All location inputs are validated server-side
4. **Boundary Checking**: Locations are restricted to Digos City area

## Troubleshooting

### Common Issues

1. **"Mapbox tokens not configured"**
   - Check your `.env` file has the correct token values
   - Ensure tokens are not wrapped in quotes

2. **"Geocoding failed"**
   - Verify your tokens have geocoding permissions
   - Check your Mapbox account usage limits

3. **"No search results"**
   - Ensure you're searching within Digos City area
   - Check the bounding box configuration

### Testing

Run the test command to diagnose issues:

```bash
php artisan mapbox:test
```

This will test:
- Configuration
- Geocoding
- Reverse geocoding
- Place search
- Boundary checking
- Distance calculation

## Performance Optimization

1. **Caching**: Consider caching geocoding results for frequently searched locations
2. **Debouncing**: Location search inputs use 300ms debounce to reduce API calls
3. **Lazy Loading**: Maps are only loaded when needed

## Future Enhancements

Potential improvements:
1. **Interactive Maps**: Add map displays for job locations
2. **Route Planning**: Integration with directions API
3. **Geofencing**: Alert users about jobs in their preferred areas
4. **Analytics**: Track popular search locations

## Support

For issues with this integration:
1. Check the test command output
2. Review Laravel logs for API errors
3. Verify Mapbox account status and usage
4. Ensure database migrations are complete

---

**Note**: This integration is specifically configured for Digos City, Davao del Sur. To use in other locations, update the boundary coordinates in `config/mapbox.php`.