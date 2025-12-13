<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Route;

class NavigationHelper
{
    /**
     * Check if the current route matches any of the given patterns
     *
     * @param array|string $patterns
     * @return bool
     */
    public static function isActiveRoute($patterns)
    {
        if (is_string($patterns)) {
            $patterns = [$patterns];
        }

        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the active class if the route matches
     *
     * @param array|string $patterns
     * @param string $activeClass
     * @return string
     */
    public static function activeClass($patterns, $activeClass = 'active')
    {
        return self::isActiveRoute($patterns) ? $activeClass : '';
    }

    /**
     * Get aria-current attribute value
     *
     * @param array|string $patterns
     * @return string
     */
    public static function ariaCurrent($patterns)
    {
        return self::isActiveRoute($patterns) ? 'page' : 'false';
    }

    /**
     * Check if employer navigation item should be active
     *
     * @param string $section
     * @return bool
     */
    public static function isEmployerSectionActive($section)
    {
        switch ($section) {
            case 'dashboard':
                return request()->routeIs('employer.dashboard');
                
            case 'jobs':
                return request()->routeIs('employer.jobs.*') && !request()->routeIs('employer.jobs.create');
                
            case 'job-create':
                return request()->routeIs('employer.jobs.create');
                
            case 'applications':
                return request()->routeIs('employer.applications.*') && !request()->routeIs('employer.applications.shortlisted');
                
            case 'shortlisted':
                return request()->routeIs('employer.applications.shortlisted');
                
            case 'analytics':
                return request()->routeIs('employer.analytics.*');
                
            case 'profile':
                return request()->routeIs('employer.profile.*');
                
            case 'settings':
                return request()->routeIs('employer.settings.*');
                
            default:
                return false;
        }
    }

    /**
     * Get employer navigation active class
     *
     * @param string $section
     * @param string $activeClass
     * @return string
     */
    public static function employerActiveClass($section, $activeClass = 'active')
    {
        return self::isEmployerSectionActive($section) ? $activeClass : '';
    }

    /**
     * Get employer navigation aria-current attribute
     *
     * @param string $section
     * @return string
     */
    public static function employerAriaCurrent($section)
    {
        return self::isEmployerSectionActive($section) ? 'page' : 'false';
    }

    /**
     * Get breadcrumb items for current route
     *
     * @return array
     */
    public static function getBreadcrumbs()
    {
        $routeName = Route::currentRouteName();
        $breadcrumbs = [];

        // Add home breadcrumb
        $breadcrumbs[] = [
            'title' => 'Home',
            'url' => route('home'),
            'active' => false
        ];

        // Handle employer routes
        if (str_starts_with($routeName, 'employer.')) {
            $breadcrumbs[] = [
                'title' => 'Employer',
                'url' => route('employer.dashboard'),
                'active' => false
            ];

            $routeParts = explode('.', $routeName);
            $section = $routeParts[1] ?? '';

            switch ($section) {
                case 'dashboard':
                    $breadcrumbs[] = [
                        'title' => 'Dashboard',
                        'url' => null,
                        'active' => true
                    ];
                    break;

                case 'jobs':
                    $breadcrumbs[] = [
                        'title' => 'Jobs',
                        'url' => route('employer.jobs.index'),
                        'active' => count($routeParts) === 2
                    ];

                    if (isset($routeParts[2])) {
                        switch ($routeParts[2]) {
                            case 'create':
                                $breadcrumbs[] = [
                                    'title' => 'Create Job',
                                    'url' => null,
                                    'active' => true
                                ];
                                break;
                            case 'edit':
                                $breadcrumbs[] = [
                                    'title' => 'Edit Job',
                                    'url' => null,
                                    'active' => true
                                ];
                                break;
                        }
                    }
                    break;

                case 'applications':
                    if (isset($routeParts[2]) && $routeParts[2] === 'shortlisted') {
                        $breadcrumbs[] = [
                            'title' => 'Shortlisted Applications',
                            'url' => null,
                            'active' => true
                        ];
                    } else {
                        $breadcrumbs[] = [
                            'title' => 'Applications',
                            'url' => null,
                            'active' => true
                        ];
                    }
                    break;

                case 'analytics':
                    $breadcrumbs[] = [
                        'title' => 'Analytics',
                        'url' => null,
                        'active' => true
                    ];
                    break;

                case 'profile':
                    $breadcrumbs[] = [
                        'title' => 'Company Profile',
                        'url' => null,
                        'active' => true
                    ];
                    break;

                case 'settings':
                    $breadcrumbs[] = [
                        'title' => 'Settings',
                        'url' => null,
                        'active' => true
                    ];
                    break;
            }
        }

        return $breadcrumbs;
    }
}