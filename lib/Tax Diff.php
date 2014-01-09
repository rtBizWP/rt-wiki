<?php
function contacts_diff_on_lead( $post_id, $newLead,$tax) {

	$diffHTML = '';
        
        
         
	if ( !isset( $newLead[tax] ) ) {
		$newLead[tax]= array();
	}
        
	$contacts = $newLead[tax];
	$contacts = array_unique($contacts);
       
        
	// String of Old Terms
        /* Change this function name. Defined below */
	$oldContactsString = get_terms_before_update( $post_id, $tax );
//        echo '<pre>';
//        print_r($oldContactsString);
//        echo '</pre>';
        
	// String of New Terms
	$newContactsString = '';
	if(!empty($contacts)) {
           
		$contactsArr = array();
		foreach ( $contacts as $contact ) {
			$newC = get_term_by( $post_id, $contact, $tax );
			$contactsArr[] = $newC->name;
		}
		$newContactsString = implode(',', $contactsArr);
            
	}
	$diff = rtcrm_text_diff_taxonomy($newContactsString,$oldContactsString);
	if ( $diff ) {
		$diffHTML .= '<tr><th style="padding: .5em;border: 0;">'. $tax .'</th><td>' . $diff . '</td><td></td></tr>';
	}
        //unset($newLead);
	return $diffHTML;
}

/*     * ********* Post Term To String **** */
/* Change this function name */
function post_term_to_string( $postid, $taxonomy, $termsep = ',' ) {
	$termsArr = get_the_terms( $postid, $taxonomy );
       
	$tmpStr = '';
	if ( $termsArr ) {
		$sep = '';
		foreach ( $termsArr as $tObj ) {
			$tmpStr .= $sep . $tObj->name;
			$sep = $termsep;
		}
	}
	return $tmpStr;
}
/*     * ********* Post Term To String **** */