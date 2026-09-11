<?php
declare(strict_types=1);
if($argc<4||0!==($argc-2)%2)exit(2);
$outputDir=$argv[1];
for($i=2;$i<$argc;$i+=2){$sourcePath=$argv[$i];$filename=$argv[$i+1];$source=imagecreatefrompng($sourcePath);if(!$source)throw new RuntimeException('Cannot read '.$sourcePath);$canvas=imagecreatetruecolor(1440,960);imagecopyresampled($canvas,$source,0,0,0,0,1440,960,imagesx($source),imagesy($source));imageinterlace($canvas,true);$target=rtrim($outputDir,'/\\').DIRECTORY_SEPARATOR.$filename;if(!imagejpeg($canvas,$target,84))throw new RuntimeException('Cannot write '.$target);imagedestroy($canvas);imagedestroy($source);echo $filename."\t".filesize($target).PHP_EOL;}
