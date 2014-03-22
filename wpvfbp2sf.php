<?php
// By Matt Withoos, 22 March 2014
// mattwithoos@gmail.com
// mattwithoos.com
//
// For WordPress' plugin, "Visual Form Builder Pro"
// Sends form data from VFBP to SalesForce's Web2Lead
// 
//
//
// INSTALLATION / PREPARATION
// You'll need:
// 1. SalesForce Web2Lead set up with the Unique ID. Set this up in the CRM control panel.
// 2. The variables Web2Lead is expecting (that is, the fields in your SalesForce CRM)
// 3. The form ID of your VFBP form(s)
// 4. The form values of your VFBP form(s) - something like "vfb-12" - can be found by inspecting source on your form
//
// 1. Fill in the appropriate fields in the script below (form ID, values of VFBP and SF)
// 2. Put entire script in the functions.php file in your Wordpress template (remove opening/closing PHP tags) - AT THE BOTTOM
// 
// For example, TYPES
// Contact Form 			- form_id = 3
// Application Form 		- form_id = 4
//
//



add_action( 'vfb_confirmation', 'vfb_action_confirmation', 10, 2 );

function vfb_action_confirmation( $form_id, $entry_id ){

	// IMPORTANT:--------------------------
	// Please enter the following variables
	//
	// 1. Your Web to Lead unique ID:
	$oidm = "ENTER_THE_ID_HERE";
	//
	// 2. Your RETURN URL - that is, after the form is submitted and arrives at SalesForce, where do you want it to go?
	$retURLm = "http://ENTER_RETURN_URL_HERE";
	//
	// 3. Enter the form id. If it's incorrect, it won't run. Get the form id from Visual Form Builder Pro settings
	$form_id_usergen = "FORM_ID_HERE"; //usually it will be 1 or 2, unless you've made lots of forms.
	//
	// (OPTIONAL) 4. Does your form have the Address field? It's an array so it'll need to be broken up. Enter the vfb value here, or comment out
	$address = $_POST['PLACE_VFB_HERE']; // ie $address = $_POST['vfb-5'];
	//
	// (OPTIONAL) 5. Does your form have a tickbox? If so, adjust the below line (per tickbox) or comment out.
	if(isset($_POST['PLACE_VFB_HERE'])) { $chkbox1 = "1"; /* checked */ } else{ $chkbox1 = "0"; /* unchecked */ }


	$url = "https://www.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8";  // SalesForce API URL
	$leadsource = "Web"; // to make SalesForce happy.
	if(!isset($_SESSION['RUNS'])) {$_SESSION['RUNS'] = 1; } // Prevents bug caused by WP Visual Form Builder Pro where it runs script twice
	$repand = array("&"); // cleans data being transmitted
	$_POST = str_replace($repand, "and", $_POST);

	if($_SESSION['RUNS'] == 1) { // hacky check to see if the form has been run twice. Had a bug with a client whose Wordpress site ran this twice.

		if($form_id == $form_id_usergen) { // Checks that the VFB form should be sent to SalesForce or not
			
		$myvars = array(
			// DO NOT TOUCH THESE ONES:
			'oid' => $oidm, 								//oid
			'retURL' => $retURLm,							//return url
			'form_id' => $_POST['form_id'],					//form id
			'lead_source' => $leadsource,					//lead source for SalesForce's purposes. Option list. May be optional?



			//----- IMPORTANT -----
			// CONVERSION SECTION 
			// This is where your VFB form data is translated for SalesForce
			// This is crucial to enter your own values
			// Don't forget commas EXCEPT for last line
			// Here is a sample:
			'sample_SF_variable' => $custom_preset_variable,
			// or
			'sample_SF_variable' => $_POST['vfb-1'], // VFB variable on the right
			// REMOVE THESE ABOVE ONES!


			// DEFINITELY TOUCH THESE ONES:
			// THESE ARE SAMPLE ONES!
			// THE UNIQUE ID ON THE LEFT IS EITHER A SALESFORCE-NATIVE VARIABLE OR A SALESFORCE CUSTOM VARIABLE YOU MADE (ie 00n900x0948).
			// REPLACE VARIALBE ON LEFT WITH DATA FROM SALESFORCE, ie Company, or 0094857jfx834
			// REPLACE VARIABLE ON RIGHT WITH VFB VALUE, ie vfb-2
			'company' => $_POST['vfb-1'],					// company name - JUST A SAMPLE!
			'URL' => $_POST['vfb-2'],						// website - JUST A SAMPLE!
			'phone' => $_POST['vfb-3'],						// phone - JUST A SAMPLE!
			'first_name' => $_POST['vfb-4'],				// first name - JUST A SAMPLE!
			'last_name' => $_POST['vfb-5'],					// last name - JUST A SAMPLE!
			'title' => $_POST['vfb-6'],						// title - JUST A SAMPLE!
			'email' => $_POST['vfb-7'],						// email - JUST A SAMPLE!
			'00N9000000ABCDE' => $_POST['vfb-14'],			// SAMPLE: custom SF variable
			'00N9000000ABCDE' => $_POST['vfb-15'],			// SAMPLE: blah blah
			'00N9000000ABCDE' => $_POST['vfb-16'],			// SAMPLE: Preferred contact method
			// DID I STRESS ENOUGH THAT THE ABOVE IS A SAMPLE? Ensure it is all replaced.
			// It will not work unless you change it to your VFB and SF variables, which are almost certainly different to mine.

			// DOES YOUR FORM HAVE A CHECKBOX? Did you enter it on step 5?
			// Comment out if not. Duplicate step 5 and below variable if you have more than 1.
			'00N9000000ABCDE' => $chkbox1,			// Sample Checkbox


			// Does your form ask for an address?
			// And, did you enter the VFB address array above, in step 4?
			// Then uncomment the below and adjust if necessary.
			/*
			'street' => $address['address'],				//street
			'city' => $address['city'],						//city
			'state' => $address['state'],					//state
			'zip' => $address['zip'],						//zip
			'country' => $address['country']				//country
			*/

			// --------- FINAL STEP ----------
			// Wait...
			// WAIT!!!
			// Now that you're done with the variables...
			// Does the LAST variable have a comma on the end?
			// Get rid of it!!
			// Are the other variables missing a comma? Give 'em a comma!

	    );
		}

		// Prepares the data into an API readable format
		$query_string = "";
		if ($myvars) {
			$kv = array();
			foreach ($myvars as $key => $value) {
				$kv[] = stripslashes($key)."=".stripslashes($value);
			}

			$query_string = join("&", $kv);

		}
		

		if($form_id == $form_id_usergen) {	// Checks that the VFB form should be sent to SalesForce or not
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, count($kv));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
		
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, FALSE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

		$result = curl_exec($ch); // If all is okay, sends to SalesForce
		curl_close($ch);
		$_SESSION['RUNS']++;
		}
	} else {
		unset($_SESSION['RUNS']);
}




}

?>