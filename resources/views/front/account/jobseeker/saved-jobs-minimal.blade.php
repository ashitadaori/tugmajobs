<!DOCTYPE html>
<html>
<head>
    <title>Saved Jobs Test</title>
</head>
<body style="background: red; color: white; padding: 50px; font-family: Arial;">
    <h1 style="font-size: 60px;">🔥 MINIMAL TEST VIEW 🔥</h1>
    <p style="font-size: 30px;">If you see this, the route is working!</p>
    <p style="font-size: 24px;">Total: {{ $savedJobs->total() }}</p>
    <p style="font-size: 24px;">Count: {{ $savedJobs->count() }}</p>
    
    <hr style="border: 3px solid white;">
    
    <div style="background: yellow; color: black; padding: 20px; margin: 20px 0;">
        @php
            foreach($savedJobs as $job) {
                echo "<div style='background: white; padding: 10px; margin: 10px; border: 2px solid black;'>";
                echo "<h3>Job ID: " . $job->id . "</h3>";
                echo "<p>Job Title: " . ($job->job ? $job->job->title : 'N/A') . "</p>";
                echo "</div>";
            }
        @endphp
    </div>
</body>
</html>