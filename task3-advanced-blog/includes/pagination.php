<?php
/**
 * Pagination Helper Functions
 * Task 3: Advanced PHP Blog Application
 * Aerospace Internship Project
 */

/**
 * Calculate pagination data
 * @param int $totalItems Total number of items
 * @param int $currentPage Current page number
 * @param int $itemsPerPage Items per page
 * @return array Pagination data
 */
function calculatePagination($totalItems, $currentPage, $itemsPerPage) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'previous_page' => $currentPage > 1 ? $currentPage - 1 : null,
        'next_page' => $currentPage < $totalPages ? $currentPage + 1 : null,
        'start_item' => $totalItems > 0 ? $offset + 1 : 0,
        'end_item' => min($offset + $itemsPerPage, $totalItems)
    ];
}

/**
 * Generate pagination links HTML
 * @param array $pagination Pagination data from calculatePagination()
 * @param string $baseUrl Base URL for pagination links
 * @param array $params Additional URL parameters
 * @return string HTML for pagination links
 */
function generatePaginationHTML($pagination, $baseUrl, $params = []) {
    if ($pagination['total_pages'] <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Page navigation">';
    $html .= '<ul class="pagination justify-content-center">';
    
    // Previous button
    if ($pagination['has_previous']) {
        $url = buildPaginationUrl($baseUrl, $pagination['previous_page'], $params);
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($url) . '">';
        $html .= '<i class="fas fa-chevron-left me-1"></i>Previous';
        $html .= '</a>';
        $html .= '</li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<span class="page-link">';
        $html .= '<i class="fas fa-chevron-left me-1"></i>Previous';
        $html .= '</span>';
        $html .= '</li>';
    }
    
    // Page numbers
    $startPage = max(1, $pagination['current_page'] - 2);
    $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
    
    // First page if not in range
    if ($startPage > 1) {
        $url = buildPaginationUrl($baseUrl, 1, $params);
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($url) . '">1</a>';
        $html .= '</li>';
        
        if ($startPage > 2) {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link">...</span>';
            $html .= '</li>';
        }
    }
    
    // Page range
    for ($i = $startPage; $i <= $endPage; $i++) {
        $url = buildPaginationUrl($baseUrl, $i, $params);
        $isActive = $i == $pagination['current_page'];
        
        $html .= '<li class="page-item' . ($isActive ? ' active' : '') . '">';
        if ($isActive) {
            $html .= '<span class="page-link">' . $i . '</span>';
        } else {
            $html .= '<a class="page-link" href="' . htmlspecialchars($url) . '">' . $i . '</a>';
        }
        $html .= '</li>';
    }
    
    // Last page if not in range
    if ($endPage < $pagination['total_pages']) {
        if ($endPage < $pagination['total_pages'] - 1) {
            $html .= '<li class="page-item disabled">';
            $html .= '<span class="page-link">...</span>';
            $html .= '</li>';
        }
        
        $url = buildPaginationUrl($baseUrl, $pagination['total_pages'], $params);
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($url) . '">' . $pagination['total_pages'] . '</a>';
        $html .= '</li>';
    }
    
    // Next button
    if ($pagination['has_next']) {
        $url = buildPaginationUrl($baseUrl, $pagination['next_page'], $params);
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($url) . '">';
        $html .= 'Next<i class="fas fa-chevron-right ms-1"></i>';
        $html .= '</a>';
        $html .= '</li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<span class="page-link">';
        $html .= 'Next<i class="fas fa-chevron-right ms-1"></i>';
        $html .= '</span>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Build pagination URL with parameters
 * @param string $baseUrl Base URL
 * @param int $page Page number
 * @param array $params Additional parameters
 * @return string Complete URL
 */
function buildPaginationUrl($baseUrl, $page, $params = []) {
    $params['page'] = $page;
    
    $queryString = http_build_query($params);
    $separator = strpos($baseUrl, '?') !== false ? '&' : '?';
    
    return $baseUrl . $separator . $queryString;
}

/**
 * Generate pagination info text
 * @param array $pagination Pagination data
 * @return string Pagination info text
 */
function generatePaginationInfo($pagination) {
    if ($pagination['total_items'] == 0) {
        return 'No items found';
    }
    
    return sprintf(
        'Showing %d to %d of %d results',
        $pagination['start_item'],
        $pagination['end_item'],
        $pagination['total_items']
    );
}

/**
 * Generate items per page selector
 * @param int $currentPerPage Current items per page
 * @param array $options Available options
 * @param string $baseUrl Base URL
 * @param array $params Additional parameters
 * @return string HTML for per page selector
 */
function generatePerPageSelector($currentPerPage, $options = [5, 10, 20, 50], $baseUrl = '', $params = []) {
    $html = '<div class="d-flex align-items-center">';
    $html .= '<label class="form-label me-2 mb-0">Show:</label>';
    $html .= '<select class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">';
    
    foreach ($options as $option) {
        $selected = $option == $currentPerPage ? ' selected' : '';
        $html .= '<option value="' . $option . '"' . $selected . '>' . $option . '</option>';
    }
    
    $html .= '</select>';
    $html .= '<span class="ms-2">per page</span>';
    $html .= '</div>';
    
    // Add JavaScript function
    $html .= '<script>';
    $html .= 'function changePerPage(perPage) {';
    $html .= '  const url = new URL(window.location);';
    $html .= '  url.searchParams.set("per_page", perPage);';
    $html .= '  url.searchParams.set("page", 1);';
    $html .= '  window.location = url.toString();';
    $html .= '}';
    $html .= '</script>';
    
    return $html;
}

/**
 * Get pagination parameters from URL
 * @param int $defaultPerPage Default items per page
 * @return array Pagination parameters
 */
function getPaginationParams($defaultPerPage = 10) {
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, intval($_GET['per_page'])) : $defaultPerPage;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    // Limit per page to reasonable values
    $perPage = min($perPage, 100);
    
    return [
        'page' => $page,
        'per_page' => $perPage,
        'search' => $search
    ];
}

/**
 * Generate compact pagination for mobile
 * @param array $pagination Pagination data
 * @param string $baseUrl Base URL
 * @param array $params Additional parameters
 * @return string HTML for compact pagination
 */
function generateCompactPagination($pagination, $baseUrl, $params = []) {
    if ($pagination['total_pages'] <= 1) {
        return '';
    }
    
    $html = '<div class="d-flex justify-content-between align-items-center">';
    
    // Previous button
    if ($pagination['has_previous']) {
        $url = buildPaginationUrl($baseUrl, $pagination['previous_page'], $params);
        $html .= '<a href="' . htmlspecialchars($url) . '" class="btn btn-outline-primary btn-sm">';
        $html .= '<i class="fas fa-chevron-left"></i> Previous';
        $html .= '</a>';
    } else {
        $html .= '<span class="btn btn-outline-secondary btn-sm disabled">';
        $html .= '<i class="fas fa-chevron-left"></i> Previous';
        $html .= '</span>';
    }
    
    // Page info
    $html .= '<span class="text-muted">';
    $html .= 'Page ' . $pagination['current_page'] . ' of ' . $pagination['total_pages'];
    $html .= '</span>';
    
    // Next button
    if ($pagination['has_next']) {
        $url = buildPaginationUrl($baseUrl, $pagination['next_page'], $params);
        $html .= '<a href="' . htmlspecialchars($url) . '" class="btn btn-outline-primary btn-sm">';
        $html .= 'Next <i class="fas fa-chevron-right"></i>';
        $html .= '</a>';
    } else {
        $html .= '<span class="btn btn-outline-secondary btn-sm disabled">';
        $html .= 'Next <i class="fas fa-chevron-right"></i>';
        $html .= '</span>';
    }
    
    $html .= '</div>';
    
    return $html;
}
?>
