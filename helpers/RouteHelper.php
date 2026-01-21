<?php
/**
 * Route Helper Functions
 * 
 * Helper functions for route management and navigation
 */

/**
 * Get URL path from route name
 * 
 * @param string $routeName - Route name defined in config/routes.php
 * @param array $params - Optional parameters for dynamic routes (e.g., ['id' => 5])
 * @return string - Absolute URL path
 */
function route($routeName, $params = [])
{
    static $routes = null;
    
    if ($routes === null) {
        $routes = require __DIR__ . '/../config/routes.php';
    }
    
    if (!isset($routes[$routeName])) {
        // Fallback untuk backward compatibility
        return $routeName;
    }
    
    $path = $routes[$routeName];
    
    // Convert relative path to absolute path by prepending project base
    // Get the project base path (e.g., /sayur_mayur_app or just /)
    $basePath = '/sayur_mayur_app/';
    
    // Make sure path starts with /
    if (strpos($path, '/') !== 0) {
        $path = '/' . $path;
    }
    
    // Prepend base path
    $path = $basePath . ltrim($path, '/');
    
    // Add query parameters if provided
    if (!empty($params)) {
        $queryString = http_build_query($params);
        $path .= '?' . $queryString;
    }
    
    return $path;
}

/**
 * Generate breadcrumb HTML from route name
 * 
 * @param string $currentRoute - Current route name
 * @return string - HTML breadcrumb
 */
function breadcrumb($currentRoute)
{
    static $navigation = null;
    static $routes = null;
    
    if ($navigation === null) {
        $navigation = require __DIR__ . '/../config/navigation.php';
        $routes = require __DIR__ . '/../config/routes.php';
    }
    
    if (!isset($navigation['breadcrumbs'][$currentRoute])) {
        return '';
    }
    
    $breadcrumbs = $navigation['breadcrumbs'][$currentRoute];
    $html = '<nav class="page-breadcrumb">';
    
    foreach ($breadcrumbs as $key => $item) {
        if (isset($item['route'])) {
            $html .= '<a href="' . htmlspecialchars(route($item['route'])) . '">';
            $html .= '<i class="bi bi-house-door"></i> ' . htmlspecialchars($item['label']);
            $html .= '</a>';
            $html .= '<span>/</span>';
        } else {
            $html .= '<span>' . htmlspecialchars($item['label']) . '</span>';
        }
    }
    
    $html .= '</nav>';
    
    return $html;
}

/**
 * Check if a route is currently active
 * 
 * @param string $routeName - Route name to check
 * @param string $currentRoute - Current active route
 * @return boolean
 */
function isRouteActive($routeName, $currentRoute)
{
    static $navigation = null;
    
    if ($navigation === null) {
        $navigation = require __DIR__ . '/../config/navigation.php';
    }
    
    // Find the menu item that corresponds to this route
    foreach ($navigation['sidebar'] as $item) {
        if ($item['route'] === $routeName) {
            return in_array($currentRoute, $item['active_when']);
        }
    }
    
    return $routeName === $currentRoute;
}

/**
 * Get breadcrumb data for current page
 * 
 * @param string $currentRoute - Current route name
 * @return array - Breadcrumb items
 */
function getBreadcrumbData($currentRoute)
{
    static $navigation = null;
    
    if ($navigation === null) {
        $navigation = require __DIR__ . '/../config/navigation.php';
    }
    
    return $navigation['breadcrumbs'][$currentRoute] ?? [];
}

/**
 * Get navigation menu
 * 
 * @return array - Sidebar menu items
 */
function getNavigation()
{
    static $navigation = null;
    
    if ($navigation === null) {
        $navigation = require __DIR__ . '/../config/navigation.php';
    }
    
    return $navigation['sidebar'] ?? [];
}
