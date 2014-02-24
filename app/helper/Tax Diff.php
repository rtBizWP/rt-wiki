<?php

/**
 * find diffrent of perticuler post
 *
 * @param type  $post_id
 * @param array $newLead : new term id of post
 * @param type  $oldLead : old term id post
 * @param type  $tax     : keyvalue of both term[$newLead & $oldLead]
 *
 * @return string
 */
function contacts_diff_on_lead( $post_id, $newLead, $oldLead, $tax )
{

	$diffHTML = '';

	if ( ! isset( $newLead ) ){
		$newLead = array();
	}

	$contacts = $newLead;
	$contacts = array_unique( $contacts );


	// String of Old Terms
	$oldContactsString = implode( ',', $oldLead );

	// String of New Terms
	$newContactsString = '';
	if ( ! empty( $contacts ) ){

		$contactsArr = array();
		foreach ( $contacts as $contact ) {
			$newC           = get_term_by( $post_id, $contact, $tax );
			$contactsArr[ ] = $newC->name;
		}
		$newContactsString = implode( ',', $contactsArr );
	}

	$diff = rtcrm_text_diff_taxonomy( $oldContactsString, $newContactsString );
	if ( $diff ){
		$diffHTML .= '<tr><th style="padding: .5em;border: 0;">' . $tax . '</th><td>' . $diff . '</td><td></td></tr>';
	}

	return $diffHTML;
}
