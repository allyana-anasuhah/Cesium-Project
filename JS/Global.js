// var conOpData = {
// 	initiated: false,
// 	ajaxReq: null,
// 	dies: true,
// 	loadedFreq: null
// }

// var jogetConOpDraw = {
// 	processName: null,
// 	partialPath: null,
// 	flag: false,
// 	shape: null,
// 	entity: null,
// 	billboardEntities: [],
// 	coordsArray: [],
// 	hierarchyArray: null,
// 	coordsRad: [],
// 	loadedFreq: null,
// 	noCoordinate: false,
// 	drawEntity: function () {
// 		var colourProcess = "";
// 		var viewUse = viewer;

// 		switch (changePointColour) {
// 			case "colourNCR":
// 				colourProcess = Cesium.Color.ORANGERED
// 				break;
// 			case "colourWIR":
// 				colourProcess = Cesium.Color.LIME
// 				break;
// 			case "colourMS":
// 				colourProcess = Cesium.Color.BLUE
// 				break;
// 			case "colourRS":
// 				colourProcess = Cesium.Color.DARKGOLDENROD
// 				break;
// 			case "colourRFI":
// 				colourProcess = Cesium.Color.GOLD
// 				break;
// 			case "colourIR":
// 				colourProcess = Cesium.Color.HOTPINK
// 				break;
// 			case "colourMOS":
// 				colourProcess = Cesium.Color.AQUA
// 				break;
// 			case "colourSD":
// 				colourProcess = Cesium.Color.DARKSALMON
// 				break;
// 			case "colourSDL":
// 				colourProcess = Cesium.Color.DARKORCHID
// 				break;
// 			case "colourDCR":
// 				colourProcess = Cesium.Color.BLUEVIOLET
// 				break;
// 			case "colourNOI":
// 				colourProcess = Cesium.Color.BLUE
// 				break;
// 			case "colourPBC":
// 				colourProcess = Cesium.Color.CRIMSON
// 				break;
// 			case "colourDA":
// 				colourProcess = Cesium.Color.DARKORCHID
// 				break;
// 			default:
// 				colourProcess = Cesium.Color.BLUE
// 				break;
// 		}

// 		if(flagPolyProcess){
// 			viewUse = viewerNewProcess
// 		}
// 		if (jogetConOpDraw.coordsArray.length >= 6) {
// 			jogetConOpDraw.hierarchyArray = Cesium.Cartesian3.fromDegreesArray(jogetConOpDraw.coordsArray)
// 			return this.entity = viewUse.entities.add({
// 				polygon: {
// 					hierarchy: jogetConOpDraw.hierarchyArray,
// 					outline: true,
// 					height: 0,
// 					extrudedHeight: 2,
// 					material: colourProcess
// 				}
// 			});
// 		}

// 	}
// }

// var jogetLotParcel = {
// 	configure: null,
// 	coordsArray: []
// }

// var jogetConOpEntities = { NCR: [], WIR: [], RFI: [], MOS: [], MS: [], IR: [], RS: [], DCR : [], NOI : [], PBC : [], SP :[], PU :[], DA :[]  };

// var jogetAppProcesses = null;
// var constructPackageId = null;
// var isParent =null;
// if (localStorage.appsLinks) {
// 	jogetAppProcesses = JSON.parse(localStorage.appsLinks)
// 	if (jogetAppProcesses['constructPackage_name']) {
// 		constructPackageId = jogetAppProcesses['constructPackage_name'].split("::")[0]
// 	}
// }
// if(localStorage.isParent == "isParent"){
// 	isParent = localStorage.isParent;
// }

// var jogetProcessApp;
// var reviewTools ={listener: false, drawMode: "draw", orientationObj: null, camPosition: null};
var thematicData = new Object();
// var selectedEntityTest = null;
// var conOpEventListener = false;

// var wmsCapabilities = null;
// var wmsURL = GEOHOST+"/geoserver";
// var baseMapLayer



