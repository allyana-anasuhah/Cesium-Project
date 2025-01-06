<?php

    include_once 'connect.php';

    $res = array();
    $func = filter_input(INPUT_POST, 'func', FILTER_SANITIZE_STRING);

    switch($func){
        case 'uploadDataLayer':
            $res = uploadDataLayer();
        break; 
        case "updateAssetData":
            updateAssetData();
        break; 
        case "updatedData":
            updateDataLot();
        break;
    }

    echo json_encode($res, true);

    function uploadDataLayer(){
        global $res, $CONN;

        $kmlName = filter_input(INPUT_POST, 'kmlName', FILTER_SANITIZE_STRING);
        $kmlType = filter_input(INPUT_POST, 'kmlType', FILTER_SANITIZE_STRING);
        $structType = filter_input(INPUT_POST, 'structType', FILTER_SANITIZE_STRING);

        //get max id in kml_data table
        $latest_id = $CONN->fetchOne("SELECT MAX(data_id)+1 as max_id FROM kml_data");

        if (!file_exists("KML/")) {
            mkdir("KML/", 0777, true);
        }

        if(isset($_FILES['fileUpload']) && $_FILES['fileUpload']['error'] == 0) {
            $fileName = $_FILES['fileUpload']['name'];
            $fileTmpName = $_FILES['fileUpload']['tmp_name'];
            $fileSize = $_FILES['fileUpload']['size'];
            $fileError = $_FILES['fileUpload']['error'];
            $fileType = $_FILES['fileUpload']['type'];

            $uploadDir = 'KML/';
            $uploadFile = $uploadDir . basename($fileName);
            $url = 'KML\\'.$fileName;

            move_uploaded_file($fileTmpName, $uploadFile);

            $sql = "INSERT INTO kml_data (data_id, data_name, data_url, data_type, kml_type, data_submitted_date) VALUES (:0, :1, :2, :3, :4, getDate())";
            $insSql = $CONN->execute($sql, array($latest_id, $kmlName, $url, $kmlType, $structType));   

            if(!$insSql){
                $res['bool'] = false;
                $res['msg'] = "SQL Error while adding data record!";
                return false;
            }

            $res['bool'] = true;
            $res['msg'] = "New record added!";
        }
    }

    function updateAssetData() {
        global $res;
        global $CON;
     
        $asset_condition = isset($_POST['asset_condition']) ? $_POST['asset_condition'] : null;
        $assetId = isset($_POST['assetId']) ? $_POST['assetId'] : null;
     
        if (empty($asset_condition) || empty($assetId)) {
            $res = [
                'status' => 'Error',
                'message' => 'Missing asset_condition or assetId'
            ];
            return;
        }
     
        $sqlUpdate = "UPDATE layer_data SET asset_condition = ? WHERE Culvert_Na = ?";
        $params = [$asset_condition, $assetId];
    
        $stmt = sqlsrv_query($CON, $sqlUpdate, $params);
     
        if ($stmt === false) {
            $res = [
                'status' => 'Error',
                'message' => 'Update failed: ' . print_r(sqlsrv_errors(), true)
            ];
            return;
        }
     
        $res = [
            'status' => 'Success',
            'message' => 'Asset condition updated successfully'
        ];
    }

    function updateDataLot(){
        global $res, $CONN;

        $data_id = filter_input(INPUT_POST, 'dataId', FILTER_VALIDATE_INT);
        $data_lot = filter_input(INPUT_POST, 'dataLot', FILTER_SANITIZE_STRING);
        
        if (!$data_id || !$data_lot) {
            $res['bool'] = false;
            $res['msg'] = "Invalid input data!";
            return false;
        }
    
        // Prepare the SQL query with named placeholders
        $updateSQL = "UPDATE land_data SET lot_id = :dataLot WHERE land_id = :dataId";
    
        try {
            // Execute the query with parameter binding
            $updated = $CONN->execute($updateSQL, array('dataId' => $data_id, 'dataLot' => $data_lot));
    
            if (!$updated) {
                $res['bool'] = false;
                $res['msg'] = "SQL Error while updating the record!";
                return false;
            }
    
            $res['bool'] = true;
            $res['msg'] = "Updated successfully!";
        } catch (Exception $e) {
            // Handle exceptions and log the error
            $res['bool'] = false;
            $res['msg'] = "Error: " . $e->getMessage();
            return false;
        }
    }

?>