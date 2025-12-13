# Job Portal System - Data Flow Diagram

## Overview
This document presents comprehensive data flow diagrams for the Job Portal system, illustrating how data moves through different processes and components of the application.

## System Architecture Overview

```mermaid
graph TB
    %% External Entities
    JS[Job Seekers]
    EM[Employers]
    AD[Administrators]
    KYC_API[Didit KYC Service]
    EMAIL[Email Service]
    SOCIAL[Social Auth Providers]
    
    %% Main System Components
    WEB[Web Interface]
    AUTH[Authentication System]
    DB[(Database)]
    FILE[File Storage]
    NOTIF[Notification System]
    AI[AI/ML Features]
    
    %% Data Flow Connections
    JS --> WEB
    EM --> WEB
    AD --> WEB
    SOCIAL --> AUTH
    WEB --> AUTH
    AUTH --> DB
    WEB --> DB
    WEB --> FILE
    WEB --> NOTIF
    WEB --> AI
    KYC_API --> DB
    NOTIF --> EMAIL
    
    %% Styling
    classDef external fill:#e1f5fe
    classDef system fill:#f3e5f5
    classDef storage fill:#e8f5e8
    
    class JS,EM,AD,KYC_API,EMAIL,SOCIAL external
    class WEB,AUTH,NOTIF,AI system
    class DB,FILE storage
```

## 1. User Registration and Authentication Flow

```mermaid
sequenceDiagram
    participant User as User (JS/EM)
    participant Web as Web Interface
    participant Auth as Auth System
    participant Social as Social Providers
    participant DB as Database
    participant Email as Email Service
    
    %% Registration Flow
    User->>Web: Access Registration
    Web->>User: Display Registration Form
    
    alt Social Registration
        User->>Web: Choose Social Login
        Web->>Social: Redirect to Provider
        Social->>Web: Return User Data
        Web->>Auth: Process Social Auth
        Auth->>DB: Create/Update User Record
        DB-->>Auth: User Created
    else Standard Registration
        User->>Web: Submit Registration Form
        Web->>Auth: Validate & Process Data
        Auth->>DB: Create User Record
        DB-->>Auth: User Created
        Auth->>Email: Send Verification Email
    end
    
    Auth->>DB: Create Role-Specific Profile
    note over DB: Creates records in:<br/>- jobseekers (if role=jobseeker)<br/>- employers (if role=employer)<br/>- admins (if role=admin)
    
    Web->>User: Registration Success/Login
```

## 2. KYC Verification Process Flow

```mermaid
sequenceDiagram
    participant User as User
    participant Web as Web Interface
    participant KYC_Controller as KYC Controller
    participant Didit as Didit KYC API
    participant Webhook as KYC Webhook
    participant DB as Database
    participant Notif as Notification System
    
    %% KYC Initiation
    User->>Web: Start KYC Verification
    Web->>KYC_Controller: Initialize KYC
    KYC_Controller->>DB: Create KYC Session
    KYC_Controller->>Didit: Create Verification Session
    Didit-->>KYC_Controller: Session URL & ID
    KYC_Controller->>DB: Store Session Data
    Web->>User: Redirect to Didit
    
    %% External Verification
    User->>Didit: Complete Verification
    note over User,Didit: Document Upload<br/>Biometric Verification<br/>Liveness Check
    
    %% Webhook Processing
    Didit->>Webhook: Send Verification Results
    Webhook->>DB: Store Detailed KYC Data
    note over DB: Updates:<br/>- users.kyc_status<br/>- kyc_data table<br/>- kyc_verifications table
    
    Webhook->>Notif: Send Completion Notification
    Notif->>User: KYC Status Update
    
    alt Verification Successful
        Webhook->>DB: Mark User as Verified
        note over DB: Enable job posting for employers<br/>Unlock premium features
    else Verification Failed
        Webhook->>DB: Mark as Failed
        Webhook->>Notif: Send Retry Instructions
    end
```

## 3. Job Management Flow (Employer Perspective)

```mermaid
flowchart TD
    A[Employer Login] --> B{KYC Verified?}
    B -->|No| C[Complete KYC Verification]
    C --> D[Wait for Approval]
    D --> E{KYC Approved?}
    E -->|No| F[Access Restricted]
    E -->|Yes| G[Access Job Management]
    B -->|Yes| G
    
    G --> H[Create Job Posting]
    H --> I[Fill Job Details]
    I --> J[Set Requirements & Benefits]
    J --> K[Choose Location & Salary]
    K --> L[Submit for Review]
    
    L --> M[(Database)]
    M --> N[Admin Review Queue]
    N --> O{Admin Decision}
    
    O -->|Approve| P[Job Goes Live]
    O -->|Reject| Q[Send Feedback to Employer]
    Q --> H
    
    P --> R[Job Visible to Job Seekers]
    R --> S[Track Applications & Views]
    S --> T[Manage Application Status]
    
    %% Data Storage Points
    M -.-> M1[jobs table]
    M -.-> M2[categories table]
    M -.-> M3[job_types table]
    M -.-> M4[employers table]
    
    style B fill:#ffeb3b
    style E fill:#ffeb3b
    style O fill:#ffeb3b
```

## 4. Job Application Process Flow

```mermaid
sequenceDiagram
    participant JS as Job Seeker
    participant Web as Web Interface
    participant App_Controller as Application Controller
    participant DB as Database
    participant Employer as Employer
    participant Notif as Notification System
    participant Email as Email Service
    
    %% Job Discovery
    JS->>Web: Browse/Search Jobs
    Web->>DB: Query Available Jobs
    DB-->>Web: Return Job Listings
    Web->>JS: Display Jobs
    
    %% Job Application
    JS->>Web: Click Apply for Job
    Web->>App_Controller: Start Application Process
    App_Controller->>DB: Check Existing Application
    
    alt First Time Applying
        App_Controller->>Web: Show Application Form
        JS->>Web: Complete Application
        note over JS,Web: Upload Resume<br/>Write Cover Letter<br/>Answer Questions
        
        Web->>App_Controller: Submit Application
        App_Controller->>DB: Create Job Application
        note over DB: Insert into job_applications<br/>Create status history record
        
        App_Controller->>Notif: Create Notifications
        Notif->>Employer: New Application Alert
        Notif->>Email: Send Email Notification
        Notif->>JS: Application Submitted Confirmation
        
    else Already Applied
        App_Controller->>Web: Show Existing Application
        Web->>JS: Display Application Status
    end
    
    %% Status Updates
    loop Application Lifecycle
        Employer->>Web: Update Application Status
        Web->>DB: Update Status & Create History
        DB->>Notif: Trigger Status Change Event
        Notif->>JS: Status Update Notification
        Notif->>Email: Send Status Email
    end
```

## 5. Job Search and Recommendation Flow

```mermaid
flowchart TB
    A[Job Seeker Search Request] --> B{Search Type?}
    
    B -->|Basic Search| C[Text Search]
    B -->|Advanced Search| D[Multi-Criteria Search]
    B -->|AI Recommendations| E[K-Means Clustering]
    
    C --> F[(job_views tracking)]
    D --> G[Filter by Location, Salary, etc.]
    E --> H[Analyze User Profile & Preferences]
    
    G --> I[(Database Query)]
    H --> J[Generate Personalized Results]
    
    I --> K[Return Search Results]
    J --> K
    F --> K
    
    K --> L[Display to User]
    L --> M[Track User Interactions]
    M --> N[(Update Analytics)]
    
    %% K-Means Enhancement
    H --> H1[User Skills Analysis]
    H --> H2[Job Category Preferences]
    H --> H3[Salary Range Matching]
    H --> H4[Location Preferences]
    
    H1 --> O[Calculate Match Scores]
    H2 --> O
    H3 --> O
    H4 --> O
    
    O --> P[Rank Recommendations]
    P --> J
    
    style E fill:#4caf50
    style H fill:#4caf50
    style O fill:#4caf50
```

## 6. Admin Management Flow

```mermaid
graph TD
    A[Admin Login] --> B{Admin Level Check}
    B -->|Super Admin| C[Full System Access]
    B -->|Department Admin| D[Limited Module Access]
    
    C --> E[User Management]
    C --> F[Job Management]
    C --> G[System Settings]
    C --> H[Analytics & Reports]
    C --> I[Document Review]
    
    D --> J[Assigned Modules Only]
    
    %% Job Management Flow
    F --> F1[Review Pending Jobs]
    F1 --> F2{Approve Job?}
    F2 -->|Yes| F3[Set Job as Active]
    F2 -->|No| F4[Send Rejection Feedback]
    F3 --> F5[(Update jobs table)]
    F4 --> F6[(Add rejection reason)]
    
    %% Document Review Flow  
    I --> I1[Review Employer Documents]
    I1 --> I2{Approve Documents?}
    I2 -->|Yes| I3[Enable Job Posting]
    I2 -->|No| I4[Request Additional Documents]
    I3 --> I5[(Update employer_documents)]
    I4 --> I6[(Add admin notes)]
    
    %% User Management
    E --> E1[Manage Job Seekers]
    E --> E2[Manage Employers]  
    E --> E3[Manage Admins]
    E1 --> E4[View Profiles & Applications]
    E2 --> E5[Review Company Information]
    E3 --> E6[Assign Permissions]
    
    style B fill:#ff9800
    style F2 fill:#ff9800
    style I2 fill:#ff9800
```

## 7. Notification System Flow

```mermaid
flowchart LR
    A[System Events] --> B{Event Type}
    
    B -->|Job Application| C[Application Notifications]
    B -->|KYC Update| D[Verification Notifications]
    B -->|Job Status| E[Job Management Notifications]
    B -->|System| F[Admin Notifications]
    
    C --> G[Create Notification Record]
    D --> G
    E --> G
    F --> G
    
    G --> H[(notifications table)]
    
    H --> I{User Preferences}
    I -->|Email Enabled| J[Send Email]
    I -->|In-App Only| K[Store for Dashboard]
    
    J --> L[Email Service]
    K --> M[User Dashboard]
    
    L --> N[Track Email Status]
    M --> O[Mark as Read/Unread]
    
    %% Notification Types Detail
    C --> C1[New Application Alert<br/>to Employer]
    C --> C2[Status Update Alert<br/>to Job Seeker]
    
    D --> D1[KYC Completion Alert]
    D --> D2[Verification Success/Failure]
    
    E --> E1[Job Approved/Rejected]
    E --> E2[Job Expired Warning]
    
    F --> F1[System Maintenance]
    F --> F2[User Reports]
    
    style I fill:#2196f3
```

## 8. File and Document Management Flow

```mermaid
sequenceDiagram
    participant User as User
    participant Web as Web Interface
    participant Upload as Upload Controller
    participant Validation as File Validator
    participant Storage as File Storage
    participant DB as Database
    participant Virus as Virus Scanner
    
    User->>Web: Upload File (Resume/Document)
    Web->>Upload: Process Upload Request
    Upload->>Validation: Validate File
    
    alt Valid File
        Validation->>Virus: Scan for Security
        Virus-->>Validation: Clean File
        Validation->>Storage: Store File
        Storage-->>Validation: File Path
        Validation->>DB: Save File Metadata
        note over DB: Store in:<br/>- employer_documents<br/>- jobseeker profile fields<br/>- user profile images
        DB-->>Upload: Success Response
        Upload->>Web: File Uploaded Successfully
    else Invalid File
        Validation->>Web: Return Error
    end
    
    Web->>User: Upload Status
    
    %% File Access Flow
    loop File Access
        User->>Web: Request File Download
        Web->>DB: Verify Access Permissions
        DB-->>Web: Permission Check Result
        alt Authorized
            Web->>Storage: Retrieve File
            Storage-->>Web: File Content
            Web->>User: Serve File
        else Unauthorized
            Web->>User: Access Denied
        end
    end
```

## 9. Database Entity Relationships and Data Flow

```mermaid
erDiagram
    %% Core Tables
    USERS ||--|| JOBSEEKERS : "has profile"
    USERS ||--|| EMPLOYERS : "has profile"  
    USERS ||--|| ADMINS : "has profile"
    
    %% Job Management
    EMPLOYERS ||--o{ JOBS : "posts"
    JOBS }o--|| CATEGORIES : "belongs to"
    JOBS }o--|| JOB_TYPES : "has type"
    
    %% Applications
    USERS ||--o{ JOB_APPLICATIONS : "applies"
    JOBS ||--o{ JOB_APPLICATIONS : "receives"
    JOB_APPLICATIONS ||--o{ APPLICATION_STATUS_HISTORIES : "tracks"
    
    %% KYC System
    USERS ||--o{ KYC_VERIFICATIONS : "has sessions"
    USERS ||--o{ KYC_DATA : "has data"
    
    %% Documents & Files
    USERS ||--o{ EMPLOYER_DOCUMENTS : "uploads"
    
    %% Interactions
    JOBS ||--o{ JOB_VIEWS : "tracked"
    USERS }o--o{ JOBS : "saves (saved_jobs)"
    
    %% Notifications
    USERS ||--o{ NOTIFICATIONS : "receives"
    
    %% Data Flow Annotations
    USERS {
        string role "jobseeker|employer|admin"
        string kyc_status "pending|verified|failed"
        json preferences "job preferences"
    }
    
    JOBS {
        enum status "pending|approved|rejected|expired"
        decimal salary_min "compensation range"
        decimal salary_max "compensation range"
    }
    
    JOB_APPLICATIONS {
        enum status "pending|shortlisted|rejected|hired"
        enum application_step "basic_info|screening|documents|review|submitted"
    }
```

## 10. Analytics and Reporting Data Flow

```mermaid
flowchart TD
    A[User Actions] --> B[Data Collection Points]
    
    B --> C[Job Views Tracking]
    B --> D[Application Tracking]
    B --> E[Profile Interactions]
    B --> F[Search Behavior]
    
    C --> G[(job_views table)]
    D --> H[(job_applications table)]
    E --> I[(user activity logs)]
    F --> J[(search analytics)]
    
    G --> K[Analytics Processing]
    H --> K
    I --> K
    J --> K
    
    K --> L{Report Type}
    
    L -->|Employer| M[Company Analytics]
    L -->|Admin| N[System Analytics]  
    L -->|Job Seeker| O[Profile Analytics]
    
    M --> M1[Job Performance<br/>Application Rates<br/>Hiring Success]
    N --> N1[User Growth<br/>System Usage<br/>Popular Categories]
    O --> O1[Profile Views<br/>Application Success<br/>Match Scores]
    
    %% K-Means Analytics
    K --> P[ML Analytics]
    P --> P1[User Clustering]
    P --> P2[Job Recommendations]
    P --> P3[Match Optimization]
    
    style P fill:#4caf50
    style P1 fill:#4caf50
    style P2 fill:#4caf50
    style P3 fill:#4caf50
```

## Key Data Flow Characteristics

### 1. **Role-Based Data Separation**
- **Users Table**: Central authentication hub
- **Jobseekers Table**: Detailed job seeker profiles and preferences
- **Employers Table**: Company information and business details
- **Admins Table**: Administrative permissions and activities

### 2. **Multi-Layer Verification**
- **Email Verification**: Standard account verification
- **KYC Verification**: Identity verification via Didit API
- **Document Verification**: Business document review for employers

### 3. **Event-Driven Notifications**
- Real-time notifications for applications, status changes, and system events
- Multiple delivery channels (in-app, email)
- User preference-based notification filtering

### 4. **Advanced Job Matching**
- K-means clustering for personalized recommendations
- Skills-based matching algorithms
- Location and salary preference filtering
- Experience level compatibility

### 5. **Comprehensive Audit Trail**
- Application status history tracking
- Admin action logging
- KYC verification audit trail
- User activity analytics

## Security Considerations in Data Flow

1. **Authentication Security**
   - Password hashing using Laravel's built-in bcrypt
   - Social OAuth integration for secure third-party login
   - Session management with CSRF protection

2. **File Upload Security**
   - File type validation and size restrictions
   - Virus scanning for uploaded documents
   - Secure file storage with access controls

3. **API Security**
   - KYC webhook signature verification
   - Rate limiting on public endpoints
   - Input validation and sanitization

4. **Data Privacy**
   - GDPR-compliant data handling
   - User consent management
   - Secure deletion of sensitive data

This comprehensive data flow diagram illustrates how your Job Portal system efficiently manages data across different user roles, processes, and integrations while maintaining security and user experience standards.
