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
				$parsed_line = $line;

				/*
				 * PARSE {basepath}
				 */
				if(preg_match("/(\{basepath\})/",$parsed_line,$matches)){
					$basepath = get_template_directory();
					$parsed_line = preg_replace("/(\{basepath\})/",$basepath,$parsed_line);
					$parsed_line = preg_replace("/^\/\//","",$parsed_line);
					//Custom hook:
					$parsed_line = apply_filters("wbf/compiler/parser/line/basepath",$parsed_line,$line,$matches,$filepath,$inputFile);
				}

				/*
				 * PARSE {childbasepath}
				 */
				if(preg_match("/(\{childbasepath\})/",$parsed_line,$matches)){
					$childbasepath = get_stylesheet_directory();
					$parsed_line = preg_replace("/(\{childbasepath\})/",$childbasepath,$parsed_line);
					$parsed_line = preg_replace("/^\/\//","",$parsed_line);
					//Custom hook:
					$parsed_line = apply_filters("wbf/compiler/parser/line/childbasepath",$parsed_line,$line,$matches,$filepath,$inputFile);
				}

				/*
				 * PARSE {baseurl}
				 */
                if(preg_match("/(\{baseurl\})/",$parsed_line,$matches)){
                    $baseurl = get_template_directory_uri();
					$parsed_line = preg_replace("/(\{baseurl\})/",$baseurl,$parsed_line);
					$parsed_line = preg_replace("/^\/\//","",$parsed_line);
					//Custom hook:
					$parsed_line = apply_filters("wbf/compiler/parser/line/baseurl",$parsed_line,$line,$matches,$filepath,$inputFile);
                }

				/*
				 * PARSE {childbaseurl}
				 */
                if(preg_match("/(\{childbaseurl\})/",$parsed_line,$matches)){
                    $baseurl = get_stylesheet_directory_uri();
					$parsed_line = preg_replace("/(\{childbaseurl\})/",$baseurl,$parsed_line);
					$parsed_line = preg_replace("/^\/\//","",$parsed_line);
					//Custom hook:
					$parsed_line = apply_filters("wbf/compiler/parser/line/childbaseurl",$parsed_line,$line,$matches,$filepath,$inputFile);
                }

				/*
				 * PARSE {@import}
				 */
				if(preg_match("|\{@import '([a-zA-Z0-9\-/_.\{\}]+)'\}|",$parsed_line,$matches)){
					//Check the file existence
					$fileToImport = new \SplFileInfo($matches[1]);
					if($fileToImport->isFile() && $fileToImport->isReadable()){
						$parsed_line = "@import ".$matches[1];
					}else{
						$parsed_line = "//@import ".$matches[1];
					}
					//Custom hook:
					$parsed_line = apply_filters("wbf/compiler/parser/line/import",$parsed_line,$line,$matches,$filepath,$inputFile);
				}

				$parsed_line = apply_filters("wbf/compiler/parser/line",$line,$filepath,$inputFile); //Allow developers and module to hooks additional filters

				$tmpFileObj->fwrite($parsed_line);
			}
			$filepath = $tmpFile->getRealPath();
		}
	}

	return $filepath;
}