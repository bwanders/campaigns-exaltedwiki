<?php
/**
 * exaltedwiki plugin, icdate type
 *
 * @author  Brend Wanders <brend@13w.nl>
 */

class plugin_strata_type_icdate extends plugin_strata_type {
    function __construct() {
        $this->helper =& plugin_load('helper', 'exaltedwiki');
    }

    function normalize($value, $hint) {
        // try to convert date to day number
        $result = $this->helper->parse_date($value);
        if($result !== false) {
            return $result;
        }
        return $value;
    }

    function render($mode, &$R, &$T, $value, $hint) {
        if($mode == 'xhtml') {
            if(is_numeric($value)) {
                $R->doc .= $this->helper->render_date($value);
            } else {
                $R->doc .= $R->_xmlEntities($value);
            }
            return true;
        }

        return false;
    }

    function getInfo() {
        return array(
            'desc'=>'Stores and displays IC dates in the DD MMMM SSSS YYYY format.',
            'tags'=>array('numeric'),
            'hint'=>''
        );
    }
}
