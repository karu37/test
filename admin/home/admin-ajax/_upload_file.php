<?
	$newwidth = $_REQUEST['width'];
	$newheight = $_REQUEST['height'];

	if (!$_FILES['file']['tmp_name']) return_die(false, null, '���ε� ���� �������ϴ�.');

	$upload_name = $_FILES['file']['tmp_name'];
	
	// ���� ����� �����ϴ� ��� �ش� ������� ���������ϰ� ó���ϱ�
	list($width, $height) = getimagesize($upload_name);
	if ($width && $height && $newwidth && $newheight && ($width != $newwidth || $height != $newheight)) {
		
		// ���ε� �� ���Ϸκ��� ��������� �̹��� ����
		$filename = $upload_name."_2";
		$canvas = imagecreatetruecolor($newwidth, $newheight);
		
		// painting ���� �ʴ� ���� �������� ó���ϱ�
		imagealphablending($canvas, true);
		imagesavealpha($canvas, true);		
		imagefill($canvas, 0, 0, 0x7fff0000);
		
		// source ���� �ε�
		$source = imagecreatefromstring(file_get_contents($upload_name));
		
		// $source �̹��������� Ratio�� ������ ����(x,y)�� width2, height2�� ���ϱ�
		$ratio_s = $height / $width;
		$ratio_t = $newheight / $newwidth;
		
		/*******************************************************************************		
		// ��� ũ�ӹ�� (target�� 0,0,nw,nh ��, source�� ������ ����)
		if ($ratio_s < $ratio_t) { // height�κ��� Ratio�� �´� source width ��� >> w : h = nw : nh >> w = (h * nw) / nh
			$width2 = ($height * $newwidth) / $newheight;	// src-width
			$start_x = ($width - $width2) / 2;				// src-x
			$height2 = $height;
			$start_y = 0;
		} else { 					// width�κ��� Ratio�� �´� source height ��� >> w : h = nw : nh >> h = (w * nh) / nw
			$height2 = ($width * $newheight) / $newwidth;	// src-height
			$start_y = ($height - $height2) / 2;			// src-y
			$width2 = $width;
			$start_x = 0;
		}
		imagecopyresampled($canvas, $source, 0, 0, $start_x, $start_y, $newwidth, $newheight, $width2, $height2);
		********************************************************************************/		
		// ���μ��� ���� �����ϸ� �̹��� ��� ǥ�� (source�� 0,0,w,h��, target ������ ����)
		if ($ratio_s < $ratio_t) {	// source height�� ���� >> w : h = nw : nh >> nh = (h * nw) / w
			$newheight2 = ($height * $newwidth) / $width;	// dst-height
			$dest_y = ($newheight - $newheight2) / 2;		// dst-y
			$newwidth2 = $newwidth;
			$dest_x = 0;
		} else {					// source width�� ���� >> w : h = nw : nh >> nw = (w * nh) / h
			$newwidth2 = ($width * $newheight) / $height;	// dst-width
			$dest_x = ($newwidth - $newwidth2) / 2;			// dst-x
			$newheight2 = $newheight;
			$dest_y = 0;
		}
		imagecopyresampled($canvas, $source, $dest_x, $dest_y, 0, 0, $newwidth2, $newheight2, $width, $height);
		/*******************************************************************************/
		
		imagepng($canvas, $filename);
		imagedestroy($canvas);

		// upload ���� ����
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