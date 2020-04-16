<?php 
/*|---------------------------------------------
  |     YDrive - Google Drive Video Proxy
  |  ©2017-2020 Yotsubal - YotsubalRU PROJECT 
  |---------------------------------------------
  */

namespace App\Lib;
  
# YDRIVE_DEFAULT_RESOLUTION
# Possible Input : 360 (default) | 480 | 720 | 1080 | ld | sd | hd | fhd 
define('YDRIVE_DEFAULT_RESOLUTION', 360);
# YDRIVE_DEFAULT_ID
# Possible Input : Google Drive ID If user input is null/404/non-video
define('YDRIVE_DEFAULT_ID', "");


class ProxyPlayer {
    # Dont Edit this class variable
    private $gdid;
    private $v_code;
    private $v_res;
    private $cookie;
    private $link;
    private $title;
    private $downloadThis;
    function __construct($d = YDRIVE_DEFAULT_ID){
        is_dir("cache") || mkdir("cache",0777);
        $this->gdid = $d;
        $this->cache = new Cache();
    }
    private function _getCode($in = 360){
        switch ($in){
            case "shd":
            case "360":
                $this->v_code = 18;
                $this->v_res = 360;
                break;
            case "shd":
            case "480":
                $this->v_code = 59;
                $this->v_res = 480;
                break;
            case "hd":
            case "720":
                $this->v_code = 22;
                $this->v_res = 720;
                break;
            case "fhd":
            case "1080":
                $this->v_code = 37;
                $this->v_res = 1080;
                break;
            default:
                $this->v_code = 18;
                $this->v_res = 360;
                break;
            
        }
    }
    private function _getCache($a,$b = null){
        $cachekey = "cache/"."PLAY_".md5($a);
        $cachetime = 3600 * 4;
            if($b){
                $this->cache->store($cachekey, $b, $cachetime);
                return $b;
            }
            if($value = $this->cache->retrieve($cachekey)) {
                return $value;
            }
            return;  
    }
    private function _setHeader($cookies){
    	if (!empty($cookies)) {
    		$headers = array('Cookie: ' . $cookies);
    	}
    	else {
    		$headers = array();
    	}
    	if (isset($_SERVER['HTTP_RANGE'])) {
    		$headers[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
    	}
    	return $headers;
    }
    private function _getContent($url,$cookie = false){
        $ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_HEADER, $cookie);
    	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    	$result = curl_exec($ch);
    	$info = curl_getinfo($ch);
    	if ($cookie === true) {
    		$header = substr($result, 0, $info['header_size']);
    		$result = substr($result, $info['header_size']);
    		preg_match_all('/^Set-Cookie:\\s*([^=]+)=([^;]+)/mi', $header, $cookie);
    
    		foreach ($cookie[1] as $i => $val) {
    			$cookies[] = $val . '=' . trim($cookie[2][$i], ' ' . "\n\r\t" . '' . "\0" . '' . "\xb");
    		}
    	}
    	return array("cookie"=> $cookies, "source" => $result);
    }
    private function _getVideoData(){
        if(!$this->gdid) die("Please set Google Drive ID");
        $url = 'https://drive.google.com/e/get_video_info?docid=' . $this->gdid ;
        $saus = "https://drive.google.com/file/d/".$this->gdid;
        $checker = $this->_getCache($this->gdid);
        if($checker){
        $gd = json_decode($checker,1);
        }else{
        $gd = $this->_getContent($url,true);
        $this->_getCache($this->gdid,json_encode($gd));
        }
        $p = $gd["source"];
        $p = urldecode(explode("&",explode("&fmt_stream_map=",$p)[1])[0]);
        $p = explode(",",$p);
        foreach($p as $w){
            $r = explode("|",$w);
            $link[$r[0]] = $r[1];
        }
        $this->title =  pathinfo(urldecode(explode("&",explode("&title=",$gd["source"])[1])[0]), PATHINFO_FILENAME);
        $this->cookie = implode('; ', $gd["cookie"]);
        $this->link = $link;
    }
    public function setID($d){
        $this->gdid = $d;
    }
    public function setResolution($res = YDRIVE_DEFAULT_RESOLUTION){
        $this->_getCode($res);
    }
    public function getResolution(){
        $this->_getVideoData();
        $c = [];
        foreach ($this->link as $a => $b){
            switch($a){
                case 18:
                    $c['sd'] = "360";
                break;
                case 59:
                    $c['mhd'] = "480";
                break;
                case 22:
                    $c['hd'] = "720";
                break;
                case 37:
                    $c['fhd'] = "1080";
                break;
            }
            
        }
        return $c;
    }
    public function setDownload($s = true){
        $this->downloadThis = $s;
    }
    public function stream(){
        if(!$this->v_code) $this->setResolution();
        $this->_getVideoData();
        $options = array(
		'http' => array('header' => $this->_setHeader($this->cookie))
        );
    	stream_context_set_default($options);
    	if(!$this->link[$this->v_code]) die("404");
        $headers = @json_decode($this->_getCache(md5($this->gdid.$this->v_code)),1);
        $headers = $headers ? $headers : [];
    	if(!count($headers)){
            $headers =  get_headers($this->link[$this->v_code], true);
        	if (isset($headers['Location'])) {
                if (is_array($headers['Location'])) {
                    $headers['Location'] = end($headers['Location']);
        		}
        		$this->link[$this->v_code] = $headers['Location'];
        		$headers = get_headers($this->link[$this->v_code], true);
        		$this->_getCache(md5($this->gdid.$this->v_code),json_encode($headers));
        	}
    	}
    	
    	$status_code = substr($headers[0], 9, 3);
        $source["link"] = $this->link[$this->v_code];
        if(!$source["link"]) die("404");
    	header($headers[0]);
    	# NOTE : Please dont Remove this Line
    	header('Developed-By: Yotsubal~');
            
    	if (http_response_code() != '403') {
    		if ($this->downloadThis) {
    			header('Pragma: public');
    			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    			header('Content-Disposition: attachment; filename="'.$this->title.' [' . $this->v_res . 'p].mp4"');
    		}
    		if (isset($headers['Content-Type'])) {
    			header('Content-Type: ' . $headers['Content-Type']);
    		}
    		if (isset($headers['Content-Length'])) {
    			header('Content-Length: ' . $headers['Content-Length']);
    		}
    		if (isset($headers['Accept-Ranges'])) {
    			header('Accept-Ranges: ' . $headers['Accept-Ranges']);
    		}
    		if (isset($headers['Content-Range'])) {
    			header('Content-Range: ' . $headers['Content-Range']);
    		}
    		$fp = fopen($source['link'], 'rb');
    		while (!feof($fp)) {
    			echo fread($fp, 1024 * 1024 * 7);
    			flush();
    			ob_flush();
    		}
            
    		fclose($fp);
    	}
    	else {
    	    die("404");
    	}
    }
}