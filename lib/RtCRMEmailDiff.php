<?php

/**
 * Don't load this file directly!
 */
if (!defined('ABSPATH'))
    exit;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RtCRMEmailDiff
 *
 * @author udit
 */
if (!class_exists('RtCRMEmailDiff')) {

    if (!class_exists('WP_Text_Diff_Renderer_Table')) {
        require( ABSPATH . WPINC . '/wp-diff.php' );
    }

    class RtCRMEmailDiff extends WP_Text_Diff_Renderer_Table {

        var $_leading_context_lines = 2;
        var $_trailing_context_lines = 2;

        function addedLine($line) {
            return "<td style='padding: .5em;border: 0;width:25px;'>+</td><td style='padding: .5em;border: 0;background-color: #dfd;'>{$line}</td>";
        }

        /**
         * @ignore
         *
         * @param string $line HTML-escape the value.
         * @return string
         */
        function deletedLine($line) {
            return "<td style='padding: .5em;border: 0;width:25px;' >-</td><td style='padding: .5em;border: 0;background-color: #fdd;' >{$line}</td>";
        }

        /**
         * @ignore
         *
         * @param string $line HTML-escape the value.
         * @return string
         */
        function contextLine($line) {
            return "<td style='padding: .5em;border: 0;' > </td><td style='padding: .5em;border: 0;'>{$line}</td>";
        }

    }

}

// wp1_text_diff
if (!function_exists('rtcrm_text_diff')) {

    function rtcrm_text_diff($left_title, $right_title, $left_content, $right_content, $args = null) {
        $defaults = array('title' => 'Updates', 'title_left' => $left_title, 'title_right' => $right_title);
        $args = wp_parse_args($args, $defaults);

        $left_string = normalize_whitespace($left_content);
        $right_string = normalize_whitespace($right_content);
        $left_lines = explode("\n", $left_string);
        $right_lines = explode("\n", $right_string);

        $text_diff = new Text_Diff($left_lines, $right_lines);
        $renderer = new RtCRMEmailDiff();
        $diff = $renderer->render($text_diff);

        if (!$diff)
            return '';

        $r = "<table class='diff' style='width: 100%;background: white;margin-bottom: 1.25em;border: solid 1px #dddddd;border-radius: 3px;margin: 0 0 18px;'>\n";
        $r .= "<col class='ltype' /><col class='content' /><col/><col class='ltype' /><col class='content' />";

        if ($args['title'] || $args['title_left'] || $args['title_right'])
            $r .= "<thead>";
        if ($args['title'])
            $r .= "<tr class='diff-title'><th colspan='5'>$args[title]</th></tr>\n";
        if ($args['title_left'] || $args['title_right']) {
            $r .= "<tr class='diff-sub-title'>\n";
            $r .= "\t<td></td><th>$args[title_left]</th>\n";
            $r .= "\t<td></td><td></td><th>$args[title_right]</th>\n";
            $r .= "</tr>\n";
        }
        if ($args['title'] || $args['title_left'] || $args['title_right'])
            $r .= "</thead>\n";
        $r .= "<tbody>\n$diff\n</tbody>\n";
        $r .= "</table>";
        return $r;
    }

    
    function rtcrm_text_diff_taxonomy($left_string, $right_string, $args = null) {

        
        $defaults = array('title' => '', 'title_left' => '', 'title_right' => '');
        $args = wp_parse_args($args, $defaults);

        $left_string = normalize_whitespace($left_string);
        $right_string = normalize_whitespace($right_string);
        $left_lines = explode("\n", $left_string);
        $right_lines = explode("\n", $right_string);

        $text_diff = new Text_Diff($left_lines, $right_lines);
        $renderer = new RtCRMEmailDiff();
        $diff = $renderer->render($text_diff);

        if (!$diff)
            return '';

        $r = "<table class='diff' style='width: 100%;background: white;margin-bottom: 1.25em;border: solid 1px #dddddd;border-radius: 3px;margin: 0 0 18px;'>\n";
        $r .= "<col class='ltype' /><col class='content' /><col/><col class='ltype' /><col class='content' />";

        if ($args['title'] || $args['title_left'] || $args['title_right'])
            $r .= "<thead>";
        if ($args['title'])
            $r .= "<tr class='diff-title'><th colspan='5'>$args[title]</th></tr>\n";
        if ($args['title_left'] || $args['title_right']) {
            $r .= "<tr class='diff-sub-title'>\n";
            $r .= "\t<td></td><th>$args[title_left]</th>\n";
            $r .= "\t<td></td><td></td><th>$args[title_right]</th>\n";
            $r .= "</tr>\n";
        }
        if ($args['title'] || $args['title_left'] || $args['title_right'])
            $r .= "</thead>\n";
        $r .= "<tbody>\n$diff\n</tbody>\n";
        $r .= "</table>";
        return $r;
    }

    

}
