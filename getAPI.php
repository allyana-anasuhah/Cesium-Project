<?php

    include_once 'connect.php';

    $res = array();
    $func = filter_input(INPUT_POST, 'func', FILTER_SANITIZE_STRING);

    switch($func){
        case 'getLandData':
            $res = getLandData();
        break;
        case 'getDataList':
            $res = getDataList();
        break;
        case "fetchAssetData":
            fetchAssetData();
        break; 
        case "fetchAssetDataList":
            fetchAssetDataList();
        break; 
        case "linkLotData":
            getLotData();
        break;
        case "getLotList":
            getLotList();
        break;
    }

    echo json_encode($res, true);

    function getLandData(){
        global $res, $CONN;

        if ($CONN === null) {
            die("Database connection is not initialized.");
        }

        $sql = "SELECT land_id, land_name, lot_id FROM land_data";
        $fetchSql = $CONN->fetchAll($sql);
        $res['bool'] = true;
        $res['data'] = $fetchSql;
        return $res;
    }

    function getDataList(){
        global $res, $CONN;

        if ($CONN === null) {
            die("Database connection is not initialized.");
        }

        $sql = "SELECT data_id, data_name, data_url, data_type, kml_type, data_submitted_date FROM kml_data";
        $fetchSql = $CONN->fetchAll($sql);
        $res['bool'] = true;
        $res['data'] = $fetchSql;
        return $res;

    }

    function fetchAssetData(){
        global $res;
        global $CON;
        $asset_id = $_POST['assetId'];
        $sql = "SELECT * FROM layer_data WHERE Culvert_Na = '$asset_id'";
        $stmt = sqlsrv_query($CON, $sql);
        
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        while ($row = sqlsrv_fetch_Array($stmt, SQLSRV_FETCH_ASSOC)) { 
            $Culvert_Na = $row['Culvert_Na'];
            $layer =  $row['layer'];
            $path =  $row['path'];
            $coordinates =  $row['coordinates'];  
            $asset_condition =  $row['asset_condition'];  
            $res['status'] = 'Success';
            $res['asset_data'] = $row;
        }
     
    }

    function fetchAssetDataList() {
        global $res;
        global $CON;
     
        $sql = "SELECT * FROM layer_data ";
        $stmt = sqlsrv_query($CON, $sql);
    
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    
        $data = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row; 
        }
    
        $res = [
            'status' => !empty($data) ? 'Success' : 'No Data Found',
            'data' => $data
        ];
    }

    function getLotData(){
        global $res, $CONN;

        if ($CONN === null) {
            die("Database connection is not initialized.");
        }

        $sql = "SELECT land_id, land_name, lot_id, submitted_date, land_type FROM land_data WHERE (lot_id IS NULL OR lot_id = '')";
        $fetchSql = $CONN->fetchAll($sql);
        $res['bool'] = true;
        $res['data'] = $fetchSql;
        return $res;
    }

    function getLotList(){
        global $res, $CONN;

        if ($CONN === null) {
            die("Database connection is not initialized.");
        }

        $sql = "SELECT DISTINCT lot FROM lot_lookup";
        $fetchSql = $CONN->fetchAll($sql);
        $res['bool'] = true;
        $res['data'] = $fetchSql;
        return $res;
    }

    
?>