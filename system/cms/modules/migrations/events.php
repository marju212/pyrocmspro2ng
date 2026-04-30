<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Migrations module — events handlers.
 *
 * Lives in this module so we don't need a third top-level module just
 * for cache invalidation; the migrations module is conceptually the
 * "deployment hygiene" module and cache busting on entry save fits
 * that bucket. (If this grows, split it into its own module.)
 *
 * Wires the streams entry insert/update/delete events to wipe the
 * pyrocache directory's `.cache` files. PyroCMS otherwise caches
 * rendered partials forever (cache_default_expires=0), so a freshly-
 * saved ad / blog post / streams entry doesn't appear on the public
 * page until something physically clears the cache (container restart,
 * manual maintenance run, etc.). Saving anything in admin now
 * invalidates the cached partials immediately.
 *
 * Heavy-handed: deletes ALL pyrocache files, not just ones referencing
 * the changed stream. Cache will repopulate on the next visit. For a
 * small site this is fine. If you see noticeable load on first hit
 * after a save, switch to a more targeted invalidator that knows which
 * files reference which stream.
 */
class Events_Migrations
{
    public function __construct()
    {
        Events::register('streams_post_insert_entry', array($this, 'bust_cache'));
        Events::register('streams_post_update_entry', array($this, 'bust_cache'));
        Events::register('streams_post_delete_entry', array($this, 'bust_cache'));
    }

    /**
     * Wipe all pyrocache `.cache` files for the current site. We only
     * touch files at the cache_dir top level — sub-directories like
     * `cloud_cache/`, `simplepie/`, and `codeigniter/` belong to other
     * subsystems and shouldn't be invalidated by entry saves.
     */
    public function bust_cache()
    {
        $dir = config_item('cache_dir');
        if ( ! $dir || ! is_dir($dir))
        {
            return;
        }

        foreach (glob(rtrim($dir, '/').'/*.cache') ?: array() as $file)
        {
            @unlink($file);
        }
    }
}
