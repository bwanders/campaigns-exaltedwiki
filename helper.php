<?php
/**
 * exaltedwiki, Helper component
 *
 * @author  Brend Wanders <brend@13w.nl>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class helper_plugin_exaltedwiki extends DokuWiki_Plugin {
    /**
     * Generates a dot track and returns the string for rendering.
     *
     * @param value the number of dots
     * @param track optional length of the track
     * @param grouping optional group size
     */
    public function dotTrack($value, $track=1, $grouping=0) {
        static $open = '&#9675;';
        static $closed = '&#9679;';

        // create filled dots
        $dots =  array_fill(0, $value, $closed);

        // fill up to the required track length
        $open_count = max(0, $track - $value);
        for($i=0;$i<$open_count;$i++) {
            $dots[] = $open;
        }

        // chunk groups
        if($grouping > 0) {
            $dots = array_chunk($dots, $grouping);
        } else {
            $dots = array($dots);
        }

        return '<big>' . implode(' ', array_map('implode', $dots)) . '</big>';
    }
}
