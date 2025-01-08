<?php
$kmlDetails = array();
 if (!isset($_POST['url'])) {
     echo json_encode($kmlDetails['error'] = "File url not available");
     exit();
 }

$file_name = "../".$_POST['url'];//'sample/Tower-OnlyPins.kml'; //fileName Here

$kml_file = simplexml_load_file($file_name);
$namespaces = $kml_file->getNamespaces(true);

if (!isset($namespaces[""])) {
    echo json_encode($kmlDetails['error'] = "KML namespace unavailable");
    exit();
}

$kml_file->registerXPathNamespace("default", $namespaces[""]);
$folderXpath = "//default:Folder";
$placemarkStyleURLXpath = "//default:Placemark//default:styleUrl";
$pointXpath = "//default:Point/..";
$lineXpath = "//default:LineString/..";
$polygonXpath = "//default:Polygon/..";
$multiGeomXpath = "//default:MultiGeometry/..";
$placemarkStyleXpath = "//default:Placemark//default:Style";
$overallStyleXpath = "//default:Style";

$numberPlacemarkStyle = count($kml_file->xpath($placemarkStyleXpath));
$numberAllStyle = count($kml_file->xpath($overallStyleXpath));

//determine style tag position
if ($numberAllStyle >= 1) {
    if ($numberPlacemarkStyle >= 1 && ($numberPlacemarkStyle - $numberAllStyle) == 0) {  // to ensure no style under placemark and document at the same time
        $kmlDetails['styleTagPosition'] = "Placemark";  //inline
    } elseif ($numberPlacemarkStyle >= 1 && ($numberPlacemarkStyle - $numberAllStyle) !== 0) {
        $kmlDetails['styleTagPosition'] = "Both";   //mixed
    } else {
        $kmlDetails['styleTagPosition'] = "Document";   //shared
    }
} else {
    $kmlDetails['styleTagPosition'] = null;
    $kmlDetails['error'] = 'No style tag found';
    exit();
}

if ($kml_file->xpath($pointXpath)) {
    $pointXquery = $kml_file->xpath($pointXpath);
    $kmlDetails['pointType'] = true;
    if ($kml_file->xpath($placemarkStyleURLXpath)) {      
        foreach ($pointXquery as $item) {
            if ($item->styleUrl) {
                //filter all styleUrl of point to check whether more than 1 style exist
                $filterUrl = array_filter($pointXquery, function ($val) use ($item) {
                    if ((string) $item->styleUrl == (string)$val->styleUrl) {
                        return false;
                    } else {
                        return true;
                    }
                });
                if (count($filterUrl) > 1) {
                    $kmlDetails['pointStyle']['MultilpleStyles'] = true; //more than 1 line style
                } else {
                    $pointStyleURL = (string) $item->styleUrl;
                    $pointStyleId = substr($pointStyleURL, 1);   //only 1 line style found
                    //go to function
                    $kmlDetails['pointStyle'] = catchPointStyleValues($kml_file, $pointStyleId);
                }
                break;
            } else {
                //multigeometry
                $newXPath = $item->xpath('..');
                if ($newXPath[0]->styleUrl) {
                        //retrieve values
                    $pointStyleURL = (string) $item->styleUrl;
                    $pointStyleId = substr($pointStyleURL, 1);   //only 1 line style found
                    //go to function
                    $kmlDetails['pointStyle'] = catchPointStyleValues($kml_file, $pointStyleId);
                    
                    break;
                }
            }
        }
    } elseif ($kmlDetails['styleTagPosition'] == "Placemark" && $kml_file->xpath($placemarkStyleXpath)) {
        foreach ($pointXquery as $item) {
            if (key($item)=="Style") {
                //filter all styleUrl of point to check whether more than 1 style exist
                $filterUrl = array_filter($pointXquery, function ($val) use ($item) {
                    if (key($val) !== "Style" || (string) $item->Style == (string)$val->Style) {
                        return false;
                    } else {
                        return true;
                    }
                });
                if (count($filterUrl) > 1) {
                    $kmlDetails['pointStyle']['MultilpleStyles'] = true; //more than 1 line style
                } else {
                    $pointColor = (string)$pointXquery[0]->IconStyle->color;
                    $kmlDetails['pointStyle']['PointColor'] = color_kml_swap($pointColor);
                    $kmlDetails['pointStyle']['PointOpacity'] = opacity_hexdec($pointColor);
                    $kmlDetails['pointStyle']['PointColorMode'] = (string)$pointXquery[0]->IconStyle->colorMode;
                    $kmlDetails['pointStyle']['PointScale'] = (string)$pointXquery[0]->IconStyle->scale;
                    $kmlDetails['pointStyle']['PointHref'] = (string)$pointXquery[0]->IconStyle->Icon->href;
                }
                break;
            } else {
                //multigeometry
                $newXPath = $item->xpath('..');
                if ($newXPath[0]->Style) {
                    $filterUrl = array_filter($newXPath[0], function ($val) {
                        if (key($val) !== "Style" || (string) $item->Style == (string) $val->Style) {
                            return false;
                        } else {
                            return true;
                        }
                    });
                    if (count($filterUrl) > 1) {
                        $kmlDetails['pointStyle']['MultilpleStyles'] = true; //more than 1 polygon style
                    } else {
                        //retrieve values
                        $pointColor = (string)$newXPath[0]->Style->IconStyle->color;
                        $kmlDetails['pointStyle']['PointColor'] = color_kml_swap($pointColor);
                        $kmlDetails['pointStyle']['PointOpacity'] = opacity_hexdec($pointColor);
                        $kmlDetails['pointStyle']['PointColorMode'] = (string)$newXPath[0]->Style->IconStyle->colorMode;
                        $kmlDetails['pointStyle']['PointScale'] = (string)$newXPath[0]->Style->IconStyle->scale;
                        $kmlDetails['pointStyle']['PointHref'] = (string)$newXPath[0]->Style->IconStyle->Icon->href;
                    }
                    break;
                }
            }
        }
    } elseif ($kmlDetails['styleTagPosition'] == "Both" && $kml_file->xpath($placemarkStyleXpath)) {
		foreach ($pointXquery as $item) {
            if (key($item)=="Style") {
                //filter all styleUrl of point to check whether more than 1 style exist
                $filterUrl = array_filter($pointXquery, function ($val) use ($item) {
                    if (key($val) !== "Style" || (string) $item->Style == (string)$val->Style) {
                        return false;
                    } else {
                        return true;
                    }
                });
                if (count($filterUrl) > 1) {
                    $kmlDetails['pointStyle']['MultilpleStyles'] = true; //more than 1 line style
                } else {
                    $pointColor = (string)$item->Style->IconStyle->color;
                    $kmlDetails['pointStyle']['PointColor'] = color_kml_swap($pointColor);
                    $kmlDetails['pointStyle']['PointOpacity'] = opacity_hexdec($pointColor);
                    $kmlDetails['pointStyle']['PointColorMode'] = (string)$item->Style->IconStyle->colorMode;
                    $kmlDetails['pointStyle']['PointScale'] = (string)$item->Style->IconStyle->scale;
                    $kmlDetails['pointStyle']['PointHref'] = (string)$item->Style->IconStyle->Icon->href;
                }
                break;
            } else {
                //multigeometry
                $newXPath = $item->xpath('..');
                if ($newXPath[0]->Style) {
                    $filterUrl = array_filter($newXPath[0], function ($val) {
                        if (key($val) !== "Style" || (string) $item->Style == (string) $val->Style) {
                            return false;
                        } else {
                            return true;
                        }
                    });
                    if (count($filterUrl) > 1) {
                        $kmlDetails['pointStyle']['MultilpleStyles'] = true; //more than 1 polygon style
                    } else {
                        //retrieve values
                        $pointColor = (string)$newXPath[0]->Style->IconStyle->color;
                        $kmlDetails['pointStyle']['PointColor'] = color_kml_swap($pointColor);
                        $kmlDetails['pointStyle']['PointOpacity'] = opacity_hexdec($pointColor);
                        $kmlDetails['pointStyle']['PointColorMode'] = (string)$newXPath[0]->Style->IconStyle->colorMode;
                        $kmlDetails['pointStyle']['PointScale'] = (string)$newXPath[0]->Style->IconStyle->scale;
                        $kmlDetails['pointStyle']['PointHref'] = (string)$newXPath[0]->Style->IconStyle->Icon->href;
                    }
                    break;
                }
            }
        }
    }
}

//check for polyline only, ignoring polygon's
if ($kml_file->xpath($lineXpath)) {
    $lineXquery = $kml_file->xpath($lineXpath);
    $kmlDetails['lineType'] = true;
    if ($kmlDetails['styleTagPosition'] == "Document" && $kml_file->xpath($placemarkStyleURLXpath)) {
        foreach ($lineXquery as $item) {	
            if ($item->styleUrl) {
                //filter all styleUrl of polyline to check whether more than 1 style exist
                $filterUrl = array_filter($lineXquery, function ($val) use ($item) {
                    if ((string) $item->styleUrl == (string)$val->styleUrl) {
                        return false;
                    } else {
                        return true;
                    }
                });
                
                if (count($filterUrl) > 1) {
                    $kmlDetails['lineStyle']['MultilpleStyles'] = true; //more than 1 line style
                } else {
                    $lineStyleURL = (string) $item->styleUrl;
                    $lineStyleId = substr($lineStyleURL, 1);   //only 1 line style found
                    //go to function
                    $kmlDetails['lineStyle'] = catchLineStyleValues($kml_file, $lineStyleId);
                }
                break;
            } else {
                //multigeometry
                $newXPath = $item->xpath('..');
                if ($newXPath[0]->styleUrl) {

                    $filterUrl = array_filter($kml_file->xpath($placemarkStyleURLXpath), function ($val) use ($newXPath) {
                        if ((string)$newXPath[0]->styleUrl == (string)$val->styleUrl) {
                            return false;
                        } else {
                            return true;
                        }
                    });

                    if (count($filterUrl) > 1) {
                        $kmlDetails['lineStyle']['MultilpleStyles'] = true; //more than 1 line style
                    } else {
                        //retrieve values
                        $lineStyleURL = (string) $newXPath[0]->styleUrl;
                        $lineStyleId = substr($lineStyleURL, 1);   //only 1 line style found
                        //go to function
                        
                        $kmlDetails['lineStyle'] = catchLineStyleValues($kml_file, $lineStyleId);
                    }


                  
                    
                    break;
                }
            }
        }
    } elseif ($kmlDetails['styleTagPosition'] == "Placemark" && $kml_file->xpath($placemarkStyleXpath)) {
        foreach ($lineXquery as $item) {
            if (key($item)=="Style") {
                //filter all styleUrl of polyline to check whether more than 1 style exist
                $filterUrl = array_filter($lineXquery, function ($val) use ($item) {
                    if (key($val) !== "Style" ||(string) $item->Style == (string)$val->Style) {
                        return false;
                    } else {
                        return true;
                    }
                });
                if (count($filterUrl) > 1) {
                    $kmlDetails['lineStyle']['MultilpleStyles'] = true; //more than 1 line style
                } else {
                    $lineColor = (string)$item->Style->LineStyle->color;
                    $kmlDetails['lineStyle']['LineColor'] = color_kml_swap($lineColor);
                    $kmlDetails['lineStyle']['LineOpacity'] = opacity_hexdec($lineColor);
                    $kmlDetails['lineStyle']['LineColorMode'] = (string)$item->Style->LineStyle->colorMode;
                    $kmlDetails['lineStyle']['LineWidth'] = (string)$item->Style->LineStyle->width;
                }
                break;
            } else {
                //multigeometry
                $newXPath = $item->xpath('..');
                if ($newXPath[0]->Style) {
                    // $filterUrl = array_filter($newXPath[0], function ($val) {
                    //     if (key($val) !== "Style" || (string) $item->Style == (string) $val->Style) {
                    //         return false;
                    //     } else {
                    //         return true;
                    //     }
                    // });
                    $filterUrl = array_filter($kml_file->xpath($placemarkStyleXpath), function ($val) use ($newXPath) {
                        if ((string)$newXPath[0]->Style == (string)$val->Style) {
                            return false;
                        } else {
                            return true;
                        }
                    });
                    if (count($filterUrl) > 1) {
                        $kmlDetails['lineStyle']['MultilpleStyles'] = true; //more than 1 polygon style
                    } else {
                        //retrieve values
                        $lineColor = (string)$newXPath[0]->Style->LineStyle->color;
                        $kmlDetails['lineStyle']['LineColor'] = color_kml_swap($lineColor);
                        $kmlDetails['lineStyle']['LineOpacity'] = opacity_hexdec($lineColor);
                        $kmlDetails['lineStyle']['LineColorMode'] = (string)$newXPath[0]->Style->LineStyle->colorMode;
                        $kmlDetails['lineStyle']['LineWidth'] = (string)$newXPath[0]->Style->LineStyle->width;
                    }
                    break;
                }
            }
        }
    } elseif ($kmlDetails['styleTagPosition'] == "Both" && $kml_file->xpath($placemarkStyleXpath)) {
		foreach ($lineXquery as $item) {
            if (key($item)=="Style") {
                //filter all styleUrl of point to check whether more than 1 style exist
                $filterUrl = array_filter($lineXquery, function ($val) use ($item) {
                    if (key($val) !== "Style" || (string) $item->Style == (string)$val->Style) {
                        return false;
                    } else {
                        return true;
                    }
                });
                if (count($filterUrl) > 1) {
                    $kmlDetails['lineStyle']['MultilpleStyles'] = true; //more than 1 line style
                } else {
					$lineColor = (string)$lineXquery[0]->LineStyle->color;
					$kmlDetails['lineStyle']['LineColor'] = color_kml_swap($lineColor);
					$kmlDetails['lineStyle']['LineOpacity'] = opacity_hexdec($lineColor);
					$kmlDetails['lineStyle']['LineColorMode'] = (string)$newXPath[0]->Style->LineStyle->colorMode;
					$kmlDetails['lineStyle']['LineWidth'] = (string)$newXPath[0]->Style->LineStyle->width;
                }
                break;
            } else {
                //multigeometry
                $newXPath = $item->xpath('..');
                if ($newXPath[0]->Style) {
                    $filterUrl = array_filter($newXPath[0], function ($val) {
                        if (key($val) !== "Style" || (string) $item->Style == (string) $val->Style) {
                            return false;
                        } else {
                            return true;
                        }
                    });
                    if (count($filterUrl) > 1) {
                        $kmlDetails['lineStyle']['MultilpleStyles'] = true; //more than 1 polygon style
                    } else {
                        //retrieve values
                        $lineColor = (string)$lineXquery[0]->LineStyle->color;
                        $kmlDetails['lineStyle']['LineColor'] = color_kml_swap($lineColor);
                        $kmlDetails['lineStyle']['LineOpacity'] = opacity_hexdec($lineColor);
                        $kmlDetails['lineStyle']['LineColorMode'] = (string)$newXPath[0]->Style->LineStyle->colorMode;
                        $kmlDetails['lineStyle']['LineWidth'] = (string)$newXPath[0]->Style->LineStyle->width;
                    }
                    break;
                }
            }
        }
    }
}



if ($kml_file->xpath($polygonXpath)) {
    $kmlDetails['polygonType'] = true;
    $polygonXquery = $kml_file->xpath($polygonXpath);
    if ($kmlDetails['styleTagPosition'] == "Document" && $kml_file->xpath($placemarkStyleURLXpath)) {
        foreach ($kml_file->xpath($polygonXpath) as $item) {
            if ($item->styleUrl) {
                //filter all styleUrl of polygon to check whether more than 1 style exist
                $filterUrl = array_filter($polygonXquery, function ($val) use ($item) {
                    if ((string) $item->styleUrl == (string) $val->styleUrl) {
                        return false;
                    } else {
                        return true;
                    }
                });
                if (count($filterUrl) > 1) {
                    $kmlDetails['polygonStyle']['MultilpleStyles'] = true; //more than 1 polygon style
                } else {
                    $polygonStyleURL = (string) $item->styleUrl;
                    $polygonStyleId = substr($polygonStyleURL, 1);   //only 1 polygon style found
                    //go to function
                    $kmlDetails['polygonStyle'] = catchPolygonStyleValues($kml_file, $polygonStyleId);
                }
                break;
            } else {
                //multigeometry
                $newXPath = $item->xpath('..');
                if ($newXPath[0]->styleUrl) {
                    $filterUrl = array_filter($polygonXquery, function ($val) use ($newXPath) {
                        if ((string) $newXPath[0]->styleUrl == (string) $val->styleUrl) {
                            return false;
                        } else {
                            return true;
                        }
                    });
                    if (count($filterUrl) > 1) {
                        $kmlDetails['polygonStyle']['MultilpleStyles'] = true; //more than 1 polygon style
                    } else {
                        $polygonStyleURL = (string) $newXPath[0]->styleUrl;
                        $polygonStyleId = substr($polygonStyleURL, 1);   //only 1 polygon style found
                        //go to function
                        $kmlDetails['polygonStyle'] = catchPolygonStyleValues($kml_file, $polygonStyleId);
                    }
                    break;
                }

            }
        }
    } elseif ($kmlDetails['styleTagPosition'] == "Placemark" && $kml_file->xpath($placemarkStyleXpath)) {
		//catch style tag, filter to compare with other tag in same feature type
        foreach ($polygonXquery as $item) {
            if (key($item) =="Style") {
                //filter all styleUrl of polygon to check whether more than 1 style exist
                $filterUrl = array_filter($polygonXquery, function ($val) use ($item) {
                    if (key($val) !== "Style" || (string) $item->Style == (string) $val->Style) {
                        return false;
                    } else {
                        return true;
                    }
                });
                //print("<pre>".print_r($item->Style->PolyStyle->fill,true)."</pre>");
                if (count($filterUrl) > 1) {
                    $kmlDetails['polygonStyle']['MultilpleStyles'] = true; //more than 1 polygon style
                } else {
                    //retrieve values
                    $polygonColor = (string)$item->Style->PolyStyle->color;
                    $outLineColor = (string)$item->Style->LineStyle->color;
                    $kmlDetails['polygonStyle']['PolygonColor'] = color_kml_swap($polygonColor);
                    $kmlDetails['polygonStyle']['PolygonOpacity'] = opacity_hexdec($polygonColor);
                    $kmlDetails['polygonStyle']['PolygonColorMode'] = (string)$item->Style->PolyStyle->colorMode;
                    $kmlDetails['polygonStyle']['PolygonFill'] = (string)$item->Style->PolyStyle->fill;
                    $kmlDetails['polygonStyle']['PolygonOutl'] = (string)$item->Style->PolyStyle->outline;
                    $kmlDetails['polygonStyle']['PolygonOutlColor'] = color_kml_swap($outLineColor);
                    $kmlDetails['polygonStyle']['PolygonOutlOpacity'] = opacity_hexdec($outLineColor);
                    $kmlDetails['polygonStyle']['PolygonOutlColorMode'] = (string)$item->Style->LineStyle->colorMode;
                    $kmlDetails['polygonStyle']['PolygonOutlWidth'] = (string)$item->Style->LineStyle->width;
                 
                }
                break;
            } else {
                //multigeometry
                $newXPath = $item->xpath('..');
                if ($newXPath[0]->Style) {
                    $filterUrl = array_filter($newXPath, function ($val) {
                        if (key($val) !== "Style" || (string) $val->Style == (string) $val->Style) {
                            return false;
                        } else {
                            return true;
                        }
                    });
                    if (count($filterUrl) > 1) {
                        $kmlDetails['polygonStyle']['MultilpleStyles'] = true; //more than 1 polygon style
                    } else {
                        //retrieve values
                        $polygonColor = (string)$newXPath[0]->Style->PolyStyle->color;
                        $outLineColor = (string)$newXPath[0]->Style->LineStyle->color;
                        $kmlDetails['polygonStyle']['PolygonColor'] = color_kml_swap($polygonColor);
                        $kmlDetails['polygonStyle']['PolygonOpacity'] = opacity_hexdec($polygonColor);
                        $kmlDetails['polygonStyle']['PolygonColorMode'] = (string)$newXPath[0]->Style->PolyStyle->colorMode;
                        $kmlDetails['polygonStyle']['PolygonFill'] = (string)$newXPath[0]->Style->PolyStyle->fill;
                        $kmlDetails['polygonStyle']['PolygonOutl'] = (string)$newXPath[0]->Style->PolyStyle->outline;
                        $kmlDetails['polygonStyle']['PolygonOutlColor'] = color_kml_swap($outLineColor);
                        $kmlDetails['polygonStyle']['PolygonOutlOpacity'] = opacity_hexdec($outLineColor);
                        $kmlDetails['polygonStyle']['PolygonOutlColorMode'] = (string)$newXPath[0]->Style->LineStyle->colorMode;
                        $kmlDetails['polygonStyle']['PolygonOutlWidth'] = (string)$newXPath[0]->Style->LineStyle->width;
                        $kmlDetails['polygonStyle']['PolygonStyleId'] = "asdas";
                    }
                    break;
                }
            }
        }
    } elseif ($kmlDetails['styleTagPosition'] == "Both" && $kml_file->xpath($placemarkStyleXpath)) {
		foreach ($polygonXquery as $item) {
            if (key($item) =="Style") {
                //filter all styleUrl of point to check whether more than 1 style exist
                $filterUrl = array_filter($polygonXquery, function ($val) use ($item) {
                    if (key($val) !== "Style" || (string) $item->Style == (string)$val->Style) {
                        return false;
                    } else {
                        return true;
                    }
                });
                if (count($filterUrl) > 1) {
                    $kmlDetails['pointStyle']['MultilpleStyles'] = true; //more than 1 line style
                } else {
					$polygonColor = (string)$polygonXquery[0]->PolyStyle->color;
                    $outLineColor = (string)$polygonXquery[0]->LineStyle->color;
                    $kmlDetails['polygonStyle']['PolygonColor'] = color_kml_swap($polygonColor);
                    $kmlDetails['polygonStyle']['PolygonOpacity'] = opacity_hexdec($polygonColor);
                    $kmlDetails['polygonStyle']['PolygonColorMode'] = (string)$polygonXquery[0]->PolyStyle->colorMode;
                    $kmlDetails['polygonStyle']['PolygonFill'] = (string)$polygonXquery[0]->PolyStyle->fill;
                    $kmlDetails['polygonStyle']['PolygonOutl'] = (string)$polygonXquery[0]->PolyStyle->outline;
                    $kmlDetails['polygonStyle']['PolygonOutlColor'] = color_kml_swap($outLineColor);
                    $kmlDetails['polygonStyle']['PolygonOutlOpacity'] = opacity_hexdec($outLineColor);
                    $kmlDetails['polygonStyle']['PolygonOutlColorMode'] = (string)$polygonXquery[0]->LineStyle->colorMode;
                    $kmlDetails['polygonStyle']['PolygonOutlWidth'] = (string)$polygonXquery[0]->LineStyle->width;
                }
                break;
			} 
			else {
                //multigeometry
                $newXPath = $item->xpath('..');
                if ($newXPath[0]->Style) {
                    $newXPath0 = (array) $newXPath;
                    $filterUrl = array_filter( $newXPath0, function ($val) {
                        if (key($val) !== "Style" || (string) $item->Style == (string) $val->Style) {
                            return false;
                        } else {
                            return true;
                        }
                    });
                    if (count($filterUrl) > 1) {
                        $kmlDetails['pointStyle']['MultilpleStyles'] = true; //more than 1 polygon style
                    } else {
                        //retrieve values
						$polygonColor = (string)$newXPath[0]->Style->PolyStyle->color;
						$outLineColor = (string)$newXPath[0]->Style->LineStyle->color;
						$kmlDetails['polygonStyle']['PolygonColor'] = color_kml_swap($polygonColor);
						$kmlDetails['polygonStyle']['PolygonOpacity'] = opacity_hexdec($polygonColor);
						$kmlDetails['polygonStyle']['PolygonColorMode'] = (string)$newXPath[0]->Style->PolyStyle->colorMode;
						$kmlDetails['polygonStyle']['PolygonFill'] = (string)$newXPath[0]->Style->PolyStyle->fill;
						$kmlDetails['polygonStyle']['PolygonOutl'] = (string)$newXPath[0]->Style->PolyStyle->outline;
						$kmlDetails['polygonStyle']['PolygonOutlColor'] = color_kml_swap($outLineColor);
						$kmlDetails['polygonStyle']['PolygonOutlOpacity'] = opacity_hexdec($outLineColor);
						$kmlDetails['polygonStyle']['PolygonOutlColorMode'] = (string)$newXPath[0]->Style->LineStyle->colorMode;
						$kmlDetails['polygonStyle']['PolygonOutlWidth'] = (string)$newXPath[0]->Style->LineStyle->width;
                    }
                    break;
                }
            }
		}
		
    }
}


function catchPointStyleValues($kml_file, $pointStyleId)
{ // function which return the style value
	$pointStyleArray = array();
	if($kml_file->xpath("//default:StyleMap[@id='".$pointStyleId."']")){
		$styleMapXquery = $kml_file->xpath("//default:StyleMap[@id='".$pointStyleId."']");
		foreach ($styleMapXquery[0] as $key=>$Pair) {
			if ($Pair->key == "normal") {
				$styleMapIdRaw = (string)$Pair->styleUrl;
				$styleMapId = substr($styleMapIdRaw, 1);
				$styleXquery = $kml_file->xpath("//default:Style[@id='".$styleMapId."']");
				$pointColor = (string)$styleXquery[0]->IconStyle->color;
				$pointStyleArray['PointColor'] = color_kml_swap($pointColor);
				$pointStyleArray['PointOpacity'] =  opacity_hexdec($pointColor);
				$pointStyleArray['PointColorMode'] = (string)$styleXquery[0]->IconStyle->colorMode;
				$pointStyleArray['PointScale'] = (string)$styleXquery[0]->IconStyle->scale;
				$pointStyleArray['PointHref'] = (string)$styleXquery[0]->IconStyle->Icon->href;
				$pointStyleArray['PointStyleId'] = $styleMapId;
				break;
			}
		}
	}
	else{
		$styleXquery = $kml_file->xpath("//default:Style[@id='".$pointStyleId."']");
		$pointColor = (string)$styleXquery[0]->IconStyle->color;
		$pointStyleArray['PointColor'] = color_kml_swap($pointColor);
		$pointStyleArray['PointOpacity'] =  opacity_hexdec($pointColor);
		$pointStyleArray['PointColorMode'] = (string)$styleXquery[0]->IconStyle->colorMode;
		$pointStyleArray['PointScale'] = (string)$styleXquery[0]->IconStyle->scale;
		$pointStyleArray['PointHref'] = (string)$styleXquery[0]->IconStyle->Icon->href;
		$pointStyleArray['PointStyleId'] = $pointStyleId;
	}
   
    return $pointStyleArray;
}

function catchLineStyleValues($kml_file, $lineStyleId)
{ // function which return the style value
	$lineStyleArray = array();
	if($kml_file->xpath("//default:StyleMap[@id='".$lineStyleId."']")){
		$styleMapXquery = $kml_file->xpath("//default:StyleMap[@id='".$lineStyleId."']");
		foreach ($styleMapXquery[0] as $key=>$Pair) {
			if ($Pair->key == "normal") {
				$styleMapIdRaw = (string)$Pair->styleUrl;
				$styleMapId = substr($styleMapIdRaw, 1);
				$styleXquery = $kml_file->xpath("//default:Style[@id='".$styleMapId."']");
				$lineColor = (string)$styleXquery[0]->LineStyle->color;
				$lineStyleArray['LineColor'] = color_kml_swap($lineColor);
				$lineStyleArray["LineOpacity"] = opacity_hexdec($lineColor);
				$lineStyleArray['LineColorMode'] = (string)$styleXquery[0]->LineStyle->colorMode;
				$lineStyleArray['LineWidth'] = (string)$styleXquery[0]->LineStyle->width;
				$lineStyleArray['LineStyleId'] = $styleMapId;
				break;
			}
		}
	}
	else{
		$styleXquery = $kml_file->xpath("//default:Style[@id='".$lineStyleId."']");
		$lineColor = (string)$styleXquery[0]->LineStyle->color;
		$lineStyleArray['LineColor'] = color_kml_swap($lineColor);
		$lineStyleArray["LineOpacity"] = opacity_hexdec($lineColor);
		$lineStyleArray['LineColorMode'] = (string)$styleXquery[0]->LineStyle->colorMode;
		$lineStyleArray['LineWidth'] = (string)$styleXquery[0]->LineStyle->width;
		$lineStyleArray['LineStyleId'] = $lineStyleId;
	}

    return $lineStyleArray;
}

function catchPolygonStyleValues($kml_file, $polygonStyleId)
{
	$polyStyleArray = array();
	if($kml_file->xpath("//default:StyleMap[@id='".$polygonStyleId."']")){
		$styleMapXquery = $kml_file->xpath("//default:StyleMap[@id='".$polygonStyleId."']");
		foreach ($styleMapXquery[0] as $key=>$Pair) {
			if ($Pair->key == "normal") {
				$styleMapIdRaw = (string)$Pair->styleUrl;
				$styleMapId = substr($styleMapIdRaw, 1);
				$styleXquery = $kml_file->xpath("//default:Style[@id='".$styleMapId."']");
	
				$polygonColor = (string)$styleXquery[0]->PolyStyle->color;
				$outLineColor = (string)$styleXquery[0]->LineStyle->color;
	
				$polyStyleArray['PolygonColor'] = color_kml_swap($polygonColor);
				$polyStyleArray['PolygonOpacity'] = opacity_hexdec($polygonColor);
				$polyStyleArray['PolygonColorMode'] = (string)$styleXquery[0]->PolyStyle->colorMode;
				$polyStyleArray['PolygonFill'] = (string)$styleXquery[0]->PolyStyle->fill;
				$polyStyleArray['PolygonOutl'] = (string)$styleXquery[0]->PolyStyle->outline;
				$polyStyleArray['PolygonOutlColor'] = color_kml_swap($outLineColor);
				$polyStyleArray['PolygonOutlOpacity'] = opacity_hexdec($outLineColor);
				$polyStyleArray['PolygonOutlColorMode'] = (string)$styleXquery[0]->LineStyle->colorMode;
				$polyStyleArray['PolygonOutlWidth'] = (string)$styleXquery[0]->LineStyle->width;
				$polyStyleArray['PolygonStyleId'] = $styleMapId;
				break;
			}
		}
	}
    else{
		$styleXquery = $kml_file->xpath("//default:Style[@id='".$polygonStyleId."']");
	
		$polygonColor = (string)$styleXquery[0]->PolyStyle->color;
		$outLineColor = (string)$styleXquery[0]->LineStyle->color;

		$polyStyleArray['PolygonColor'] = color_kml_swap($polygonColor);
		$polyStyleArray['PolygonOpacity'] = opacity_hexdec($polygonColor);
		$polyStyleArray['PolygonColorMode'] = (string)$styleXquery[0]->PolyStyle->colorMode;
		$polyStyleArray['PolygonFill'] = (string)$styleXquery[0]->PolyStyle->fill;
		$polyStyleArray['PolygonOutl'] = (string)$styleXquery[0]->PolyStyle->outline;
		$polyStyleArray['PolygonOutlColor'] = color_kml_swap($outLineColor);
		$polyStyleArray['PolygonOutlOpacity'] = opacity_hexdec($outLineColor);
		$polyStyleArray['PolygonOutlColorMode'] = (string)$styleXquery[0]->LineStyle->colorMode;
		$polyStyleArray['PolygonOutlWidth'] = (string)$styleXquery[0]->LineStyle->width;
		$polyStyleArray['PolygonStyleId'] = $polygonStyleId;
	}
    return $polyStyleArray;
}

//######################################
//Functions to call
function color_kml_swap($color_code)
{
    if(!$color_code){
        return;
    }
    
    if (strlen($color_code)==8) {
        $color_code = substr($color_code, 2);
    } //remove the first 2 char(opacity)
    $color_arr = str_split($color_code, 2);
    $color_code = $color_arr[2].$color_arr[1].$color_arr[0];
    return $color_code;
}

function opacity_hexdec($color_code)
{
    if ($color_code == null) {
        $color_code = 'ffffffff';
    }
    $color_arr = str_split($color_code, 2);
    $opacity_dec = (hexdec($color_arr[0]) / 255) * 100 ;
    return $opacity_dec;
}
//print("<pre>".print_r($kml_file->xpath("//default:Placemark/not(LineMark"),true)."</pre>");

echo json_encode($kmlDetails);
