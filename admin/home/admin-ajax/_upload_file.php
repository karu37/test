<?
	$newwidth = $_REQUEST['width'];
	$newheight = $_REQUEST['height'];

	if (!$_FILES['file']['tmp_name']) return_die(false, null, '업로드 파일 오류립니다.');

	$upload_name = $_FILES['file']['tmp_name'];
	
	// 파일 사이즈가 존재하는 경우 해당 사이즈로 리사이즈하고 처리하기
	list($width, $height) = getimagesize($upload_name);
	if ($width && $height && $newwidth && $newheight && ($width != $newwidth || $height != $newheight)) {
		
		// 업로드 된 파일로부터 리사이즈된 이미지 추출
		$filename = $upload_name."_2";
		$canvas = imagecreatetruecolor($newwidth, $newheight);
		
		// painting 되지 않는 영역 투명으로 처리하기
		imagealphablending($canvas, true);
		imagesavealpha($canvas, true);		
		imagefill($canvas, 0, 0, 0x7fff0000);
		
		// source 파일 로딩
		$source = imagecreatefromstring(file_get_contents($upload_name));
		
		// $source 이미지에서의 Ratio를 유지한 시작(x,y)와 width2, height2를 구하기
		$ratio_s = $height / $width;
		$ratio_t = $newheight / $newwidth;
		
		/*******************************************************************************		
		// 가운데 크롭방식 (target은 0,0,nw,nh 로, source의 영역을 결정)
		if ($ratio_s < $ratio_t) { // height로부터 Ratio에 맞는 source width 계산 >> w : h = nw : nh >> w = (h * nw) / nh
			$width2 = ($height * $newwidth) / $newheight;	// src-width
			$start_x = ($width - $width2) / 2;				// src-x
			$height2 = $height;
			$start_y = 0;
		} else { 					// width로부터 Ratio에 맞는 source height 계산 >> w : h = nw : nh >> h = (w * nh) / nw
			$height2 = ($width * $newheight) / $newwidth;	// src-height
			$start_y = ($height - $height2) / 2;			// src-y
			$width2 = $width;
			$start_x = 0;
		}
		imagecopyresampled($canvas, $source, 0, 0, $start_x, $start_y, $newwidth, $newheight, $width2, $height2);
		********************************************************************************/		
		// 가로세로 비율 유지하며 이미지 모두 표시 (source는 0,0,w,h로, target 영역을 결정)
		if ($ratio_s < $ratio_t) {	// source height가 낮음 >> w : h = nw : nh >> nh = (h * nw) / w
			$newheight2 = ($height * $newwidth) / $width;	// dst-height
			$dest_y = ($newheight - $newheight2) / 2;		// dst-y
			$newwidth2 = $newwidth;
			$dest_x = 0;
		} else {					// source width가 좁음 >> w : h = nw : nh >> nw = (w * nh) / h
			$newwidth2 = ($width * $newheight) / $height;	// dst-width
			$dest_x = ($newwidth - $newwidth2) / 2;			// dst-x
			$newheight2 = $newheight;
			$dest_y = 0;
		}
		imagecopyresampled($canvas, $source, $dest_x, $dest_y, 0, 0, $newwidth2, $newheight2, $width, $height);
		/*******************************************************************************/
		
		imagepng($canvas, $filename);
		imagedestroy($canvas);

		// upload 파일 삭제
		unlink($upload_name);

	} else {
		
		$filename = $_FILES['file']['tmp_name'];	
		
	}

	$imagefile = file_get_contents($filename);
	$db_imagefile = mysql_real_escape_string($imagefile);
	
	$sql = "INSERT image_data_t (data) VALUES ('{$db_imagefile}')";
	mysql_execute($sql, $conn);
	$id = mysql_insert_id();
	
	// delete uploaded temporary file
	unlink($filename);
	
	$url = "https://app.autoring.kr/home/ajax/image.php?file=" . $id;
	
	$db_url = mysql_real_escape_string($url);
	$sql = "UPDATE image_data_t SET url = '{$db_url}'";
	mysql_execute($sql, $conn);
	
	$ar_data['id'] = $id;
	$ar_data['url'] = $url;
	
	return_die(true, $ar_data);

?>