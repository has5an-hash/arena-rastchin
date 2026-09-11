<?php
declare(strict_types=1);

if(!extension_loaded('gd'))throw new RuntimeException('GD extension is required.');

$generated='C:\\Users\\m\\.codex\\generated_images\\01a07ad9-f359-72d2-9dd5-4b0604c075fa';
$sources=[
    'nails'=>'exec-b61d27d7-a9e5-428c-94fa-b60b6434052f.png',
    'clinic'=>'exec-da917e07-4738-446b-b9e6-690743f69500.png',
    'hair'=>'exec-e0e435f7-1162-4ab4-ba96-d849d64e0ae8.png',
    'spa'=>'exec-4d5d39b3-b5eb-4104-89f2-0d79272bb476.png',
    'lashes'=>'exec-bbc8e85a-c06a-4d7e-8c0c-a93ff9acd64c.png',
    'makeup'=>'exec-d6755cbe-220d-4229-8354-8bacb8cf3c43.png',
    'barber'=>'exec-9a92d5b0-c57f-43e2-937d-9a8b55a68b8e.png',
];

$themeRoots=[];
foreach(['nail','clinic','spa','hair','makeup','lashes','barber'] as $folder){
    $themeRoots[]=dirname(__DIR__).DIRECTORY_SEPARATOR.'demoes'.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.'wp-content'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.'beautia'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'img';
}

foreach($sources as $demo=>$sourceName){
    $sourcePath=$generated.DIRECTORY_SEPARATOR.$sourceName;
    if(!is_file($sourcePath))throw new RuntimeException('Missing '.$sourcePath);
    $source=imagecreatefrompng($sourcePath);
    if(!$source)throw new RuntimeException('Cannot read '.$sourcePath);
    $width=imagesx($source);$height=imagesy($source);$panelWidth=(int)floor($width/3);
    $cropHeight=min($height,(int)round($panelWidth*4/3));
    $topOffset=$demo==='makeup'?.15:.07;
    $cropY=max(0,min($height-$cropHeight,(int)round($height*$topOffset)));
    foreach([1,2,3] as $index){
        $target=imagecreatetruecolor(720,960);
        imagecopyresampled($target,$source,0,0,($index-1)*$panelWidth,$cropY,720,960,$panelWidth,$cropHeight);
        imageinterlace($target,true);
        $filename='team-'.$demo.'-'.$index.'.jpg';
        foreach($themeRoots as $root){
            $targetPath=$root.DIRECTORY_SEPARATOR.$filename;
            if(!imagejpeg($target,$targetPath,84))throw new RuntimeException('Cannot write '.$targetPath);
        }
        echo $filename."\t".filesize($themeRoots[0].DIRECTORY_SEPARATOR.$filename).PHP_EOL;
        imagedestroy($target);
    }
    imagedestroy($source);
}
