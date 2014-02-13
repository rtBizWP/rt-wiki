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
 * Description of RtWikiAttributeTaxonomyModel
 *
 * @author udit
 */
if (!class_exists('RtWikiSubscribeModel')) {

    class RtWikiSubscribeModel extends RTDBModel {

        public function __construct() {
            parent::__construct('rtwiki_subscribe');
        }

        function is_subscribe($postid, $userid) {
            $subscribers = $this->get_subscriber($postid,$userid);
            foreach ($subscribers as $subscriber) {
                if ($userid == $subscriber->attribute_userid) {
                    return true;
                }
            }
            return false;
        }
        
        function is_subpage_subscribe($postid,$userid) {
            $subscribers = $this->get_subscriber($postid,$userid);
            foreach ($subscribers as $subscriber) {
                if ($subscriber->attribute_sub_subscribe) {
                    return true;
                }
            }
            return false;
        }

        function get_all_subscribers($postid) {
            $args = array();
            $return = array();
            if (!empty($postid)) {
                $args['attribute_postid'] = array(
                    'compare' => '=',
                    'value' => explode(',', $postid)
                );
                $return = parent::get($args);
            }
            return $return;
        }
        
        function get_subscriber($postid,$userid) {
             $args = array();
            $return = array();
            if (!empty($postid)) {
                $args['attribute_postid'] = array(
                    'compare' => '=',
                    'value' => explode(',', $postid)
                );
                $args['attribute_userid'] = array(
                    'compare' => '=',
                    'value' => explode(',', $userid)
                );
                $return = parent::get($args);
            }
            return $return;
        }

        function get_subscribers_by_groups($postid, $groups) {
            $args = array();
            $return = array();
            if (!empty($postid) && !empty($groups)) {
                $return = parent::get($args);
            }
            return $return;
        }

        function add_subscriber($data) {
            return parent::insert($data);
        }

        function update_subscriber($data, $where) {
            return parent::update($data, $where);
        }

        function delete_subscriber($where) {
            return parent::delete($where);
        }

    }

}