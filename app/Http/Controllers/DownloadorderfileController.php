<?php

class DownloadorderfileController extends BaseController 
{
	public function download($file){
		
		if(!empty($file)){
			$filename =  DB::table('order_files')->where('download_linkname', $file)->pluck('order_file');
			$fname = $filename.'.txt.zip';

			$docRoot = '/var/www/esealCentral/eSealcentral/app/orderFiles/';
			$yourfile = $docRoot.$fname;

		    $file_name = basename($yourfile);

		    header("Content-Type: application/zip");
		    header("Content-Disposition: attachment; filename=$file_name");
		    header("Content-Length: " . filesize($yourfile));

		    readfile($yourfile);

		}
	}
}