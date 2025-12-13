# Admin files
Copy-Item -Path "app\Http\Controllers\Admin\*" -Destination "app\Modules\Admin\Http\Controllers\" -Force
Copy-Item -Path "resources\views\admin\*" -Destination "app\Modules\Admin\Views\" -Recurse -Force

# Employer files
Copy-Item -Path "app\Http\Controllers\EmployerController.php" -Destination "app\Modules\Employer\Http\Controllers\" -Force
Copy-Item -Path "app\Models\EmployerProfile.php" -Destination "app\Modules\Employer\Models\" -Force
Copy-Item -Path "resources\views\front\employer\*" -Destination "app\Modules\Employer\Views\" -Recurse -Force

# JobSeeker files
Copy-Item -Path "app\Http\Controllers\JobsController.php" -Destination "app\Modules\JobSeeker\Http\Controllers\" -Force
Copy-Item -Path "app\Http\Controllers\SavedJobController.php" -Destination "app\Modules\JobSeeker\Http\Controllers\" -Force
Copy-Item -Path "app\Models\JobSeekerProfile.php" -Destination "app\Modules\JobSeeker\Models\" -Force
Copy-Item -Path "app\Models\SavedJob.php" -Destination "app\Modules\JobSeeker\Models\" -Force
Copy-Item -Path "resources\views\front\account\job\*" -Destination "app\Modules\JobSeeker\Views\" -Recurse -Force 