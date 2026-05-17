<?php
if (!defined('ABSPATH')) { exit; }

function zolei_pdf_compression_quality_options() {
    return array(
        'screen' => __('Smallest size / lower quality','zolei-react'),
        'ebook' => __('Recommended good quality','zolei-react'),
        'printer' => __('Print quality / larger file','zolei-react'),
        'prepress' => __('Prepress quality / largest file','zolei-react'),
    );
}

function zolei_pdf_compression_enabled() {
    return (bool) get_option('zolei_pdf_compression_enabled', 1);
}

function zolei_pdf_compression_quality() {
    $quality = sanitize_key(get_option('zolei_pdf_compression_quality', 'ebook'));
    return array_key_exists($quality, zolei_pdf_compression_quality_options()) ? $quality : 'ebook';
}

function zolei_pdf_compression_binary() {
    $saved = trim((string) get_option('zolei_pdf_compression_binary', ''));
    if ($saved !== '') { return $saved; }
    return 'gs';
}

function zolei_pdf_can_run_commands() {
    if (!function_exists('exec')) { return false; }
    $disabled = ini_get('disable_functions');
    if (!$disabled) { return true; }
    $items = array_map('trim', explode(',', strtolower($disabled)));
    return !in_array('exec', $items, true);
}

function zolei_pdf_compress_file($path, $quality = null) {
    $result = array(
        'ok' => false,
        'attempted' => false,
        'message' => '',
        'original_size' => 0,
        'compressed_size' => 0,
        'saved_bytes' => 0,
    );

    if (!zolei_pdf_compression_enabled()) {
        $result['message'] = __('PDF compression is disabled.','zolei-react');
        return $result;
    }
    if (!is_string($path) || $path === '' || !is_file($path) || !is_readable($path) || !is_writable($path)) {
        $result['message'] = __('PDF file is not readable/writable for compression.','zolei-react');
        return $result;
    }
    if (!preg_match('/\.pdf$/i', $path)) {
        $result['message'] = __('Only PDF files can be compressed.','zolei-react');
        return $result;
    }
    if (!zolei_pdf_can_run_commands()) {
        $result['message'] = __('Server command execution is disabled, so Ghostscript compression cannot run.','zolei-react');
        return $result;
    }

    $quality = $quality ? sanitize_key($quality) : zolei_pdf_compression_quality();
    if (!array_key_exists($quality, zolei_pdf_compression_quality_options())) { $quality = 'ebook'; }

    clearstatcache(true, $path);
    $original_size = filesize($path);
    $result['original_size'] = $original_size ? intval($original_size) : 0;
    if ($original_size < 1024) {
        $result['message'] = __('PDF is too small to compress.','zolei-react');
        return $result;
    }

    $tmp = trailingslashit(dirname($path)) . '.' . basename($path, '.pdf') . '-compressed-' . wp_generate_password(8, false, false) . '.pdf';
    $binary = zolei_pdf_compression_binary();
    $cmd = escapeshellcmd($binary) . ' -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/' . escapeshellarg($quality);
    $cmd .= ' -dNOPAUSE -dQUIET -dBATCH -dDetectDuplicateImages=true -dCompressFonts=true -dSubsetFonts=true';
    $cmd .= ' -sOutputFile=' . escapeshellarg($tmp) . ' ' . escapeshellarg($path) . ' 2>&1';

    $output = array();
    $code = 0;
    $result['attempted'] = true;
    @exec($cmd, $output, $code);

    if ($code !== 0 || !file_exists($tmp) || filesize($tmp) < 1024) {
        if (file_exists($tmp)) { @unlink($tmp); }
        $result['message'] = __('Ghostscript could not compress this PDF. Original file was kept.','zolei-react');
        return $result;
    }

    clearstatcache(true, $tmp);
    $compressed_size = filesize($tmp);
    $result['compressed_size'] = $compressed_size ? intval($compressed_size) : 0;

    if ($compressed_size > 0 && $compressed_size < $original_size) {
        if (@rename($tmp, $path)) {
            @chmod($path, 0644);
            $result['ok'] = true;
            $result['saved_bytes'] = intval($original_size - $compressed_size);
            $result['message'] = sprintf(__('PDF compressed: saved %s.','zolei-react'), size_format($result['saved_bytes']));
            return $result;
        }
        @unlink($tmp);
        $result['message'] = __('Compressed PDF could not replace the original. Original file was kept.','zolei-react');
        return $result;
    }

    @unlink($tmp);
    $result['compressed_size'] = intval($original_size);
    $result['message'] = __('Compression did not reduce file size. Original file was kept.','zolei-react');
    return $result;
}

function zolei_pdf_compression_admin_status() {
    if (!zolei_pdf_compression_enabled()) { return __('Disabled','zolei-react'); }
    if (!zolei_pdf_can_run_commands()) { return __('Enabled, but server command execution is disabled','zolei-react'); }
    $binary = zolei_pdf_compression_binary();
    $out = array();
    $code = 0;
    @exec(escapeshellcmd($binary) . ' --version 2>&1', $out, $code);
    if ($code === 0 && !empty($out[0])) {
        return sprintf(__('Enabled: Ghostscript %s, quality %s','zolei-react'), sanitize_text_field($out[0]), zolei_pdf_compression_quality());
    }
    return __('Enabled, but Ghostscript was not found. Install Ghostscript or set the binary path.','zolei-react');
}
