<?php
/**
 * version.php
 *
 * Single source of truth for the cache-busting asset version, appended as
 * ?v=... to shared CSS/JS/manifest links in the root landing page and every
 * brand's index.php. Bump this one value to invalidate cached assets
 * everywhere at once, instead of editing each index.php individually.
 */
$version = "2026.08.08a";
