<?php
/*
    Copyright (c) 2013 Brown Paper Tickets

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
*/

class BptApi {

    /*  SALES LIST CALL
        http://www.brownpapertickets.com/apidocs/2.0/orderlist.html

        This will return a list of all sales made on an event.
        $dev_id, $event_id, and $account are required.

        The account you are accessing must be added to the Authorized
        Accounts list on your developer account. You need the producer's
        username and login to add them. 

        If you are trying to access an account that is listed in the
        Authorized account section and still getting access denied,
        try removing and readding the account.
        
        Access your Authorized Accounts here:
        https://www.brownpapertickets.com/developer/accounts.html
    */

    public function sales_list_call($dev_id, $account, $event_id = '', $date_id = '', $price_id = '') {

        /* 
        The URL that we will have cURL send a GET request to.
        (I have it broken into seperate lines simply for readability.) 
        */
        $sales_call = 'https://www.brownpapertickets.com/api2/orderlist?'.
                    'id='.$dev_id.
                    '&account='.$account.
                    '&event_id='.$event_id.
                    '&date_id='.$date_id.
                    '&price_id='.$price_id;
        /* 
        Initialize cURL and set all of it's options. 

        (I don't actually know if these are set properly... I should go
        over these at some point. Also split off the curl stuff into 
        it's own function. No need to set it up like this everytime.)

        http://php.net/manual/en/ref.curl.php
        */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sales_call);
        curl_setopt($ch, CURLOPT_SSLVERSION, 3); 
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        /*
        Call cURL will return the xml and we'll be saving it to the 
        $sales_list_xml variable.
        */
        $sales_list_xml = curl_exec($ch);
        curl_close($ch);

        /* 
        Call produce_XML_object_Tree function to parse the xml and
        return it as something that PHP can use.

        http://php.net/manual/en/class.simplexmlelement.php
        */
        $sales_xml = $this->produce_XML_object_tree($sales_list_xml);

        //Create an array that will hold each of the sales. 
        $sales = array();

        /*
        Loop through the XML document and parse all the info that we 
        need. First check to ensure that the API has given us a correct
        response. See below for the actual XML structure.
        */

        if ($sales_xml->result == 'fail') {

            /*
            Use $document_root->element->sub_element to access the 
            nested XML.
            */
            if ($sales_xml->resultcode == '100003') {
                return 'Access denied. Ensure that the account you are
                trying to access is added to your list of 
                <a href="https://www.brownpapertickets.com/developer/accounts.html" target="_blank">
                Authrozied Accounts</a>';
            } 

            if ($sales_xml->resultcode == '200004') {

                return 'No Event ID was provided.';

            } else {

                return 'Something went wrong. Please check that your 
                Developer ID is correct.';

            }

        } else {

            /*
            Begin the for each loop. Remember, use 
            $document_root->elemet to access that xml tag.
            This is essentially setting the item as the document_root
            in this loop so take that into account when travesing into
            the lower levels of the XML. 
            */
            foreach ($sales_xml->item as $sale) {

                /* 
                The array of sales will actually be a multidimensional
                array. An array within an array!
                (I'm using the same naming format as the returned XML)
                */

                $single_sale = array(
                    /* 
                    Remember only go one level down since sale is the 
                    document_root. Also keep in mind the different arrow
                    uses. => pushes into an array element, -> goes down
                    into the XML object.

                    It's not necessary to add all values to the array.
                    For example, if you know that none of the producer's
                    events have assigned seating, you don't need to 
                    add that info.
                    */
                    
                    'order_time'=>$sale->order_time,
                    'date_id'=>$sale->date_id,
                    'price_id'=>$sale->price_id,
                    'quantity'=>$sale->quantity,
                    'fname'=>$sale->fname,
                    'lname'=>$sale->lname,
                    'address'=>$sale->address,
                    'city'=>$sale->city,
                    'state'=>$sale->state,
                    'zip'=>$sale->zip,
                    'country'=>$sale->country,
                    'email'=>$sale->email,
                    'phone'=>$sale->phone,
                    'cc'=>$sale->cc,
                    'shipping_method'=>$sale->shipping_method,
                    'order_notes'=>$sale->order_notes,
                    'ticket_number'=>$sale->ticket_number,
                    'section'=>$sale->section,
                    'row'=>$sale->row,
                    'seat'=>$sale->seat
                );
                
                // put the single_sale into the sales array
                $sales[] = $single_sale;

                /* 
                You can sort if they array if you want to passing it
                to the sort_by_key function below. 

                $sales = sort_by_key($sales, 'the value you want to sort it by') 
                */
            }

            /* Finally return the array to whatever called this function! */
            return $sales;
        }

        /* 
        XML Structure of the Sales List. Each order is an <item> 
        within <document>.
        
        <document>
            <result>success or fail</result>
            <resultcode>000000</resultcode>
            <note>A note</note>
            <item>
                <order_time>2011-01-20 10:42:53</order_time>
                <date_id>123456</date_id>
                <price_id>123456</price_id>
                <quantity>1</quantity>
                <fname>Joe</fname>
                <lname>Schmoe</lname>
                <address>123 Street St.</address>
                <city>Boston</city>
                <state>MA</state>
                <zip>02109</zip>
                <country>United States</country>
                <email>some@email.com</email>
                <phone>555-555-555</phone>
                <cc>N/A</cc>
                <shipping_method>Physical</shipping_method>
                <order_notes/>
                <ticket_number>A11370017</ticket_number>
                <section>H</section>
                <row>C</row>
                <seat>38</seat>
            </item>
        </document>
        */

    }

    public function event_list_call( $dev_id, $client_id, $event_id = '', $dates = false, $prices = false) {

        if ( isset( $event_id ) ) {
            $event_call = 'https://www.brownpapertickets.com/api2/eventlist?id='.$dev_id.'&client='.$client_id.'&event_id='.$event_id;
        }

        else {

            $event_call = 'https://www.brownpapertickets.com/api2/eventlist?id='.$dev_id.'&client='.$client_id;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $event_call);
        curl_setopt($ch, CURLOPT_SSLVERSION, 3); 
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $event_list_xml = curl_exec($ch);
        curl_close($ch);

        $event_xml = $this->produce_XML_object_tree($event_list_xml);

        $events = array();

         if ($event_xml->result == 'fail') {

            if ($event_xml->resultcode == '100003') {
                return 'Access denied. Ensure that the account you are
                trying to access is added to your list of 
                <a href="https://www.brownpapertickets.com/developer/accounts.html" target="_blank">
                Authrozied Accounts</a>';
            } else {

                return 'Something went wrong. Please check that your 
                Developer ID is correct.';

            }

        }

        else {
            foreach ($event_xml->event as $event) {
                $event_id = $event->event_id;

                    $single_event = array(
                        'event_id'=>$event_id,
                        'event_title'=>$event->title,
                        'event_live'=>$event->live,
                        'event_address1'=>$event->e_address1,
                        'event_address2'=>$event->e_address2,
                        'event_city'=>$event->e_city,
                        'event_state'=>$event->e_state,
                        'event_zip'=>$event->e_zip,
                        'short_description'=>$event->description,
                        'full_description'=>$event->e_description
                    );
                    // if dates is true, call the date list call
                    if($dates == true) {
                        // Call date_list with $this so that you can cast it from a new instance of the class
                       $single_event['dates'] = array($this->date_list_call($dev_id, $event_id, '', $prices));
                    }

                    $events[] = $single_event;
            }
        }
        return $events;
    }

    public function date_list_call($dev_id, $event_id, $date_id = '', $prices = false) {

        $date_call = 'https://www.brownpapertickets.com/api2/datelist?id='.$dev_id.'&event_id='.$event_id.'&date_id='.$date_id;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $date_call);
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $date_list_xml = curl_exec($ch);
        curl_close($ch);

        $date_xml = $this->produce_XML_object_tree($date_list_xml);

        $dates = array();

        foreach ($date_xml->date as $date) {

            $date_id = $date->date_id;


                $single_date = array(
                    'date_id'=>$date_id,
                    'date_start'=>$date->datestart,
                    'date_end'=>$date->dateend,
                    'time_start'=>$date->timestart,
                    'date_live'=>$date->live,
                    'time_end'=>$date->timeend,
                    'date_available'=>$date->date_available
                    
                );
                // If prices is true, call the prices_list_call
                if($prices == true) {
                    // Call price_list with $this so that you can cast it from a new instance of the class
                    $single_date['prices'] = array($this->price_list_call( $dev_id, $event_id, $date_id));
                }

                $dates[] = $single_date;

        }

        if ( count($dates) > 1 ) {
            $dates = $this->sort_by_key( $dates, 'date_start' );
            return $dates;
        }

        else {
            return $dates;
        }

    }


    public function price_list_call($dev_id, $event_id, $date_id) {

        $price_call = 'https://www.brownpapertickets.com/api2/pricelist?id='.$dev_id.'&event_id='.$event_id.'&date_id='.$date_id;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $price_call);
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $price_list_xml = curl_exec($ch);
        curl_close($ch);

        $price_xml = $this->produce_XML_object_tree($price_list_xml);

        $prices = array();

        foreach ($price_xml->price as $price) {

            $single_price = array(
                'price_id'=>$price->price_id,
                'price_name'=>$price->name,
                'price_value'=>$price->value,
                'price_service_fee'=>$price->service_fee,
                'price_venue_fee'=>$price->venue_fee,
                'price_live'=>$price->live,
            );

            $prices[] = $single_price;
        }

        if ( count($prices) > 1 ) {
            $prices = $this->sort_by_key( $prices, 'price_name' );
            return $prices;
        }

        else {
            return $prices;
        }
    }

    protected function sort_by_key( $array, $key ) {

        //Loop through and get the values of our specified key
        foreach($array as $k=>$v) {
            $b[] = strtolower($v[$key]);
        }

        asort($b);

        foreach($b as $k=>$v) {
            $c[] = $array[$k];
        }

        return $c;

    }

    protected function produce_XML_object_tree($raw_XML) {
        libxml_use_internal_errors(true);
            try {
                $xmlTree = new SimpleXMLElement($raw_XML);
            }

            catch (Exception $e) {
            // Something went wrong.
                $error_message = '<strong>Error retrieving XML. Please check your Client ID and Developer ID.</strong><br /><br />';
                #foreach(libxml_get_errors() as $error_line) {
                #   $error_message .= "\n" . $error_line->message;
                #}
                trigger_error($error_message);
                return false;
            }

        return $xmlTree;
    }
}

?>