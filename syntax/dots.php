<?php
/**
 * exaltedwiki, dots syntax component
 *
 * @author  Brend Wanders <brend@13w.nl>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class syntax_plugin_exaltedwiki_dots extends DokuWiki_Syntax_Plugin {
    public function __construct() {
        $this->helper =& plugin_load('helper', 'exaltedwiki');
    }

    public function getType() { return 'substition'; }
    public function getPType() { return 'normal'; }
    public function getSort() { return 225; }

   public function connectTo($mode) {
        $this->Lexer->addSpecialPattern(':[0-9]+(?:/[0-9]+(?:g[0-9]+)?)?:',$mode,'plugin_exaltedwiki_dots');
    }

   public function handle($match, $state, $pos, Doku_Handler &$handler){
        preg_match('@:([0-9]+)(?:/([0-9]+)(?:g([0-9]+))?)?:@', $match, $m);
        list(, $value, $track, $grouping) = $m;

        if(!isset($track)) $track = 5;
        if(!isset($grouping)) $grouping = 5;

        return array($value, $track, $grouping);
    }

   public function render($mode, Doku_Renderer &$renderer, $data) {
        list($value, $track, $grouping) = $data;

        if($mode != 'xhtml') {
            $renderer->cdata("$value/$track");
        } else {
            $renderer->doc .= $this->helper->dotTrack($value, $track, $grouping);
        }

        return true;
    }
}
