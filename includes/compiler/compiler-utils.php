<?php

namespace WBF\includes\compiler;

/**
 * Generate a temp file parsing commented include tags in the $filepath less file.
 * Must be called during compile() of various compilers (@see Less_Compiler.php)
 *
 * @param $filepath (the absolute path to the file to parse (usually waboot.less or waboot-child.less)
 *
 * @return string filepath to temp file
 *
 * @since 0.7.0
 */
function parse_input_file($filepath){
	$inputFile = new \SplFileInfo($filepath);
	if($inputFile->isReadable()){
		$inputFileObj = $inputFile->openFile();
		$tmpFile = new \SplFileInfo($inputFile->getPath()."/tmp_".$inputFile->getFilename());
		$tmpFileObj = $tmpFile->openFile("w+");
		if($tmpFileObj->isWritable()){
			while (!$inputFileObj->eof()) {
				$line = $inputFileObj->fgets();

				/*
				 * PARSE {baseurl}
				 */
                if(preg_match("/(\{baseurl\})/",$line,$matches)){
                    $baseurl = get_template_directory_uri();
                    $line = preg_replace("/(\{baseurl\})/",$baseurl,$line);
                    $line = preg_replace("/^\/\//","",$line);
                }

				/*
				 * PARSE {childbaseurl}
				 */
                if(preg_match("/(\{childbaseurl\})/",$line,$matches)){
                    $baseurl = get_stylesheet_directory_uri();
                    $line = preg_replace("/(\{childbaseurl\})/",$baseurl,$line);
                    $line = preg_replace("/^\/\//","",$line);
                }

				$line = apply_filters("wbf/compiler/parser/line",$line,$filepath,$inputFile); //Allow developers and module to hooks additional filters

				$tmpFileObj->fwrite($line);
			}
			$filepath = $tmpFile->getRealPath();
		}
	}

	return $filepath;
}