<?php
function contacts_diff_on_lead( $post_id, $newLead,$tax) {

	$diffHTML = '';
         echo '<pre>';
        print_r($newLead);
        echo '</pre>';
        
         
	if ( !isset( $newLead ) ) {
		$newLead= array();
	}
        
	$contacts = $newLead;
	$contacts = array_unique($contacts);
         
        
	// String of Old Terms
        /* Change this function name. Defined below */
	//$oldContactsString = post_term_to_string( $post_id, $tax );
       
	// String of New Terms
	$newContactsString = '';
	if(!empty($contacts)) {
                //echo 'inside';
		$contactsArr = array();
		foreach ( $contacts as $contact ) {
			$newC = get_term_by( $post_id, $contact, $tax );
			$contactsArr[] = $newC->name;
		}
		$newContactsString = implode(',', $contactsArr);
                    
	}
	$diff = rtcrm_text_diff_taxonomy($oldContactsString,$newContactsString);
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
//        echo '<pre>';
//        print_r($termsArr);
//        echo '</pre>';
        //var_dump($termsArr);
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