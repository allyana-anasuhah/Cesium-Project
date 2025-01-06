$(document).ready(function () {
    const urlParams = new URLSearchParams(window.location.search);
    const asset_id = urlParams.get('asset_id');

    if (asset_id) {
        fetchAssetData(asset_id);
    }
});

function fetchAssetData(asset_id) {
    console.log("Asset Name: ", asset_id); 
    $.ajax({
        url: "getAPI.php",
        type: "POST",
        dataType: "JSON",
        data: {
            func: "fetchAssetData", 
            assetId: asset_id
        },
        success: (data) => {
            if (data.status === 'Success') {  
 
                $('#id').val(data.asset_data.id)
                $('#culvert_name').val(data.asset_data.Culvert_Na)
                $('#layer').val(data.asset_data.layer)
                $('#path').val(data.asset_data.path)
                $('#coordinates').val(data.asset_data.coordinates)
                $('#asset_condition').val(data.asset_data.asset_condition)  
            } else {
                window.alert("Record not available.")
            }
        },

    }); 
}


function updateAssetData() { 

    console.log("update asset data")

    event.preventDefault();
    var culvert_name = $('#culvert_name').val()
    var asset_condition = $('#asset_condition').val() 
    $.ajax({
        url: "postAPI.php",
        type: "POST",
        dataType: "JSON",
        data: {
            func: "updateAssetData", 
            assetId: culvert_name, 
            asset_condition: asset_condition
        },
        success: (data) => {
            if (data.status === 'Success') { 
                sendDataToParent()
            } else {
                window.alert("Record not available.")
            }
        },

    });
}

function sendDataToParent() {
    if (window.opener && !window.opener.closed) {
        window.opener.receiveDataFromPopup('Refresh Asset Table');
        window.close();
    } else {
        alert("Parent window is not available.");
    }
}
