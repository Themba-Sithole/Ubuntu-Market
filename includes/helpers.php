<?php
// helper functions used across the site
// mostly for fixing links when pages are in /pages/ vs the root folder

function env_or_default(string $key, string $default = ''): string
{
    $value = getenv($key);
    return ($value !== false && $value !== '') ? $value : $default;
}

// true when the current file is inside /pages/
function is_pages_directory()
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    return strpos($script, '/pages/') !== false;
}

// builds the right image/css path depending on where we are
function asset_url($path)
{
    if ($path === null || trim($path) === '') {
        return is_pages_directory() ? '../images/placeholder.png' : 'images/placeholder.png';
    }
    $path = trim($path);
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    $path = ltrim(str_replace('\\', '/', $path), '/');
    $prefix = is_pages_directory() ? '../' : '';

    return $prefix . $path;
}

function home_url()
{
    return is_pages_directory() ? '../index.php' : 'index.php';
}

// builds internal links and keeps query strings for filters/pagination
function site_url($path, $query = [])
{
    $path = trim(str_replace('\\', '/', $path), '/');
    if (is_pages_directory()) {
        if (strncmp($path, 'pages/', 6) === 0) {
            $path = substr($path, 6);
        } else {
            $path = '../' . $path;
        }
    }

    return $path . ($query ? ('?' . http_build_query($query)) : '');
}

function current_user_name()
{
    if (!empty($_SESSION['full_name'])) {
        return trim($_SESSION['full_name']);
    }
    if (!empty($_SESSION['username'])) {
        return trim($_SESSION['username']);
    }
    if (!empty($_SESSION['email'])) {
        return preg_replace('/@.*$/', '', $_SESSION['email']);
    }
    return 'Member';
}

function pages_redirect_url($page, $query = [])
{
    if (!preg_match('/^[a-z0-9._-]+\.php$/i', $page)) {
        $page = 'discovery.php';
    }
    $q = $query ? ('?' . http_build_query($query)) : '';

    return $page . $q;
}

// adds ?v=timestamp so the browser reloads CSS after I change styles
function css_url()
{
    $file = dirname(__DIR__) . '/CSS/style.css';
    $version = is_file($file) ? (string) filemtime($file) : '1';
    $href = is_pages_directory() ? '../CSS/style.css' : 'CSS/style.css';

    return $href . '?v=' . $version;
}

function portal_css_url()
{
    $file = dirname(__DIR__) . '/CSS/portal.css';
    $version = is_file($file) ? (string) filemtime($file) : '1';
    $href = is_pages_directory() ? '../CSS/portal.css' : 'CSS/portal.css';

    return $href . '?v=' . $version;
}

function portal_script_url()
{
    return script_url();
}

function products_per_page()
{
    return 24;
}

function paginate_page()
{
    return max(1, (int) ($_GET['page'] ?? 1));
}

function pagination_query($extra = [])
{
    $base = [];
    foreach (['search', 'category_id', 'brand_id', 'price_min', 'price_max'] as $key) {
        if (isset($_GET[$key]) && $_GET[$key] !== '') {
            $base[$key] = $_GET[$key];
        }
    }

    return array_merge($base, $extra);
}

function filter_query_except($omitKeys, $extra = [])
{
    $q = [];
    foreach (['search', 'category_id', 'brand_id', 'price_min', 'price_max'] as $key) {
        if (in_array($key, $omitKeys, true)) {
            continue;
        }
        if (isset($_GET[$key]) && $_GET[$key] !== '') {
            $q[$key] = $_GET[$key];
        }
    }

    return array_merge($q, $extra);
}

// little filter chips shown above the product grid
function active_filter_chips(
    $pagePath,
    $search,
    $categoryId,
    $brandId,
    $priceMin,
    $priceMax,
    $categories,
    $brands
) {
    $chips = [];

    if ($search !== '') {
        $chips[] = [
            'label' => 'Search: ' . $search,
            'href' => site_url($pagePath, filter_query_except(['search'])),
        ];
    }

    if ($categoryId !== '') {
        $name = 'Category';
        foreach ($categories as $cat) {
            if ((string) $cat['category_id'] === (string) $categoryId) {
                $name = $cat['name'];
                break;
            }
        }
        $chips[] = [
            'label' => $name,
            'href' => site_url($pagePath, filter_query_except(['category_id'])),
        ];
    }

    if ($brandId !== '') {
        $name = 'Brand';
        foreach ($brands as $brand) {
            if ((string) $brand['brand_id'] === (string) $brandId) {
                $name = $brand['name'];
                break;
            }
        }
        $chips[] = [
            'label' => $name,
            'href' => site_url($pagePath, filter_query_except(['brand_id'])),
        ];
    }

    if ($priceMin !== '' && is_numeric($priceMin)) {
        $chips[] = [
            'label' => 'From R ' . number_format((float) $priceMin, 0),
            'href' => site_url($pagePath, filter_query_except(['price_min'])),
        ];
    }

    if ($priceMax !== '' && is_numeric($priceMax)) {
        $chips[] = [
            'label' => 'Up to R ' . number_format((float) $priceMax, 0),
            'href' => site_url($pagePath, filter_query_except(['price_max'])),
        ];
    }

    return $chips;
}

function filters_css_url()
{
    $file = dirname(__DIR__) . '/CSS/filters.css';
    $version = is_file($file) ? (string) filemtime($file) : '1';
    $href = is_pages_directory() ? '../CSS/filters.css' : 'CSS/filters.css';

    return $href . '?v=' . $version;
}

// cache categories in session for a bit so every page doesn't query the DB
function get_categories($pdo)
{
    $cacheKey = '_cache_categories';
    if (
        !empty($_SESSION[$cacheKey])
        && is_array($_SESSION[$cacheKey])
        && (time() - (int) ($_SESSION[$cacheKey]['t'] ?? 0)) < 900
    ) {
        return $_SESSION[$cacheKey]['data'];
    }

    $data = $pdo->query('SELECT category_id, name FROM categories ORDER BY name ASC')->fetchAll();
    $_SESSION[$cacheKey] = ['t' => time(), 'data' => $data];

    return $data;
}

// same caching idea as categories
function get_brands($pdo)
{
    $cacheKey = '_cache_brands';
    if (
        !empty($_SESSION[$cacheKey])
        && is_array($_SESSION[$cacheKey])
        && (time() - (int) ($_SESSION[$cacheKey]['t'] ?? 0)) < 900
    ) {
        return $_SESSION[$cacheKey]['data'];
    }

    $data = $pdo->query('SELECT brand_id, name FROM brands ORDER BY name ASC')->fetchAll();
    $_SESSION[$cacheKey] = ['t' => time(), 'data' => $data];

    return $data;
}

function script_url()
{
    $file = dirname(__DIR__) . '/script.js';
    $version = is_file($file) ? (string) filemtime($file) : '1';
    $href = is_pages_directory() ? '../script.js' : 'script.js';

    return $href . '?v=' . $version;
}

function header_nav_css_url()
{
    $file = dirname(__DIR__) . '/CSS/header-nav.css';
    $version = is_file($file) ? (string) filemtime($file) : '1';
    $href = is_pages_directory() ? '../CSS/header-nav.css' : 'CSS/header-nav.css';

    return $href . '?v=' . $version;
}

function hero_css_url()
{
    $file = dirname(__DIR__) . '/CSS/hero.css';
    $version = is_file($file) ? (string) filemtime($file) : '1';
    $href = is_pages_directory() ? '../CSS/hero.css' : 'CSS/hero.css';

    return $href . '?v=' . $version;
}

function footer_css_url()
{
    $file = dirname(__DIR__) . '/CSS/footer.css';
    $version = is_file($file) ? (string) filemtime($file) : '1';
    $href = is_pages_directory() ? '../CSS/footer.css' : 'CSS/footer.css';

    return $href . '?v=' . $version;
}

function responsive_css_url()
{
    $file = dirname(__DIR__) . '/CSS/responsive.css';
    $version = is_file($file) ? (string) filemtime($file) : '1';
    $href = is_pages_directory() ? '../CSS/responsive.css' : 'CSS/responsive.css';

    return $href . '?v=' . $version;
}
