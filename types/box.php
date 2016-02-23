<?php
/**
 * exaltedwiki plugin, dot type
 *
 * @author  Brend Wanders <b.wanders@13w.nl>
 */

class plugin_strata_type_box extends plugin_strata_type {
    function __construct() {
        $this->helper =& plugin_load('helper', 'exaltedwiki');
    }

    function normalize($value, $hint) {
        return (int)$value;
    }

    function render($mode, &$R, &$T, $value, $hint) {
        if($mode == 'xhtml') {
            $value = (int)$value;
            $track = 1;
            $grouping = 0;

            // get the settings (if given)
            preg_match_all('/(t|g)([0-9]+)/S',$hint,$matches,PREG_SET_ORDER);
            foreach($matches as $m) {
                switch($m[1]) {
                    case 't': $track = (int)$m[2]; break;
                    case 'g': $grouping = (int)$m[2]; break;
                }
            }

            $R->doc .= $this->helper->dotTrack($value, $track, $grouping, '&#9633;', '&#9632;');
            return true;
        }

        return false;
    }

    function getInfo() {
        return array(
            'desc'=>'Shows numbers as a string of boxes. The type hint is used to add a track size (as \'tN\') and group size (as \'gN\').',
            'tags'=>array('numeric'),
            'hint'=>'\'tN\', \'gN\', or both'
        );
    }
}
