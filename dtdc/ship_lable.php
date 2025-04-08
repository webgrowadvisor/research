<?php
    include '../config.php';
    
    // Shipping Label A4 = SHIP_LABEL_A4
    // Shipping Label A6 = SHIP_LABEL_A6
    // Shipping Label POD = SHIP_LABEL_POD
    // Shipping Label 4x6 = SHIP_LABEL_4X6
    // Routing Label A4 = ROUTE_LABEL_A4
    // Routing Label 4x4 = ROUTE_LABEL_4X4
    
    // Invoice Print INVOICE
    // Address Label A4 = ADDR_LABEL_A4
    // Address Label 4x2 = ADDR_LABEL_4X2
    
    $size = $_POST['ship_label'];
    $oderid = $_POST['oderid'];

    $url = 'https://dtdcapi.shipsy.io/api/customer/integration/consignment/shippinglabel/stream?reference_number='.$oderid.'&label_code='.$size.'&label_format=pdf';
    

    // Initialize cURL session
    $curl = curl_init();

    // Set cURL options
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_VERBOSE => true, // for debugging
        CURLOPT_HTTPHEADER => array(
            'api-key: fadskjfjk45545454jifdsjiie'
        ),
    ));

    // Execute the cURL request
    $response = curl_exec($curl);

    // Check for errors
    if (curl_errno($curl)) {
        echo 'cURL error: ' . curl_error($curl);
    } else {
        // Print the response
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="shipping_label.pdf"');
        header('Content-Length: ' . strlen($response));
    
        // Output the PDF file
        echo $response;
    }

    // Close the cURL session
    curl_close($curl);
?>
