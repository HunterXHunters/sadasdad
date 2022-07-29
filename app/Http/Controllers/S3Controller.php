<?php
ini_set('allow_url_fopen',1);
ini_set('memory_limit', '-1');
set_time_limit(0);
class S3Controller extends BaseController
{
    public function load_customers($dmy,$filename){
    	if(file_exists(public_path().'/'.$dmy.'/'.$filename)){
    		readfile(public_path().'/'.$dmy.'/'.$filename);
    	} else {
	    	$s3 = new s3\S3();
			$s3->getFileUrl($dmy.'/'.$filename,'customerThumbnail');	
    	}		
    } 
    public function load_products($dmy,$filename){
    	if(file_exists(public_path().'/'.$dmy.'/'.$filename)){
    		readfile(public_path().'/'.$dmy.'/'.$filename);
    	} else {
	    	$s3 = new s3\S3();
			$s3->getFileUrl($dmy.'/'.$filename,'productThumbnail');	
    	}		
    }
    public function downloadQrPdf($dmy,$filename){
        if(file_exists(public_path().'/'.$dmy.'/'.$filename)){
            readfile(public_path().'/'.$dmy.'/'.$filename);
        } else {
	    	$s3 = new s3\S3();
			$s3->getFileUrl($dmy.'/'.$filename,'download_qrpdf');	
    	}		
    }
    public function upload_po_file($dmy,$filename){
        if(file_exists(public_path().'/'.$dmy.'/'.$filename)){
            readfile(public_path().'/'.$dmy.'/'.$filename);
        } else {
            $s3 = new s3\S3();
            $s3->getFileUrl($dmy.'/'.$filename,'upload_po_file');   
        }       
    }
}