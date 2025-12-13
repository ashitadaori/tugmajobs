# Design Document - Professional Resume Template Designs

## Overview

This design outlines the technical approach for creating professional, visually appealing resume templates that transform user-entered data into beautifully designed resumes. The system will support multiple template variants with a focus on the Minimalist template design, featuring a two-column layout with sidebar and main content area.

## Architecture

### High-Level Architecture

```
User Input (Form) → Resume Data (JSON) → Template Engine → Preview/PDF Output
```

**Components:**
1. **Data Layer** - Existing (resume_data table with JSON fields)
2. **Template Views** - New Blade templates for each design
3. **CSS Styling** - Dedicated stylesheets for print/PDF
4. **PDF Generator** - Laravel DomPDF for PDF generation
5. **Preview System** - Real-time preview in browser

### Template System Design

Each template will be a separate Blade view file:
- `minimalist.blade.php` - Two-column sidebar layout
- `modern.blade.php` - Contemporary design with accent colors
- `professional.blade.php` - Traditional single-column layout

## Components and Interfaces

### 1. Template View Structure

**File Location:** `resources/views/front/account/resume-builder/templates/`

**Template Interface:**
```php
// Each template receives:
$resume = [
    'id' => int,
    'title' => string,
    'template' => object,
    'data' => [
        'personal_info' => [
            'name' => string,
            'email' => string,
            'phone' => string,
            'address' => string,
            'photo' => string|null
        ],
        'professional_summary' => string,
        'work_experience' => array,
        'education' => array,
        'skills' => array,
        'certifications' => array,
        'languages' => array,
        'projects' => array
    ]
];
```

### 2. Minimalist Template Layout

**Structure:**
```
┌─────────────────────────────────────┐
│  ┌──────────┐  ┌─────────────────┐ │
│  │          │  │                 │ │
│  │  SIDEBAR │  │  MAIN CONTENT   │ │
│  │  (30%)   │  │     (70%)       │ │
│  │          │  │                 │ │
│  │  Photo   │  │  About Me       │ │
│  │  Name    │  │  Education      │ │
│  │  Title   │  │  Work Exp       │ │
│  │  Contact │  │                 │ │
│  │  Skills  │  │                 │ │
│  │          │  │                 │ │
│  └──────────┘  └─────────────────┘ │
└─────────────────────────────────────┘
```

**CSS Grid Implementation:**
```css
.resume-container {
    display: grid;
    grid-template-columns: 30% 70%;
    min-height: 100vh;
}
```

### 3. Visual Elements

**Icons:**
- Use Font Awesome or Material Design Icons
- Inline SVG for print compatibility
- Icons for: phone, email, location, website, education, work, skills

**Timeline Design:**
```
Education/Work Experience:
┌─────────────────────────────────┐
│  ●────  Masters Degree          │
│  │      2004-2008                │
│  │      University Name          │
│  │                               │
│  ●────  Bachelor Degree          │
│         2000-2004                │
│         University Name          │
└─────────────────────────────────┘
```

**Skills Rating Bars:**
```
Project Management  ████████░░  80%
Problem Solving     ██████████  100%
Creativity          ████████░░  80%
```

### 4. Controller Updates

**ResumeBuilderController Methods:**

```php
public function preview($id)
{
    $resume = Resume::with(['template', 'data'])->findOrFail($id);
    $templateView = 'front.account.resume-builder.templates.' . $resume->template->slug;
    
    return view($templateView, compact('resume'));
}

public function download($id)
{
    $resume = Resume::with(['template', 'data'])->findOrFail($id);
    $templateView = 'front.account.resume-builder.templates.' . $resume->template->slug;
    
    $pdf = PDF::loadView($templateView, compact('resume'))
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'Arial'
        ]);
    
    return $pdf->download($resume->title . '.pdf');
}
```

## Data Models

### Resume Data Structure (Existing)

```json
{
  "personal_info": {
    "name": "Isabel Mercado",
    "email": "isabel@example.com",
    "phone": "+123-456-7890",
    "address": "123 Anywhere St., Any City",
    "website": "www.reallygreatsite.com",
    "photo": "/storage/photos/user-123.jpg"
  },
  "professional_summary": "Lorem ipsum dolor sit amet...",
  "work_experience": [
    {
      "title": "Senior Graphic Designer",
      "company": "Really Great Company",
      "location": "City, State",
      "start_date": "2018-01",
      "end_date": "2020-12",
      "current": false,
      "description": "• Managed website appearance\n• Collaborated with teams"
    }
  ],
  "education": [
    {
      "degree": "Masters in Product Design",
      "institution": "Really Great University",
      "location": "City, State",
      "graduation_date": "2008-05",
      "gpa": "3.8"
    }
  ],
  "skills": [
    {"name": "Project Management", "level": 80},
    {"name": "Problem Solving", "level": 100},
    {"name": "Creativity", "level": 80},
    {"name": "Leadership", "level": 70}
  ]
}
```

### Photo Upload Feature

**Migration Addition:**
```php
Schema::table('users', function (Blueprint $table) {
    $table->string('profile_photo')->nullable();
});
```

**Storage:**
- Photos stored in `storage/app/public/profile-photos/`
- Symlink: `php artisan storage:link`
- Max size: 2MB
- Formats: JPG, PNG
- Resize to: 300x300px

## CSS Styling Strategy

### 1. Print-Optimized CSS

**File:** `public/css/resume-templates.css`

```css
/* Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', 'Helvetica', sans-serif;
    font-size: 11pt;
    line-height: 1.4;
    color: #333;
}

/* Print Styles */
@media print {
    body {
        background: white;
    }
    .no-print {
        display: none;
    }
    .page-break {
        page-break-after: always;
    }
}

/* Minimalist Template */
.minimalist-template {
    display: grid;
    grid-template-columns: 30% 70%;
    min-height: 297mm; /* A4 height */
    max-width: 210mm; /* A4 width */
}

.sidebar {
    background: #2c3e50;
    color: white;
    padding: 30px 20px;
}

.main-content {
    padding: 30px 40px;
    background: white;
}

/* Typography */
.name {
    font-size: 28pt;
    font-weight: bold;
    margin-bottom: 5px;
}

.job-title {
    font-size: 14pt;
    font-weight: 300;
    margin-bottom: 20px;
}

.section-title {
    font-size: 16pt;
    font-weight: bold;
    margin-bottom: 15px;
    padding-bottom: 5px;
    border-bottom: 2px solid #3498db;
}

/* Icons */
.icon {
    width: 16px;
    height: 16px;
    margin-right: 10px;
    vertical-align: middle;
}

/* Skills Rating */
.skill-bar {
    height: 8px;
    background: #ecf0f1;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 10px;
}

.skill-fill {
    height: 100%;
    background: #3498db;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #3498db;
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -26px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #3498db;
    border: 3px solid white;
}
```

### 2. Responsive Design

Templates will be optimized for:
- **Desktop Preview:** Full-width display
- **Print/PDF:** A4 size (210mm x 297mm)
- **Mobile:** Responsive for editing (not for final output)

## PDF Generation

### DomPDF Configuration

**config/dompdf.php:**
```php
return [
    'show_warnings' => false,
    'public_path' => public_path(),
    'convert_entities' => true,
    'options' => [
        'font_dir' => storage_path('fonts/'),
        'font_cache' => storage_path('fonts/'),
        'temp_dir' => sys_get_temp_dir(),
        'chroot' => realpath(base_path()),
        'enable_font_subsetting' => false,
        'pdf_backend' => 'CPDF',
        'default_media_type' => 'print',
        'default_paper_size' => 'a4',
        'default_font' => 'Arial',
        'dpi' => 96,
        'enable_php' => false,
        'enable_javascript' => false,
        'enable_remote' => true,
        'font_height_ratio' => 1.1,
        'enable_html5_parser' => true,
    ],
];
```

### Image Handling in PDF

```php
// Convert image to base64 for PDF
$photoPath = storage_path('app/public/' . $resume->data->personal_info['photo']);
if (file_exists($photoPath)) {
    $photoData = base64_encode(file_get_contents($photoPath));
    $photoSrc = 'data:image/jpeg;base64,' . $photoData;
} else {
    $photoSrc = asset('images/default-avatar.png');
}
```

## Testing Strategy

### 1. Visual Testing
- Compare preview with PDF output
- Test all templates with sample data
- Verify print output matches screen

### 2. Data Testing
- Test with minimal data (only required fields)
- Test with maximum data (all fields filled)
- Test with special characters and long text

### 3. Browser Testing
- Chrome (PDF generation)
- Firefox (preview)
- Safari (preview)
- Edge (preview)

### 4. PDF Testing
- Verify fonts embed correctly
- Check image quality
- Test file size (should be < 1MB)
- Verify printability

## Error Handling

### Missing Data
```php
// Handle missing photo
$photo = $resume->data->personal_info['photo'] ?? null;
if (!$photo || !file_exists(storage_path('app/public/' . $photo))) {
    $photo = 'images/default-avatar.png';
}

// Handle empty sections
$hasWorkExperience = !empty($resume->data->work_experience);
$hasEducation = !empty($resume->data->education);
```

### PDF Generation Errors
```php
try {
    $pdf = PDF::loadView($templateView, compact('resume'));
    return $pdf->download($filename);
} catch (\Exception $e) {
    Log::error('PDF Generation Failed', [
        'resume_id' => $resume->id,
        'error' => $e->getMessage()
    ]);
    return back()->with('error', 'Failed to generate PDF. Please try again.');
}
```

## Implementation Priority

### Phase 1: Minimalist Template (Priority)
1. Create minimalist template view
2. Implement CSS styling
3. Add icons and visual elements
4. Test preview functionality
5. Test PDF generation

### Phase 2: Modern Template
1. Create modern template view
2. Implement styling with accent colors
3. Test and refine

### Phase 3: Professional Template
1. Create professional template view
2. Implement traditional styling
3. Test and refine

### Phase 4: Enhancements
1. Add photo upload feature
2. Optimize PDF file size
3. Add template customization options
4. Performance optimization

## File Structure

```
resources/views/front/account/resume-builder/
├── index.blade.php (existing)
├── create.blade.php (existing)
├── edit.blade.php (existing)
└── templates/
    ├── minimalist.blade.php (new)
    ├── modern.blade.php (new)
    └── professional.blade.php (new)

public/css/
└── resume-templates.css (new)

public/images/
└── icons/ (new)
    ├── phone.svg
    ├── email.svg
    ├── location.svg
    ├── website.svg
    ├── education.svg
    └── work.svg
```

## Summary

This design provides a comprehensive approach to creating professional resume templates with a focus on the Minimalist design. The system uses Blade templates for flexibility, CSS Grid for layout, and DomPDF for PDF generation. The architecture supports multiple template variants while maintaining code reusability and ensuring accurate preview-to-PDF conversion.
