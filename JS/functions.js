var originalColor = [];
var thematicData = new Object();

async function LoadKMLData(URL) {

	console.log(URL)
	var mykml = new Cesium.KmlDataSource();
	await mykml.load(URL, {
		//clampToGround: true
	}).then( function (dataSource){
			var entities = dataSource.entities.values;
			for (let entity of entities) {
				entity.filePathOriginal = URL;
			}

			console.log("loaded")
		}
	);
	viewer.dataSources.add(mykml);
	viewer.flyTo(mykml.entities)

	thematicData.landEntities = mykml.entities.values;
	return mykml
}

async function LoadGEOJSONData(){

	console.log("geojson")

	var myjson = new Cesium.GeoJsonDataSource();
	await myjson.load('GEOJSON/polygon-samples.geojson', {
	}).then( function (dataSource){
			var entities = dataSource.entities.values;
			for (let entity of entities) {
				entity.filePathOriginal = 'GEOJSON/polygon-samples.geojson';
			}

		}
	);

	viewer.dataSources.add(myjson);
	viewer.flyTo(myjson.entities)

	// viewer.dataSources.add(Cesium.GeoJsonDataSource.load('GEOJSON/polygon-samples.geojson', {
	// 	stroke: Cesium.Color.HOTPINK,
	// 	fill: Cesium.Color.PINK,
	// 	strokeWidth: 3,
	// 	markerSymbol: '?'
	//   }));

}

function thematicView(type){

	$('#belowTableContainer').show()
	var legendsTable = legendsTable = document.getElementById("themLegend")

	console.log(legendsTable)

	switch(type){
		case "Land":
			console.log("land")
			$.ajax({
				type: "POST",
				url: 'getAPI.php',
				dataType: 'json',
				data: {
					func: 'getLandData',
				},
				success: function (obj, textstatus) {
					if (!obj.data) return;
					thematicData.landEntities.forEach((entity) => {
						if (!entity._kml.extendedData || !entity._polygon) {
							return;
						};

						if (entity._kml.extendedData.LOT.value !== undefined && !entity._polygon){
							return;
						};

						if(entity && entity._polygon && entity._polygon.material && entity._polygon.material._color && entity._polygon.material._color._value){
							var red = (entity._polygon.material._color._value.red) ? entity._polygon.material._color._value.red : 0;
							var green = (entity._polygon.material._color._value.green) ? entity._polygon.material._color._value.green : 0;
							var blue = (entity._polygon.material._color._value.blue) ? entity._polygon.material._color._value.blue : 0;
							var alpha = (entity._polygon.material._color._value.alpha) ? entity._polygon.material._color._value.alpha : 0;
							
							originalColor = [
								red, 
								green, 
								blue, 
								alpha
							];
						};

						for(let i = 0; i<obj.data.length; i++){
							let item = obj.data[i]

							if (!item.lot_id || item.lot_id == undefined) {
								continue; 
							}       
							else if ((entity._kml.extendedData.LOT.value === item.lot_id) && entity._kml.extendedData.STRUCTURE_.value === "AGRICULTURE") {
								entity._polygon.material = new Cesium.Color(0, 1, 0, 0.7)
								return;
							}
							else if ((entity._kml.extendedData.LOT.value === item.lot_id) && entity._kml.extendedData.STRUCTURE_.value === "RESIDENTIAL") {
								entity._polygon.material = new Cesium.Color(1, 0.5, 0, 1);
								return;
							}
							else if (entity._kml.extendedData.LOT.value !== undefined && entity._polygon && entity._kml.extendedData.LOT.value !== item.lot_id) {
								entity._polygon.material = new Cesium.Color(1, 0, 0, 0.7)
							}
							
						};
					});
				},
				error: function (xhr, textStatus, errorThrown) {
					console.log(xhr, errorThrown);
					$.alert({
						boxWidth: '30%',
						useBootstrap: false,
						title: 'Error',
						content: textStatus
					});
				}
			});
			legendsTable.innerHTML = "<tr><td style='background-color:rgb(10%, 95%, 3%);font-family: Arial, Helvetica, sans-serif;'>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</td><td>Aqriculture</td></tr>"
			legendsTable.innerHTML += "<tr><td style='background-color:rgb(100%, 50%, 0%, 100%);font-family: Arial, Helvetica, sans-serif;'>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</td><td>Residential</td></tr>"
			legendsTable.innerHTML += "<tr><td style='background-color:rgb(90%, 5%, 5%);font-family: Arial, Helvetica, sans-serif;'>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</td><td>Non-acquired</td></tr>"
				
		break;
		case "Building":
			if (!thematicData.landEntities) {
                return
            }
			console.log("bulding")
            thematicData.landEntities.forEach(function (entity) {
                if (entity._kml.extendedData !== undefined && entity._polygon) {
                    console.log(entity._kml.extendedData.Occupant)
                    switch (entity._kml.extendedData.Occupant.value) {
                        case "Unknown":
                            entity._polygon.material = new Cesium.Color(0.3, 0.3, 0.3, 1)

                            break;
                        case "Guard House 1":
                            entity._polygon.material = new Cesium.Color(0.45, 0.3, 0.05, 1)
                            break;
                        case "Guard House 2":
                            entity._polygon.material = new Cesium.Color(0.95, 0.9, 0.75, 1)
                            break;
                        case "PLC Main Building":
                            entity._polygon.material = new Cesium.Color(0.1, 0.3, 0.95, 1)
                            break;
                        case "Surau":
                            entity._polygon.material = new Cesium.Color(0.1, 0.9, 0.1, 1)
                            break;
                        default:
                            break;
                    }
                    entity._polygon.outlineColor = new Cesium.Color(0.1, 0.1, 0.1, 1)
                }
            })
            legendsTable.innerHTML = "<tr><td style='background-color:rgb(10%, 30%, 95%);font-family: Arial, Helvetica, sans-serif;'>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</td><td>PLC Main Building</td></tr>"
            legendsTable.innerHTML += "<tr><td style='background-color:rgb(10%, 90%, 10%);font-family: Arial, Helvetica, sans-serif;'>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</td><td>Surau</td></tr>"
            legendsTable.innerHTML += "<tr><td style='background-color:rgb(45%, 30%, 5%);font-family: Arial, Helvetica, sans-serif;'>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</td><td>Guard House 1</td></tr>"
            legendsTable.innerHTML += "<tr><td style='background-color:rgb(95%, 90%, 75%);font-family: Arial, Helvetica, sans-serif;'>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</td><td>Guard House 2</td></tr>"
            legendsTable.innerHTML += "<tr><td style='background-color:rgb(30%, 30%, 30%);font-family: Arial, Helvetica, sans-serif;'>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</td><td>Unknown</td></tr>"
            

			console.log(legendsTable.innerHTML)
		break;
	}

}

function getListOfData(){
	$.ajax({
		type: "POST",
		url: 'getAPI.php',
		dataType: 'json',
		data: {
			func: 'getDataList',
		},
		success: function (obj, textstatus) {

			var data = obj['data'];
			var url;
			let myhtml = '';

			for (var j = 0; j < data.length; j++) {

				url = data[j].data_url;
				myhtml +=
					'<tr>'+
						'<td id="data_name">'+data[j].data_name+'</td>'+
						'<td id="data_type">'+data[j].kml_type+'</td>'+
						'<td id="data_type">'+data[j].data_submitted_date+'</td>'+
						'<td id="data_icon" class="center-icons">';
				
				if(data[j].data_type == 'KML'){
					myhtml +=
							'<i class="fa-solid fa-binoculars" title="Fly To" onclick="LoadKMLData(\'' + url + '\')"></i>&nbsp;';
				}else{
					myhtml +=
							'<i class="fa-solid fa-binoculars" title="Fly To" onclick="LoadGEOJSONData(\'' + url + '\')"></i>&nbsp;';
				}

				myhtml +=
							'<i class="fa-solid fa-palette" title="Thematic" onclick="thematicView(\'' + data[j].kml_type + '\')"></i>'+
						'</td>'+
					'</tr>';
			}

			$('#data_layer').html(myhtml);
		},
		error: function (xhr, textStatus, errorThrown) {
			var str = textStatus + " " + errorThrown;
			console.log(str);
		}
	});
}

function linkLotData(){

	$('#layerList').hide()
	$('#uploadContainer').hide()
	$('#linkDataList').css('display', 'flex')
	$('#modal1').hide()

	$.ajax({
		type: "POST",
		url: 'getAPI.php',
		dataType: 'json',
		data: {
			func: 'linkLotData',
		},
		success: function (obj, textstatus) {

			var data = obj['data'];
			var url;
			let myhtml = '';

			for (var j = 0; j < data.length; j++) {

				url = data[j].data_url;
				myhtml +=
					'<tr>'+
						'<td id="land_id">'+data[j].land_id+'</td>'+
						'<td id="land_name">'+data[j].land_name+'</td>'+
						'<td id="land_date">'+data[j].submitted_date+'</td>'+
						'<td id="lot_id">'+data[j].land_type+'</td>'+
						'<td id="land_type">'+data[j].lot_id+'</td>'+
						'<td id="data_icon" class="center-icons">';

				myhtml +=
							'<button class="linkLot-button w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" onClick="updateLinkLot(this)" data-id='+data[j].land_id+' data-name='+data[j].land_name+'>Link</button>'+
						'</td>'+
					'</tr>';
			}

			$('#data_lot').html(myhtml);
		},
		error: function (xhr, textStatus, errorThrown) {
			var str = textStatus + " " + errorThrown;
			console.log(str);
		}
	});
}

function  openModalAssetList(){

	console.log("masuk")
 
    $.ajax({
        url: "getAPI.php",
        type: "POST",
        dataType: "JSON",
        data: {
            func: "fetchAssetDataList" 
        },
        success: (data) => {
            if (data.status === 'Success') {
                const $modal = $('#modal1');
                const $tableBody = $('#dataTable tbody');
        
                 
                $tableBody.empty();
          
                data.data.forEach(row => {
                    const tableRow = `
                        <tr>
                            <td>${row.id}</td>
                            <td>${row.Culvert_Na}</td>
                            <td>${row.layer}</td>
                            <td>${row.path}</td>
                            <td>${row.coordinates}</td>
                            <td>${row.asset_condition}</td>
                            <td><button class="fly-button" onClick="flyTo(this)" data-coordinates="${row.coordinates}">Fly To</button></td>
                            <td><button class="break-keep w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" onClick="editAsset(this)" data-id="${row.Culvert_Na}">Edit</button></td>
                        </tr>
                    `;
                    $tableBody.append(tableRow);
                });
        
                // Show the modal
                $modal.css('display', 'flex');
            } else {
                window.alert("Record not available.");
            }
        },

    });
    
 
}

function showLayerData(){
	getListOfData();

	$('#layerList').css('display', 'flex')
	$('#uploadContainer').hide()
	$('#modal1').hide()
	$('#linkDataList').hide()
}

function uploadLayer(){
	$('#layerList').hide()
	$('#uploadContainer').css('display', 'flex')
	$('#modal1').hide()
	$('#linkDataList').hide()

}

function flyTo(button) {
    
    const coordinates = button.getAttribute('data-coordinates');
    if (!coordinates) {
        console.error('Coordinates are missing');
        return;
    }

    const [longitude, latitude, height = 0] = coordinates.split(',').map(Number);

    if (typeof viewer === 'undefined') {
        console.error('Cesium viewer is not defined');
        return;
    }

    // Slightly increase the height to zoom out a bit
    const zoomedOutHeight = height + 500; // Adjust this value as needed

    // Fly to the specified coordinates
    viewer.camera.flyTo({
        destination: Cesium.Cartesian3.fromDegrees(longitude, latitude, zoomedOutHeight)
    });
}

function editAsset(button){ 
    event.preventDefault();
 
    const assetId = button.dataset.id;
 
    const url = `editAsset.html?asset_id=${assetId}`;
     
    window.open(url, '_blank', 'width=800,height=600');
}

function receiveDataFromPopup(data) {
    console.log("Data received from popup:", data); 
    openModalAssetList()
}

function showCustomPopup(entity, data) { 
    var amendRow = `<tr><th>Condition</th><td> ${data.asset_condition || 'No data available.'}</td></tr></tbody>`

    let updated_Infobox_Detail = entity._description._value.replace('</tbody></table></div>', amendRow+'</tbody></table></div>');
    $('.cesium-infoBox-description-lighter').find('table tbody').append(amendRow)
    
    entity._description._value = updated_Infobox_Detail
}

function updateLinkLot(){

	var id = $('.linkLot-button').data('id'); // Get the 'data-id' value
	var name = $('.linkLot-button').data('name'); // Get the 'data-id' value

	// Set values in the modal's form fields
	$('#dataId').val(id);
	$('#dataName').val(name);

	$('.modal-lot').css('display', 'flex');
}

function getListOfLot(){
	$.ajax({
		type: "POST",
		url: 'getAPI.php',
		dataType: 'json',
		data: {
			func: 'getLotList',
		},
		success: function (obj, textstatus) {

			console.log(obj.data)

			if (obj.bool) {
                var data = obj.data;
                let myhtml = '';

                for (var j = 0; j < data.length; j++) {
                    myhtml += '<option value="' + data[j].lot + '">' + data[j].lot + '</option>';
                }

                $('#lotId').html(myhtml);
            } else {
                console.error('Error:', obj.message || 'Unknown error occurred.');
            }
		},
		error: function (xhr, textStatus, errorThrown) {
			var str = textStatus + " " + errorThrown;
			console.log(str);
		}
	});
}

$(document).ready(function() {

	getListOfLot()

	$('#uploadData').click(function () {

		console.log('upload data')
		var kmlName = $('#kmlName').val();
		var kmlType = $('#kmlType').val();
		var structureType = $('#structureType').val();
		var fileData = $('#fileUpload')[0].files[0];

		var formData = new FormData();
		formData.append('kmlName', kmlName);
		formData.append('kmlType', kmlType);
		formData.append('structType', structureType);
		formData.append('fileUpload', fileData);
		formData.append('func', 'uploadDataLayer');

		$.ajax({
			url: 'postAPI.php', // The PHP file that will handle the upload
			type: 'POST',
			dataType: "JSON",
			data: formData,
			contentType: false, // Don't set any content type header
			processData: false, // Don't process the data
			success: function(response) {
				// Handle the response from the PHP file
				console.log('File uploaded successfully');
				console.log(response);

				$('#kmlName').val('');
				$('#kmlType').val('');
				$('#structureType').val('');
				$('#fileUpload').val(''); 
				$('#success-modal').css('display', 'flex');
			},
			error: function(xhr, status, error) {
				// Handle any errors
				console.log('Error uploading file');
				console.log(xhr.responseText);
			}
		});

	});

	$('#updateData').click(function () {
		var id = $('#dataId').val();
		var lot = $('#lotId').val();

		var formData = new FormData();
		formData.append('dataId', id);
		formData.append('dataLot', lot);
		formData.append('func', 'updateData');

		$.ajax({
			url: 'postAPI.php', // The PHP file that will handle the upload
			type: 'POST',
			data: formData,
			contentType: false, // Don't set any content type header
			processData: false, // Don't process the data
			success: function(response) {
				// Handle the response from the PHP file
				console.log('File uploaded successfully');
				console.log(response);

				$('#layerList').css('display', 'flex')
				$('#linkDataList').hide(); 
				$('#modalLot').fadeOut();
			},
			error: function(xhr, status, error) {
				// Handle any errors
				console.log('Error uploading file');
				console.log(xhr.responseText);
			}
		});
	});

	var modal = $('#modal1');
    
    $('#closeModal').on('click', function () {
		console.log("sinii")
		console.log(modal)
      modal.fadeOut();  
    });
   
    $(window).on('click', function (e) {
      if ($(e.target).is(modal)) {
        modal.fadeOut();
      }
    });

	// Close the modal when the user clicks the "OK" button or the close icon
	$('#success-btn-close, #success-btn-x').click(function() {
		$('#success-modal').css('display', 'none');
	});

	$('.close-button').click(function() {
		$('#success-modal').css('display', 'none');
	});

	$('#x-button').click(function() {
		$('#modalLot').fadeOut();
	});

	$('.close-list-button').click(function() {
		$('#uploadContainer').fadeOut();
		$('#layerList').fadeOut();
		$('#linkDataList').fadeOut();
	})

	$('#searchField').on('keyup', function () {
        const value = $(this).val().toLowerCase();
        $('#dataTable tbody tr').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});

