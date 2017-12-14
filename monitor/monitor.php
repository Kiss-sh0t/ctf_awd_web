<?php
ignore_user_abort(true);
set_time_limit(0);

#config
define('sleep', 500);
define('monitorPath',getcwd());//
define('backupPath', '/tmp/.back1p/');//备份目录
define('excludePathNoExec', 0);//是否设置exclude目录不可执行
$excludePath = array(getcwd().'/upload', getcwd().'/logs');//不进行备份和监控的目录
#config end

#删除
function deldir($dir) {
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
        if($file != "." && $file!="..") {
        $fullpath = $dir."/".$file;
        if(!is_dir($fullpath)) {
            unlink($fullpath);
        } else {
            deldir($fullpath);
        }
        }
    }
    closedir($dh);
}

#文件夹备份
function MD5_DIR($dir)
{
    if (!is_dir($dir))
    {
        return false;
    }

    $filemd5s = array();
    $d = dir($dir);

    while (false !== ($entry = $d->read()))
    {
        if ($entry != '.' && $entry != '..')
        {
             if (is_dir($dir.'/'.$entry))
             {
                 $filemd5s[] = MD5_DIR($dir.'/'.$entry);
             }
             else
             {
                 $filemd5s[] = md5_file($dir.'/'.$entry);
             }
         }
    }
    $d->close();
    return md5(implode('', $filemd5s));
}


$fileArr = array();
//file hash init
function fileHash($excludePath){
  $path = monitorPath;
  $file = scandir($path);
  global $fileArr;
  foreach($file as $k=>$v){
    if (!strcmp($v,'.')||!strcmp($v,'..'))continue;#
    if (in_array(monitorPath.'/'.$v, $excludePath))continue;#exclude
    if(is_dir($v))#文件夹
    {
      $fileArr[$v]=MD5_DIR($v);
    }else{#文件
      $fileArr[$v] = md5_file(monitorPath.'/'.$v);
    }
  }
}

/*file monitor */
function monitor($excludePath){
  global $fileArr;
  do{
    $path = monitorPath;
    $file = scandir($path);
    foreach($file as $k=>$v){
      if (!strcmp($v,'.')||!strcmp($v,'..'))continue;
      if (in_array(monitorPath.'/'.$v, $excludePath))continue;#excludepath
      #caculate
      if(is_dir($v)){
        $md5flie=MD5_DIR($v);
      }else{
        $md5flie=md5_file($v);
      }

      if(strcmp(@$fileArr[$v],$md5flie)!=0){
        //file changed!
        //back from backfile
        if(is_dir(backupPath.$v)){
          if(file_exists(backupPath.$v)){
            deldir(monitorPath.'/'.$v);//??
            recurse_copy(backupPath.$v, monitorPath.'/'.$v);#被监控文件夹修改且存在文件夹
          }
          else{
            deldir(monitorPath.'/'.$v, monitorPath.'/xxx');#监控文件夹修改且不存在文件夹
          }
        }
        else{
          //single file
          if(file_exists(backupPath.$v))
          {
            copy(backupPath.$v, monitorPath.'/'.$v);
          }
          else{
            if(file_exists(monitorPath.'/'.$v)){unlink(monitorPath.'/'.$v);}
          }
        }
      }
    }
    #find if delete
    $path = monitorPath;
    $file = scandir($path);
    foreach($fileArr as $k=>$v){
      if (!strcmp($k,'.')||!strcmp($k,'..'))continue;#
      if (in_array(monitorPath.'/'.$k, $excludePath))continue;#exclude
      if (!in_array($k, $file)){
        #判断
        if(is_dir(backupPath.$k)){
            recurse_copy(backupPath.$k, monitorPath.'/'.$k);#被监控文件夹修改且存在文件夹
        }
        else{
            copy(backupPath.$k, monitorPath.'/'.$k);
        }
      }
    }

    usleep(sleep*2);
  }while (true);
}

//for bak
function recurse_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' ) && ( $file != '.back1p' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
              //add bak to file ext
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}
//
function backup(){
  //mkdir
  if(!file_exists(backupPath)){
      //mkdir(backupPath);
      recurse_copy(monitorPath, backupPath);
      chmod(backupPath, 0755);
  }
  return 0;
}

//no exec in uploadfile
if(excludePathNoExec){
  chmod(excludePath, 0555);
}

backup();
fileHash($excludePath);
//var_dump($fileArr);
monitor($excludePath);
?>
