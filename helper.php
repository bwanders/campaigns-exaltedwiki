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
    public function dotTrack($value, $track=1, $grouping=0, $open='&#9675;', $closed='&#9679;') {
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

        return '<span class="dot-track">' . implode(' ', array_map('implode', $dots)) . '</span>';
    }

    const SEASONS = array(
        array('name'=>'Air', 'aliases'=>array('Air', 'air'), 'ordinal'=>0),
        array('name'=>'Water', 'aliases'=>array('Water', 'water'), 'ordinal'=>1),
        array('name'=>'Earth', 'aliases'=>array('Earth', 'earth'), 'ordinal'=>2),
        array('name'=>'Wood', 'aliases'=>array('Wood', 'wood'), 'ordinal'=>3),
        array('name'=>'Fire', 'aliases'=>array('Fire', 'fire'), 'ordinal'=>4),
    );
    const MONTH_QUALIFIER = array(
        array('name'=>'Ascending', 'aliases'=>array('Ascending', 'ascending', 'Asc', 'asc'), 'ordinal'=>0),
        array('name'=>'Resplendent', 'aliases'=>array('Resplendent', 'resplendent', 'Res', 'res'), 'ordinal'=>1),
        array('name'=>'Descending', 'aliases'=>array('Descending', 'descending', 'Desc', 'desc'), 'ordinal'=>2),
    );
    const CALIBRATION = array(
        array('name'=>'Calibration', 'aliases'=>array('Calibration', 'calibration'), 'ordinal'=>0)
    );

    const DAYS_PER_MONTH = 7 * 4;
    const MONTHS_PER_SEASON = 3;
    const DAYS_PER_SEASON = self::DAYS_PER_MONTH * self::MONTHS_PER_SEASON;
    const SEASONS_PER_YEAR = 5;
    const CALIBRATION_LENGTH= 5;
    const DAYS_PER_YEAR = self::DAYS_PER_SEASON * self::SEASONS_PER_YEAR + self::CALIBRATION_LENGTH;

    private function resolve_name($candidates, $word) {
        foreach($candidates as $candidate) {
            if(in_array($word, $candidate['aliases'])) {
                return $candidate;
            }
        }
        return null;
    }

    /**
     * Parses an IC date into a day number.
     *
     * @param value the date string to parser
     */
    public function parse_date($value) {
        // Scan default DD MMMM SSSS [RY] YYYY format, sprinkled with commans where appropriate
        static $pattern = '/^\\s*([0-9]+)[\\s,]+([a-zA-Z ]+?)[\\s,]+(?:(RY)\\s*)?(-?[0-9]+)\\s*$/';
        if(preg_match($pattern, $value, $m)) {
            list($all, $day, $month, $epoch, $year) = $m;
            if($epoch == '') $epoch = 'RY';
            $parts = preg_split('/\\s+/', $month);

            $month_offset = 0;
            $day = (int)$day;
            $year = (int)$year;

            if(count($parts) == 2) {
                // we have a 'Month Season' month
                $month = $this->resolve_name(self::MONTH_QUALIFIER, $parts[0]);
                $season = $this->resolve_name(self::SEASONS, $parts[1]);
                if($month == null || $season == null) { return false; }
                if($day < 1 || $day > 28) { return false; }
                // all date components are valid, convert to day number
                $month_offset = $month['ordinal'] * self::DAYS_PER_MONTH;
                $month_offset += $season['ordinal'] * self::DAYS_PER_SEASON;
            } else {
                // we have a 'Calibration' month
                $month = $this->resolve_name(self::CALIBRATION, $month);
                if($month = null) { return false; }
                if($day < 1 || $day > 5) { return false; }
                // all date components are valid, convert to day number
                $month_offset += self::DAYS_PER_SEASON * self::SEASONS_PER_YEAR; // Calibration
            }

            // RY 1 is the first year
            if($year > 0) {
               $year = $year - 1;
            } elseif($year == 0) {
               return false;
            }
            // day 0 is the first day
            $day -= 1;

            $day_number = 0;
            $day_number += $day;
            $day_number += $month_offset;
            $day_number += $year * self::DAYS_PER_YEAR;
            return $day_number;
        } else {
            return false;
        }
    }

    /**
     * Render a day number into a string.
     *
     * @param value the day numbe
     */
    public function render_date($value) {
        $year = 0;
        $month = 0;
        $day = 0;
        $left = $value;

        $year = floor($left / self::DAYS_PER_YEAR);
        $left = $left % self::DAYS_PER_YEAR;
        if($left > (self::DAYS_PER_SEASON * self::SEASONS_PER_YEAR)) {
            $day = $left - self::DAYS_PER_SEASON * self::SEASONS_PER_YEAR;
            $month = self::CALIBRATION[0]['name'];
        } else {
            $season_index = floor($left / self::DAYS_PER_SEASON);
            $left = $left % self::DAYS_PER_SEASON;
            $month_index = floor($left / self::DAYS_PER_MONTH);
            $day = $left % self::DAYS_PER_MONTH;
            $month = self::MONTH_QUALIFIER[$month_index]['name'] . ' ' . self::SEASONS[$season_index]['name'];
        }

        // RY 1 is the first year
        if($year >= 0) $year += 1;
        // day 0 is the first day
        $day += 1;
        return "$day $month $year";
    }
}
