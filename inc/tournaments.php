<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Compact tournament calendar manager.
 *
 * v3.4 replaces the one-post-per-tournament workflow with one fast, sortable
 * month-by-month admin screen stored in a single non-autoloaded option.
 * Existing zolei_tournament posts are kept hidden for rollback/migration only.
 */

function zolei_register_tournament_type() {
    register_post_type('zolei_tournament', array(
        'labels' => array('name' => __('Legacy tournaments','zolei-react')),
        'public' => false,
        'show_ui' => false,
        'show_in_menu' => false,
        'show_in_rest' => false,
        'supports' => array('title','editor'),
        'has_archive' => false,
        'rewrite' => false,
    ));
}
add_action('init','zolei_register_tournament_type');

function zolei_calendar_month_definitions() {
    return array(
        array('slug'=>'janvaris','lv'=>'Janvāris','en'=>'January'),
        array('slug'=>'februaris','lv'=>'Februāris','en'=>'February'),
        array('slug'=>'marts','lv'=>'Marts','en'=>'March'),
        array('slug'=>'aprilis','lv'=>'Aprīlis','en'=>'April'),
        array('slug'=>'maijs','lv'=>'Maijs','en'=>'May'),
        array('slug'=>'junijs','lv'=>'Jūnijs','en'=>'June'),
        array('slug'=>'julijs','lv'=>'Jūlijs','en'=>'July'),
        array('slug'=>'augusts','lv'=>'Augusts','en'=>'August'),
        array('slug'=>'septembris','lv'=>'Septembris','en'=>'September'),
        array('slug'=>'oktobris','lv'=>'Oktobris','en'=>'October'),
        array('slug'=>'novembris','lv'=>'Novembris','en'=>'November'),
        array('slug'=>'decembris','lv'=>'Decembris','en'=>'December'),
    );
}

function zolei_compact_event_id($text, $date = '', $source = '') {
    return 'evt_' . substr(md5((string)$date . '|' . trim((string)$text) . '|' . (string)$source), 0, 14);
}

function zolei_compact_clean_links($links) {
    $out = array();
    if (!is_array($links)) { return $out; }
    foreach ($links as $link) {
        if (!is_array($link)) { continue; }
        $url = esc_url_raw($link['url'] ?? '');
        if ($url === '') { continue; }
        $out[] = array(
            'url' => $url,
            'label' => sanitize_text_field($link['label'] ?? ''),
        );
        if (count($out) >= 4) { break; }
    }
    return $out;
}

function zolei_compact_detect_event_date($text, $fallback_month = 0, $fallback_year = 0) {
    $text = (string) $text;
    if (preg_match('/\b([0-3]?\d)\s*[.\/-]\s*([01]?\d)\s*[.\/-]\s*(20\d{2})\b/u', $text, $m)) {
        $day = max(1, min(31, (int)$m[1]));
        $month = max(1, min(12, (int)$m[2]));
        $year = max(2000, min(2100, (int)$m[3]));
        if ($fallback_year && $year === ((int)$fallback_year - 1) && preg_match('/čempionāts\s+(20\d{2})/iu', $text, $title_year_match)) {
            $title_year = (int)$title_year_match[1];
            if ($title_year === (int)$fallback_year) { $year = $title_year; }
        }
        return array('date'=>sprintf('%04d-%02d-%02d',$year,$month,$day),'year'=>$year,'month'=>$month,'day'=>$day);
    }
    if ($fallback_year && preg_match('/\b([0-3]?\d)\s*\.\s*([01]?\d)\s*\.(?!\d)/u', $text, $m)) {
        $day = max(1, min(31, (int)$m[1]));
        $month = max(1, min(12, (int)$m[2]));
        return array('date'=>sprintf('%04d-%02d-%02d',$fallback_year,$month,$day),'year'=>$fallback_year,'month'=>$month,'day'=>$day);
    }
    if ($fallback_year && $fallback_month) {
        return array('date'=>'','year'=>(int)$fallback_year,'month'=>(int)$fallback_month,'day'=>0);
    }
    return array('date'=>'','year'=>0,'month'=>0,'day'=>0);
}

function zolei_compact_normalize_event($row, $fallback_month = 0, $fallback_year = 0, $fallback_source = 'manual') {
    if (!is_array($row)) { return null; }
    $text = sanitize_textarea_field(wp_unslash($row['text'] ?? ($row['description'] ?? ($row['title'] ?? ''))));
    $text = trim(preg_replace('/\s+/u', ' ', $text));
    if ($text === '') { return null; }

    $date = sanitize_text_field($row['date'] ?? '');
    $year = absint($row['year'] ?? 0);
    $month = absint($row['month'] ?? 0);
    $day = absint($row['day'] ?? 0);
    if ($date && preg_match('/^(20\d{2})-(\d{2})-(\d{2})$/', $date, $m)) {
        $year = (int)$m[1]; $month = (int)$m[2]; $day = (int)$m[3];
    } else {
        $detected = zolei_compact_detect_event_date($text, $fallback_month ?: $month, $fallback_year ?: $year);
        if (!$year) { $year = $detected['year']; }
        if (!$month) { $month = $detected['month']; }
        if (!$day) { $day = $detected['day']; }
        if (!$date) { $date = $detected['date']; }
    }
    if ($year < 2000 || $year > 2100) { $year = $fallback_year ?: (int) current_time('Y'); }
    if ($month < 1 || $month > 12) { $month = $fallback_month ?: 1; }
    if ($day < 0 || $day > 31) { $day = 0; }
    if ($date && !preg_match('/^20\d{2}-\d{2}-\d{2}$/', $date)) { $date = ''; }

    $source = sanitize_key($row['source'] ?? $fallback_source);
    if ($source === '') { $source = $fallback_source; }
    $id = sanitize_key($row['id'] ?? '');
    if ($id === '') { $id = zolei_compact_event_id($text, $date, $source); }

    return array(
        'id' => $id,
        'date' => $date,
        'year' => $year,
        'month' => $month,
        'day' => $day,
        'text' => $text,
        'links' => zolei_compact_clean_links($row['links'] ?? array()),
        'source' => $source,
        'order' => absint($row['order'] ?? 0),
    );
}

function zolei_compact_tournaments() {
    $rows = get_option('zolei_compact_tournaments_v1', array());
    if (!is_array($rows)) { $rows = array(); }
    $clean = array();
    foreach ($rows as $row) {
        $event = zolei_compact_normalize_event($row);
        if ($event) { $clean[] = $event; }
    }
    usort($clean, function($a,$b){
        if ($a['year'] !== $b['year']) { return $a['year'] <=> $b['year']; }
        if ($a['month'] !== $b['month']) { return $a['month'] <=> $b['month']; }
        // Respect the admin drag/drop order. Date is only a stable fallback for migrated rows.
        $ao = (int)($a['order'] ?? 0); $bo = (int)($b['order'] ?? 0);
        if ($ao !== $bo) { return $ao <=> $bo; }
        $ad = $a['day'] ?: 99; $bd = $b['day'] ?: 99;
        return $ad <=> $bd;
    });
    return $clean;
}

function zolei_compact_save_tournaments($rows) {
    $clean = array(); $seen = array();
    foreach ((array)$rows as $row) {
        $event = zolei_compact_normalize_event($row);
        if (!$event) { continue; }
        $key = md5($event['year'].'|'.$event['month'].'|'.$event['date'].'|'.(function_exists('mb_strtolower') ? mb_strtolower($event['text'],'UTF-8') : strtolower($event['text'])));
        if (isset($seen[$key])) { continue; }
        $seen[$key] = true;
        $clean[] = $event;
    }
    update_option('zolei_compact_tournaments_v1', $clean, false);
    return $clean;
}

function zolei_import_tournament_source_date($text, $month_index, $fallback_year) {
    $d = zolei_compact_detect_event_date($text, $month_index, $fallback_year);
    return $d['date'] ?: sprintf('%04d-%02d-01', $fallback_year, $month_index);
}

function zolei_compact_rows_from_months($source, $source_name = 'legacy-option', $fallback_year = 0) {
    $rows = array();
    $fallback_year = $fallback_year ?: (int) current_time('Y');
    if (!is_array($source)) { return $rows; }
    foreach (array_values($source) as $mi=>$month) {
        $events = isset($month['events']) && is_array($month['events']) ? $month['events'] : array();
        foreach ($events as $order=>$event) {
            if (!is_array($event)) { continue; }
            $text = trim((string)($event['title'] ?? ($event['text'] ?? ($event['description'] ?? ''))));
            if ($text === '') { continue; }
            $detected = zolei_compact_detect_event_date($text, $mi+1, $fallback_year);
            // Correct an obvious source typo when the event name itself states the following
            // championship year (for example a 2026 championship accidentally dated 17.05.2025).
            if ($detected['year'] && preg_match('/čempionāts\s+(20\d{2})/iu', $text, $title_year_match)) {
                $title_year = (int)$title_year_match[1];
                if ($title_year === ((int)$detected['year'] + 1) && $title_year === (int)$fallback_year) {
                    $detected['year'] = $title_year;
                }
            }
            $day_hint = absint($event['day'] ?? 0);
            // Bundled/legacy month data already carries the displayed day; prefer that hint for
            // ranges like 11.-12.07 and for rows where score notation (for example 10/28/1)
            // must never be mistaken for a calendar date.
            if ($day_hint >= 1 && $day_hint <= 31) {
                $detected['day'] = $day_hint;
                $detected['date'] = sprintf('%04d-%02d-%02d', $detected['year'] ?: $fallback_year, $detected['month'] ?: ($mi+1), $day_hint);
            }
            $links = isset($event['links']) && is_array($event['links']) ? $event['links'] : array();
            $rows[] = array(
                'id'=>zolei_compact_event_id($text, $detected['date'], $source_name),
                'date'=>$detected['date'], 'year'=>$detected['year'], 'month'=>$detected['month'], 'day'=>$detected['day'],
                'text'=>$text, 'links'=>$links, 'source'=>$source_name, 'order'=>$order,
            );
        }
    }
    return $rows;
}

function zolei_compact_bootstrap_legacy() {
    if (get_option('zolei_compact_tournaments_bootstrap_340_done')) { return; }
    $rows = zolei_compact_tournaments();
    if (!$rows) {
        $fallback_year = (int) current_time('Y');

        // The theme ships with a recent snapshot collected from zolei.lv. Use it first so
        // the frontend never becomes empty merely because an older database option is stale.
        $bundled = function_exists('zolei_default_months') ? zolei_default_months() : array();
        $rows = zolei_compact_rows_from_months($bundled, 'zolei.lv-snapshot', $fallback_year);
        $covered_bundled = array();
        foreach ($rows as $row) { $covered_bundled[$row['year'].'-'.$row['month']] = true; }

        // Preserve complete historical month/year groups from the database only when the bundled
        // zolei.lv snapshot does not already provide that exact month/year. This avoids doubles
        // without dropping all but the first legacy event in a missing month.
        $legacy = get_option('zolei_calendar_months');
        $legacy_added_keys = array();
        foreach (zolei_compact_rows_from_months($legacy, 'legacy-option', $fallback_year) as $row) {
            $key = $row['year'].'-'.$row['month'];
            if (isset($covered_bundled[$key])) { continue; }
            $rows[] = $row; $legacy_added_keys[$key] = true;
        }
        $covered = $covered_bundled + $legacy_added_keys;

        // Keep genuinely separate legacy CPT month/year data for rollback/manual history, but do
        // not duplicate the bulk records that v3.3 generated from the same old calendar option.
        $posts = get_posts(array('post_type'=>'zolei_tournament','post_status'=>'any','posts_per_page'=>-1,'orderby'=>'ID','order'=>'ASC','suppress_filters'=>true));
        foreach ($posts as $post) {
            $text = trim(wp_strip_all_tags($post->post_content ?: $post->post_title));
            if ($text === '') { continue; }
            $date = (string)get_post_meta($post->ID,'_zolei_event_date',true);
            $detected = zolei_compact_detect_event_date($date . ' ' . $text, 0, $fallback_year);
            $key = $detected['year'].'-'.$detected['month'];
            if (!$detected['year'] || !$detected['month'] || isset($covered[$key])) { continue; }
            $links = json_decode((string)get_post_meta($post->ID,'_zolei_event_links',true), true);
            $rows[] = array(
                'id'=>'legacy_'.$post->ID,
                'date'=>$date ?: $detected['date'], 'year'=>$detected['year'], 'month'=>$detected['month'], 'day'=>$detected['day'],
                'text'=>$text, 'links'=>is_array($links)?$links:array(), 'source'=>'legacy-cpt', 'order'=>$post->ID,
            );
            $covered[$key] = true;
        }
        zolei_compact_save_tournaments($rows);
    }
    update_option('zolei_compact_tournaments_bootstrap_340_done', 1, false);
}
add_action('admin_init','zolei_compact_bootstrap_legacy',20);

function zolei_compact_absolute_url($url, $base) {
    $url = html_entity_decode(trim((string)$url), ENT_QUOTES, 'UTF-8');
    if ($url === '') { return ''; }
    if (preg_match('~^https?://~i', $url)) { return esc_url_raw($url); }
    if (strpos($url, '//') === 0) { return esc_url_raw('https:' . $url); }
    return esc_url_raw(trailingslashit($base) . ltrim($url, '/'));
}

function zolei_compact_dom_node_text($node) {
    $text = $node ? $node->textContent : '';
    $text = html_entity_decode((string)$text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim(preg_replace('/\s+/u', ' ', $text));
}

function zolei_compact_parse_month_html($html, $month_number, $slug, $base_url = 'https://zolei.lv') {
    if (!class_exists('DOMDocument') || trim((string)$html) === '') { return array(); }
    $dom = new DOMDocument();
    $prev = libxml_use_internal_errors(true);
    $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . (string)$html);
    libxml_clear_errors(); libxml_use_internal_errors($prev);
    if (!$loaded) { return array(); }
    $xp = new DOMXPath($dom);
    $nodes = $xp->query('//li');
    if (!$nodes || !$nodes->length) { $nodes = $xp->query('//p'); }
    $raw = array(); $years = array();
    foreach ($nodes as $node) {
        $text = zolei_compact_dom_node_text($node);
        if ($text === '' || (function_exists('mb_strlen') ? mb_strlen($text,'UTF-8') : strlen($text)) < 8) { continue; }
        if (preg_match('/^(EN|LV)$/iu',$text)) { continue; }
        // Page content items should represent calendar rows. Ignore generic nav-like one-word items.
        if (!preg_match('/\d|turn|kauss|reit|čemp|zolit|zoles|zolīt/u', function_exists('mb_strtolower') ? mb_strtolower($text,'UTF-8') : strtolower($text))) { continue; }
        if (preg_match_all('/\b(20\d{2})\b/u', $text, $ym)) {
            foreach ($ym[1] as $y) { $years[(int)$y] = ($years[(int)$y] ?? 0) + 1; }
        }
        $links = array();
        $link_nodes = $xp->query('.//a[@href]', $node);
        if ($link_nodes) {
            foreach ($link_nodes as $a) {
                $href = zolei_compact_absolute_url($a->getAttribute('href'), $base_url);
                if ($href === '') { continue; }
                $label = zolei_compact_dom_node_text($a);
                $links[] = array('url'=>$href,'label'=>$label ?: __('Document','zolei-react'));
            }
        }
        $raw[] = array('text'=>$text,'links'=>$links);
    }
    if (!$raw) { return array(); }
    $default_year = (int) current_time('Y');
    if ($years) { arsort($years); $default_year = (int) array_key_first($years); }
    $rows = array();
    foreach ($raw as $order=>$item) {
        $detected = zolei_compact_detect_event_date($item['text'], $month_number, $default_year);
        if (!$detected['year']) { $detected['year'] = $default_year; }
        if (!$detected['month']) { $detected['month'] = $month_number; }
        $rows[] = array(
            'id'=>zolei_compact_event_id($item['text'], $detected['date'], 'zolei.lv'),
            'date'=>$detected['date'], 'year'=>$detected['year'], 'month'=>$detected['month'], 'day'=>$detected['day'],
            'text'=>$item['text'], 'links'=>$item['links'], 'source'=>'zolei.lv', 'order'=>$order,
        );
    }
    return $rows;
}

function zolei_compact_remote_source_url() {
    $url = esc_url_raw(get_option('zolei_tournament_source_url','https://zolei.lv'));
    return $url ?: 'https://zolei.lv';
}

function zolei_compact_fetch_remote_months() {
    $base = untrailingslashit(zolei_compact_remote_source_url());
    $defs = zolei_calendar_month_definitions();
    $wanted = array(); foreach ($defs as $i=>$d) { $wanted[$d['slug']] = $i+1; }
    $found = array();

    // Fast path: fetch all old WordPress pages in one REST request.
    $rest = add_query_arg(array('per_page'=>100,'_fields'=>'slug,content,link'), trailingslashit($base) . 'wp-json/wp/v2/pages');
    $response = wp_remote_get($rest, array('timeout'=>12,'redirection'=>5,'headers'=>array('Accept'=>'application/json','User-Agent'=>'Zolei-Migration/3.4')));
    if (!is_wp_error($response) && (int)wp_remote_retrieve_response_code($response) === 200) {
        $pages = json_decode((string)wp_remote_retrieve_body($response), true);
        if (is_array($pages)) {
            foreach ($pages as $page) {
                $slug = sanitize_title($page['slug'] ?? '');
                if (!isset($wanted[$slug])) { continue; }
                $html = (string)($page['content']['rendered'] ?? '');
                $rows = zolei_compact_parse_month_html($html, $wanted[$slug], $slug, $base);
                if ($rows) { $found[$slug] = $rows; }
            }
        }
    }

    // Fallback for servers where the old REST API is disabled/blocked.
    foreach ($wanted as $slug=>$month_number) {
        if (isset($found[$slug])) { continue; }
        $url = trailingslashit($base) . $slug . '/';
        $r = wp_remote_get($url, array('timeout'=>8,'redirection'=>5,'headers'=>array('Accept'=>'text/html','User-Agent'=>'Zolei-Migration/3.4')));
        if (is_wp_error($r) || (int)wp_remote_retrieve_response_code($r) !== 200) { continue; }
        $body = (string)wp_remote_retrieve_body($r);
        if ($body === '') { continue; }
        if (class_exists('DOMDocument')) {
            $dom = new DOMDocument(); $prev=libxml_use_internal_errors(true); $dom->loadHTML('<?xml encoding="utf-8" ?>'.$body); libxml_clear_errors(); libxml_use_internal_errors($prev);
            $xp = new DOMXPath($dom);
            $entry = $xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," entry-content ")]')->item(0);
            if (!$entry) { $entry = $xp->query('//article')->item(0); }
            if ($entry) {
                $html = ''; foreach ($entry->childNodes as $child) { $html .= $dom->saveHTML($child); }
                $body = $html ?: $body;
            }
        }
        $rows = zolei_compact_parse_month_html($body, $month_number, $slug, $base);
        if ($rows) { $found[$slug] = $rows; }
    }
    return $found;
}

function zolei_compact_remote_sync() {
    $months = zolei_compact_fetch_remote_months();
    if (!$months) { return new WP_Error('zolei_sync_failed', __('Could not read tournament month pages from zolei.lv. Existing calendar data was kept.','zolei-react')); }
    $existing = zolei_compact_tournaments();
    $existing_by_text = array();
    foreach ($existing as $old_row) {
        $canon = trim(preg_replace('/\s+/u',' ', function_exists('mb_strtolower') ? mb_strtolower($old_row['text'],'UTF-8') : strtolower($old_row['text'])));
        if ($canon !== '') { $existing_by_text[md5($canon)] = $old_row; }
    }
    $replacement_keys = array(); $incoming = array();
    foreach ($months as $rows) {
        foreach ($rows as $row) {
            $event = zolei_compact_normalize_event($row);
            if (!$event) { continue; }
            // Some live zolei.lv rows intentionally omit the date in their text. Reuse the
            // packaged snapshot's known day when the row text is otherwise the same.
            if (!$event['day']) {
                $canon = trim(preg_replace('/\s+/u',' ', function_exists('mb_strtolower') ? mb_strtolower($event['text'],'UTF-8') : strtolower($event['text'])));
                $prev = $canon !== '' ? ($existing_by_text[md5($canon)] ?? null) : null;
                if (is_array($prev) && (int)$prev['month'] === (int)$event['month'] && !empty($prev['day'])) {
                    $event['day'] = (int)$prev['day'];
                    $event['date'] = sprintf('%04d-%02d-%02d',(int)$event['year'],(int)$event['month'],(int)$event['day']);
                }
            }
            $replacement_keys[$event['year'].'-'.$event['month']] = true;
            $incoming[] = $event;
        }
    }
    $keep = array();
    foreach ($existing as $row) {
        $key = $row['year'].'-'.$row['month'];
        $is_imported = in_array($row['source'], array('zolei.lv','zolei.lv-snapshot','legacy-option','legacy-cpt'), true);
        if ($is_imported && isset($replacement_keys[$key])) { continue; }
        $keep[] = $row;
    }
    $merged = zolei_compact_save_tournaments(array_merge($keep, $incoming));
    update_option('zolei_compact_last_sync', array('time'=>current_time('mysql'),'count'=>count($incoming),'months'=>count($months),'source'=>zolei_compact_remote_source_url()), false);
    update_option('zolei_compact_remote_sync_version','3.4.0',false);
    return count($incoming);
}

function zolei_compact_maybe_remote_sync_once() {
    if (!is_admin() || wp_doing_ajax() || !current_user_can('edit_posts')) { return; }
    zolei_compact_bootstrap_legacy();
    if (get_option('zolei_compact_remote_sync_version') === '3.4.0') { return; }
    $last_attempt = absint(get_option('zolei_compact_remote_sync_attempt',0));
    if ($last_attempt && (time() - $last_attempt) < 6 * HOUR_IN_SECONDS) { return; }
    update_option('zolei_compact_remote_sync_attempt', time(), false);
    zolei_compact_remote_sync();
}
add_action('admin_init','zolei_compact_maybe_remote_sync_once',60);

function zolei_tournament_years() {
    $years = array();
    foreach (zolei_compact_tournaments() as $row) { if ($row['year']) { $years[$row['year']] = $row['year']; } }
    $current = (int) current_time('Y');
    if (!$years) { $years[$current] = $current; }
    ksort($years, SORT_NUMERIC);
    return array_values($years);
}

function zolei_calendar_year() {
    $years = zolei_tournament_years();
    if (!empty($_GET['year'])) {
        $year = absint($_GET['year']);
        if ($year >= 2000 && $year <= 2100) { return $year; }
    }
    $saved = absint(get_option('zolei_calendar_default_year',0));
    if ($saved >= 2000 && $saved <= 2100 && in_array($saved,$years,true)) { return $saved; }
    $current = (int) current_time('Y');
    if (in_array($current,$years,true)) { return $current; }
    return $years ? (int)max($years) : $current;
}

function zolei_dynamic_calendar_months($year = null) {
    $year = $year ? absint($year) : zolei_calendar_year();
    $months = zolei_calendar_month_definitions();
    foreach ($months as $i=>$month) { $months[$i]['events']=array(); $months[$i]['year']=$year; }
    foreach (zolei_compact_tournaments() as $row) {
        if ((int)$row['year'] !== (int)$year || $row['month'] < 1 || $row['month'] > 12) { continue; }
        $months[$row['month']-1]['events'][] = array(
            'id'=>$row['id'],
            'date'=>$row['date'],
            'day'=>$row['day'] ? sprintf('%02d',$row['day']) : '',
            'title'=>'',
            'description'=>$row['text'],
            'time'=>'', 'location'=>'', 'format'=>'', 'contact'=>'',
            'links'=>$row['links'],
        );
    }
    return $months;
}

function zolei_has_dynamic_tournaments() { return count(zolei_compact_tournaments()) > 0; }
function zolei_calendar_months_from_source($legacy_callback = null) {
    if (zolei_has_dynamic_tournaments()) { return zolei_dynamic_calendar_months(); }
    return is_callable($legacy_callback) ? call_user_func($legacy_callback) : array();
}

/** Backwards-compatible name from v3.3, now migrates into compact storage instead of creating posts. */
function zolei_import_legacy_tournaments_once() { zolei_compact_bootstrap_legacy(); }

function zolei_compact_tournament_admin_menu() {
    add_menu_page(__('Turnīri','zolei-react'), __('Turnīri','zolei-react'), 'edit_posts', 'zolei-tournaments', 'zolei_compact_tournament_admin_page', 'dashicons-calendar-alt', 24);
}
add_action('admin_menu','zolei_compact_tournament_admin_menu');

function zolei_compact_tournament_admin_actions() {
    if (!is_admin() || !current_user_can('edit_posts')) { return; }
    if (!empty($_POST['zolei_compact_tournament_save'])) {
        check_admin_referer('zolei_compact_tournament_save','zolei_compact_tournament_nonce');
        $year = absint($_POST['calendar_year'] ?? current_time('Y'));
        if ($year < 2000 || $year > 2100) { $year = (int) current_time('Y'); }
        $all = zolei_compact_tournaments();
        $keep = array_values(array_filter($all,function($row) use ($year){ return (int)$row['year'] !== $year; }));
        $posted = isset($_POST['events']) && is_array($_POST['events']) ? wp_unslash($_POST['events']) : array();
        $new = array();
        foreach ($posted as $month=>$month_rows) {
            $month = absint($month); if ($month < 1 || $month > 12 || !is_array($month_rows)) { continue; }
            $order = 0;
            foreach ($month_rows as $row) {
                if (!is_array($row)) { continue; }
                $text = trim((string)($row['text'] ?? '')); if ($text === '') { continue; }
                $links = array();
                for ($i=1;$i<=2;$i++) {
                    $u = esc_url_raw($row['link'.$i.'_url'] ?? '');
                    if ($u) { $links[] = array('url'=>$u,'label'=>sanitize_text_field($row['link'.$i.'_label'] ?? '')); }
                }
                $source = sanitize_key($row['source'] ?? 'manual');
                $normalized = zolei_compact_normalize_event(array(
                    'id'=>$row['id'] ?? '', 'date'=>$row['date'] ?? '', 'year'=>$year, 'month'=>$month,
                    'text'=>$text, 'links'=>$links, 'source'=>$source ?: 'manual', 'order'=>$order++,
                ), $month, $year, 'manual');
                if ($normalized) { $new[] = $normalized; }
            }
        }
        zolei_compact_save_tournaments(array_merge($keep,$new));
        update_option('zolei_calendar_default_year',$year,false);
        $source_url = esc_url_raw($_POST['zolei_tournament_source_url'] ?? '');
        if ($source_url) { update_option('zolei_tournament_source_url',untrailingslashit($source_url),false); }
        wp_safe_redirect(add_query_arg(array('page'=>'zolei-tournaments','year'=>$year,'updated'=>'1'),admin_url('admin.php'))); exit;
    }
    if (!empty($_POST['zolei_compact_tournament_sync'])) {
        check_admin_referer('zolei_compact_tournament_sync','zolei_compact_tournament_sync_nonce');
        $source_url = esc_url_raw($_POST['zolei_tournament_source_url'] ?? '');
        if ($source_url) { update_option('zolei_tournament_source_url',untrailingslashit($source_url),false); }
        $result = zolei_compact_remote_sync();
        $args = array('page'=>'zolei-tournaments','year'=>absint($_POST['calendar_year'] ?? current_time('Y')));
        if (is_wp_error($result)) { $args['sync_error']=rawurlencode($result->get_error_message()); } else { $args['synced']=absint($result); }
        wp_safe_redirect(add_query_arg($args,admin_url('admin.php'))); exit;
    }
}
add_action('admin_init','zolei_compact_tournament_admin_actions',90);

function zolei_compact_tournament_admin_assets($hook) {
    if ($hook !== 'toplevel_page_zolei-tournaments') { return; }
    wp_enqueue_script('jquery-ui-sortable');
}
add_action('admin_enqueue_scripts','zolei_compact_tournament_admin_assets');

function zolei_compact_tournament_admin_page() {
    if (!current_user_can('edit_posts')) { return; }
    zolei_compact_bootstrap_legacy();
    $years = zolei_tournament_years();
    $year = isset($_GET['year']) ? absint($_GET['year']) : zolei_calendar_year();
    if ($year < 2000 || $year > 2100) { $year = (int)current_time('Y'); }
    if (!in_array($year,$years,true)) { $years[]=$year; sort($years,SORT_NUMERIC); }
    $defs = zolei_calendar_month_definitions();
    $grouped = array_fill(1,12,array());
    foreach (zolei_compact_tournaments() as $row) { if ((int)$row['year']===$year && isset($grouped[$row['month']])) { $grouped[$row['month']][]=$row; } }
    $current_month = ((int)$year === (int)current_time('Y')) ? (int)current_time('n') : 1;
    $last_sync = get_option('zolei_compact_last_sync',array());
    ?>
    <div class="wrap zole-compact-admin">
      <h1><?php esc_html_e('Turnīri — ātra mēnešu pārvaldība','zolei-react'); ?></h1>
      <p class="description"><?php esc_html_e('Vairs nav jāveido viens CPT ieraksts katram turnīram. Rediģē visu gadu vienā ekrānā, velc rindas pareizajā secībā un pievieno vairākus turnīrus uzreiz.','zolei-react'); ?></p>
      <?php if (!empty($_GET['updated'])): ?><div class="notice notice-success is-dismissible"><p><?php esc_html_e('Turnīru kalendārs saglabāts.','zolei-react'); ?></p></div><?php endif; ?>
      <?php if (isset($_GET['synced'])): ?><div class="notice notice-success is-dismissible"><p><?php printf(esc_html__('No zolei.lv sinhronizēti %d turnīru ieraksti.','zolei-react'),absint($_GET['synced'])); ?></p></div><?php endif; ?>
      <?php if (!empty($_GET['sync_error'])): ?><div class="notice notice-warning"><p><?php echo esc_html(wp_unslash($_GET['sync_error'])); ?></p></div><?php endif; ?>

      <div class="zole-toolbar-card">
        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="zole-year-form">
          <input type="hidden" name="page" value="zolei-tournaments">
          <label><strong><?php esc_html_e('Gads','zolei-react'); ?></strong>
            <select name="year" onchange="this.form.submit()">
              <?php foreach($years as $y): ?><option value="<?php echo esc_attr($y); ?>" <?php selected($year,$y); ?>><?php echo esc_html($y); ?></option><?php endforeach; ?>
              <?php if(!in_array($year+1,$years,true)): ?><option value="<?php echo esc_attr($year+1); ?>"><?php echo esc_html($year+1); ?></option><?php endif; ?>
            </select>
          </label>
        </form>
        <form method="post" class="zole-sync-form">
          <?php wp_nonce_field('zolei_compact_tournament_sync','zolei_compact_tournament_sync_nonce'); ?>
          <input type="hidden" name="calendar_year" value="<?php echo esc_attr($year); ?>">
          <label><strong><?php esc_html_e('Avots','zolei-react'); ?></strong><input type="url" name="zolei_tournament_source_url" value="<?php echo esc_attr(zolei_compact_remote_source_url()); ?>"></label>
          <button class="button button-secondary" type="submit" name="zolei_compact_tournament_sync" value="1"><?php esc_html_e('Sinhronizēt visus 12 mēnešus no zolei.lv','zolei-react'); ?></button>
          <?php if(is_array($last_sync) && !empty($last_sync['time'])): ?><span class="description"><?php echo esc_html(sprintf(__('Pēdējā sinhronizācija: %s (%d ieraksti)','zolei-react'),$last_sync['time'],absint($last_sync['count'] ?? 0))); ?></span><?php endif; ?>
        </form>
      </div>

      <form method="post" id="zole-tournament-form">
        <?php wp_nonce_field('zolei_compact_tournament_save','zolei_compact_tournament_nonce'); ?>
        <input type="hidden" name="calendar_year" value="<?php echo esc_attr($year); ?>">
        <input type="hidden" name="zolei_tournament_source_url" value="<?php echo esc_attr(zolei_compact_remote_source_url()); ?>">
        <div class="zole-month-tabs-admin" role="tablist">
          <?php foreach($defs as $i=>$def): $m=$i+1; ?><button type="button" class="button zole-month-tab-admin <?php echo $m===$current_month?'button-primary':''; ?>" data-month="<?php echo esc_attr($m); ?>"><span><?php echo esc_html($def['lv']); ?></span><b><?php echo esc_html(count($grouped[$m])); ?></b></button><?php endforeach; ?>
        </div>

        <?php foreach($defs as $i=>$def): $m=$i+1; ?>
        <section class="zole-month-panel-admin" data-month-panel="<?php echo esc_attr($m); ?>" <?php echo $m===$current_month?'':'hidden'; ?>>
          <div class="zole-month-panel-head"><div><h2><?php echo esc_html($def['lv'].' '.$year); ?></h2><p><?php esc_html_e('Velc aiz ⋮⋮, lai mainītu secību. Tekstu var rediģēt uzreiz bez atsevišķa ieraksta atvēršanas.','zolei-react'); ?></p></div><button type="button" class="button button-primary zole-add-event" data-month="<?php echo esc_attr($m); ?>">+ <?php esc_html_e('Pievienot turnīru','zolei-react'); ?></button></div>
          <div class="zole-compact-list" data-month="<?php echo esc_attr($m); ?>">
            <?php foreach($grouped[$m] as $row):
              $id=esc_attr($row['id']); $links=$row['links']; $l1=$links[0]??array(); $l2=$links[1]??array(); ?>
            <div class="zole-event-admin-row" data-event-id="<?php echo $id; ?>">
              <button type="button" class="zole-drag" title="<?php esc_attr_e('Velc, lai pārvietotu','zolei-react'); ?>">⋮⋮</button>
              <div class="zole-date-col"><input type="date" name="events[<?php echo $m; ?>][<?php echo $id; ?>][date]" value="<?php echo esc_attr($row['date']); ?>"><small><?php echo esc_html($row['source']); ?></small></div>
              <div class="zole-text-col"><textarea rows="3" name="events[<?php echo $m; ?>][<?php echo $id; ?>][text]" placeholder="03.10.2026. Turnīra nosaukums, vieta, laiks, formāts..."><?php echo esc_textarea($row['text']); ?></textarea>
                <div class="zole-link-line"><input type="url" name="events[<?php echo $m; ?>][<?php echo $id; ?>][link1_url]" value="<?php echo esc_attr($l1['url']??''); ?>" placeholder="https://..."><input type="text" name="events[<?php echo $m; ?>][<?php echo $id; ?>][link1_label]" value="<?php echo esc_attr($l1['label']??''); ?>" placeholder="Nolikums"></div>
                <div class="zole-link-line zole-link-second"><input type="url" name="events[<?php echo $m; ?>][<?php echo $id; ?>][link2_url]" value="<?php echo esc_attr($l2['url']??''); ?>" placeholder="https://..."><input type="text" name="events[<?php echo $m; ?>][<?php echo $id; ?>][link2_label]" value="<?php echo esc_attr($l2['label']??''); ?>" placeholder="Afiša"></div>
              </div>
              <div class="zole-row-actions"><button type="button" class="button zole-duplicate-event"><?php esc_html_e('Dublēt','zolei-react'); ?></button><button type="button" class="button-link-delete zole-delete-event"><?php esc_html_e('Dzēst','zolei-react'); ?></button></div>
              <input type="hidden" class="zole-source-field" name="events[<?php echo $m; ?>][<?php echo $id; ?>][source]" value="<?php echo esc_attr($row['source']); ?>"><input type="hidden" name="events[<?php echo $m; ?>][<?php echo $id; ?>][id]" value="<?php echo $id; ?>">
            </div>
            <?php endforeach; ?>
          </div>
          <details class="zole-bulk-add"><summary><?php esc_html_e('Ātrā vairāku turnīru pievienošana','zolei-react'); ?></summary><p><?php esc_html_e('Ielīmē vienu turnīru katrā rindā. Datums DD.MM.GGGG tiks atpazīts automātiski.','zolei-react'); ?></p><textarea rows="6" data-bulk-month="<?php echo esc_attr($m); ?>" placeholder="03.10.2026. ...&#10;10.10.2026. ..."></textarea><button type="button" class="button zole-bulk-add-button" data-month="<?php echo esc_attr($m); ?>"><?php esc_html_e('Pievienot rindas','zolei-react'); ?></button></details>
        </section>
        <?php endforeach; ?>
        <div class="zole-save-bar"><button type="submit" class="button button-primary button-hero" name="zolei_compact_tournament_save" value="1"><?php esc_html_e('Saglabāt visu gadu','zolei-react'); ?></button><span><?php esc_html_e('Visi 12 mēneši tiek saglabāti vienā reizē.','zolei-react'); ?></span></div>
      </form>
    </div>
    <style>
      .zole-compact-admin{max-width:1500px}.zole-toolbar-card{display:flex;gap:20px;align-items:flex-end;justify-content:space-between;background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:16px;margin:16px 0}.zole-year-form select{min-width:110px}.zole-sync-form{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap}.zole-sync-form label{display:grid;gap:5px}.zole-sync-form input[type=url]{min-width:260px}.zole-month-tabs-admin{display:flex;gap:6px;flex-wrap:wrap;position:sticky;top:32px;background:#f0f0f1;padding:10px 0;z-index:10}.zole-month-tab-admin{display:flex!important;gap:7px;align-items:center}.zole-month-tab-admin b{background:rgba(0,0,0,.08);border-radius:999px;padding:1px 7px}.zole-month-panel-admin{background:#fff;border:1px solid #dcdcde;border-radius:16px;margin:10px 0 90px;overflow:hidden}.zole-month-panel-head{display:flex;justify-content:space-between;gap:15px;align-items:center;padding:18px 20px;background:#f8faf9;border-bottom:1px solid #e4e7e5}.zole-month-panel-head h2{margin:0}.zole-month-panel-head p{margin:4px 0 0;color:#646970}.zole-compact-list{padding:10px}.zole-event-admin-row{display:grid;grid-template-columns:38px 150px minmax(380px,1fr) 120px;gap:10px;align-items:start;padding:10px;border:1px solid #e2e5e3;border-radius:12px;background:#fff;margin:8px 0}.zole-event-admin-row.ui-sortable-helper{box-shadow:0 12px 28px rgba(0,0,0,.18)}.zole-drag{border:0;background:#f0f2f1;border-radius:8px;cursor:grab;font-size:20px;line-height:40px;height:40px}.zole-date-col input{width:100%}.zole-date-col small{display:block;color:#8c8f94;margin-top:5px}.zole-text-col textarea{width:100%;min-height:70px;resize:vertical}.zole-link-line{display:grid;grid-template-columns:2fr 1fr;gap:6px;margin-top:6px}.zole-link-line input{width:100%}.zole-link-second{opacity:.75}.zole-row-actions{display:flex;gap:8px;align-items:center;justify-content:flex-end;flex-wrap:wrap}.zole-bulk-add{margin:14px 20px 20px;border:1px dashed #a7aaad;border-radius:10px;padding:12px}.zole-bulk-add textarea{width:100%;margin:8px 0}.zole-save-bar{position:fixed;bottom:0;left:160px;right:0;z-index:100;background:rgba(255,255,255,.96);border-top:1px solid #ccd0d4;padding:10px 22px;display:flex;align-items:center;gap:16px;box-shadow:0 -8px 22px rgba(0,0,0,.06)}
      @media(max-width:960px){.zole-toolbar-card{display:block}.zole-sync-form{margin-top:12px}.zole-event-admin-row{grid-template-columns:34px 1fr}.zole-date-col{grid-column:2}.zole-text-col{grid-column:2}.zole-row-actions{grid-column:2;justify-content:flex-start}.zole-save-bar{left:36px}.zole-link-line{grid-template-columns:1fr}}
    </style>
    <script>
    jQuery(function($){
      var year=<?php echo (int)$year; ?>;
      function uid(){return 'evt_m'+Date.now().toString(36)+Math.random().toString(36).slice(2,7);}
      function esc(s){return $('<div>').text(s||'').html();}
      function rowHtml(month,text,date){var id=uid();return '<div class="zole-event-admin-row" data-event-id="'+id+'"><button type="button" class="zole-drag">⋮⋮</button><div class="zole-date-col"><input type="date" name="events['+month+']['+id+'][date]" value="'+esc(date)+'"><small>manual</small></div><div class="zole-text-col"><textarea rows="3" name="events['+month+']['+id+'][text]">'+esc(text)+'</textarea><div class="zole-link-line"><input type="url" name="events['+month+']['+id+'][link1_url]" placeholder="https://..."><input type="text" name="events['+month+']['+id+'][link1_label]" placeholder="Nolikums"></div><div class="zole-link-line zole-link-second"><input type="url" name="events['+month+']['+id+'][link2_url]" placeholder="https://..."><input type="text" name="events['+month+']['+id+'][link2_label]" placeholder="Afiša"></div></div><div class="zole-row-actions"><button type="button" class="button zole-duplicate-event">Dublēt</button><button type="button" class="button-link-delete zole-delete-event">Dzēst</button></div><input type="hidden" class="zole-source-field" name="events['+month+']['+id+'][source]" value="manual"><input type="hidden" name="events['+month+']['+id+'][id]" value="'+id+'"></div>';}
      $('.zole-compact-list').sortable({handle:'.zole-drag',placeholder:'zole-sort-placeholder',update:function(e,ui){var $r=ui.item;$r.find('.zole-source-field').val('manual');$r.find('.zole-date-col small').text('manual');}});
      $('.zole-month-tab-admin').on('click',function(){var m=$(this).data('month');$('.zole-month-tab-admin').removeClass('button-primary');$(this).addClass('button-primary');$('.zole-month-panel-admin').attr('hidden',true);$('.zole-month-panel-admin[data-month-panel="'+m+'"]').removeAttr('hidden');});
      $(document).on('click','.zole-add-event',function(){var m=$(this).data('month');$('.zole-compact-list[data-month="'+m+'"]').append(rowHtml(m,'',''));});
      $(document).on('click','.zole-delete-event',function(){if(confirm('Dzēst šo turnīru?')) $(this).closest('.zole-event-admin-row').remove();});
      $(document).on('click','.zole-duplicate-event',function(){var $row=$(this).closest('.zole-event-admin-row'),m=$row.closest('.zole-compact-list').data('month'),text=$row.find('textarea').first().val(),date=$row.find('input[type=date]').val();$row.after(rowHtml(m,text,date));});
      $(document).on('input change','.zole-event-admin-row input,.zole-event-admin-row textarea',function(){var $r=$(this).closest('.zole-event-admin-row');$r.find('.zole-source-field').val('manual');$r.find('.zole-date-col small').text('manual');});
      $('.zole-bulk-add-button').on('click',function(){var m=parseInt($(this).data('month'),10),$ta=$('[data-bulk-month="'+m+'"]'),lines=($ta.val()||'').split(/\r?\n/).map(function(x){return x.trim();}).filter(Boolean),$list=$('.zole-compact-list[data-month="'+m+'"]');lines.forEach(function(text){var date='',mm=text.match(/\b([0-3]?\d)[.\/-]([01]?\d)[.\/-](20\d{2})\b/);if(mm){date=mm[3]+'-'+String(parseInt(mm[2],10)).padStart(2,'0')+'-'+String(parseInt(mm[1],10)).padStart(2,'0');}else{mm=text.match(/\b([0-3]?\d)[.\/-]([01]?\d)[.\/-](?!\d)/);if(mm){date=year+'-'+String(parseInt(mm[2],10)).padStart(2,'0')+'-'+String(parseInt(mm[1],10)).padStart(2,'0');}}$list.append(rowHtml(m,text,date));});$ta.val('');});
    });
    </script>
    <?php
}
