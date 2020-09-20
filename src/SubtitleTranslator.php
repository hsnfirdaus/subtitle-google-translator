<?php
namespace Hsnfirdaus;
/**
 * The main class
 */
class SubtitleTranslator
{
	
	private function doTranslate($text,$source_lang,$target_lang)
	{
		$url = "https://translate.google.com/translate_a/single?client=at&dt=t&dt=ld&dt=qca&dt=rm&dt=bd&dj=1&hl=id-ID&ie=UTF-8&oe=UTF-8&inputm=2&otf=2&iid=1dd3b944-fa62-4b55-b330-74909a99969e";
		$post_data = array(
			'sl'=>$source_lang,
			'tl'=>$target_lang,
			'q'=>$text
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		if (isset($_SERVER['REMOTE_ADDR'])) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Forwarded-For: '.$_SERVER['REMOTE_ADDR']));
		}
		curl_setopt($ch, CURLOPT_USERAGENT, 'AndroidTranslate/5.3.0.RC02.130475354-53000263 5.1 phone TRANSLATE_OPM5_TEST_1');
		$response = curl_exec($ch);
		curl_close($ch);
		$json_decoded = json_decode($response);
		if ($json_decoded->sentences) {
			$return='';
			foreach ($json_decoded->sentences as $sentence) {
				$return.=$sentence->trans;
			}
			return $return;
		}else{
			$this->doTranslate($text,$source_lang,$target_lang);
		}
	}
	private function parseSrt($raw)
	{
		preg_match_all('#([0-9]+)(\r\n|\r|\n)([0-9: \-,>]*?)(\r\n|\r|\n)(.*?)(\r\n\r\n|\r\r|\n\n)#s', $raw, $match);
		$result=[];
		for ($i=0; $i < count($match[1]); $i++) { 
			$text=preg_replace('#(\r\n|\r|\n)#s', '<br/>', $match[5][$i]);
			if (!empty($text)) {
				$array=[
					'index'=>$match[1][$i],
					'timeline'=>$match[3][$i],
					'text'=>$text
				];
				$result[]=$array;
			}
		}
		return $result;
	}
	private function chunkedTranslate($parsed_subtitle,$source_lang='en',$target_lang='id')
	{
		$length=0;
		$index=0;
		$chunked=[0=>''];
		foreach ($parsed_subtitle as $subtitle) {
			if (strlen(@$chunked[$index]."\n".$subtitle['text'])<5000) {
				$chunked[$index].="\n".$subtitle['text'];
			}else{
				$index++;
				$chunked[$index]=$subtitle['text'];
			}
		}
		$translated=[];
		foreach ($chunked as $text) {
			$doTranslate=$this->doTranslate(trim($text),$source_lang,$target_lang);
			foreach (explode("\n", $doTranslate) as $sentence) {
				$translated[]=$sentence;
			}
		}
		$result=[];
		for ($i=0; $i < count($parsed_subtitle); $i++) { 
			$result[]=[
				'index'=>$parsed_subtitle[$i]['index'],
				'timeline'=>$parsed_subtitle[$i]['timeline'],
				'text'=>$translated[$i]
			];
		}
		return $result;
	}
	private function formatSrt($parsed_subtitle)
	{
		$raw='';
		foreach ($parsed_subtitle as $subtitle) {
			$text=preg_replace('#\s*<\s*br\s*/\s*>\s*#s', "\n", trim($subtitle['text'],' \n\t\r'));
			$text=preg_replace_callback('#&\s*(amp|lt|gt|quot)\s*;#', function($m){
				return html_entity_decode('&'.$m[1].';');
			}, $text);
			$text=preg_replace('/\s*=\s*"\s*(#)?\s*/s', '="$1', $text);
			$raw.=$subtitle['index']."\n".$subtitle['timeline']."\n".$text."\n\n";
		}
		return $raw;
	}
	public function fromRaw($raw)
	{
		$parse=$this->parseSrt($raw);
		$translated=$this->chunkedTranslate($parse);
		$output_raw=$this->formatSrt($translated);
		return $output_raw;
	}
	public function fromFile($filepath)
	{
		$raw=file_get_contents($filepath);
		$explode=explode('.', $filepath);
		$ext=end($explode);
		$output_raw=$this->fromRaw($raw);
		return $output_raw;
	}
}