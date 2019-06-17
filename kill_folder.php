<?php

/**
 * AVONTURE Christophe - https://www.avonture.be.
 *
 * Date : 16/10/2015
 *
 * Kill a folder : put this script inside a folder (f.i. /cache) and every files inside
 * that folder except .htaccess and index.html will be removed.
 * All subfolders will be removed too
 */

define('DEBUG', false);
define('DS', DIRECTORY_SEPARATOR);

/**
 * A few helping functions.
 */
class Helpers
{
    /**
     * Generic function for adding a css in the HTML response.
     *
     * @param type $localfile
     * @param type $weblocation
     *
     * @return string
     */
    public static function addStylesheet($localfile, $weblocation = '')
    {
        $return='';

        if (is_file(dirname(__DIR__) . DS . 'assets' . DS . 'css' . DS . $localfile)) {
            $return='<link href="../assets/css/' . $localfile . '" rel="stylesheet" />';
        } else {
            if ('' != $weblocation) {
                $return='<link href="' . $weblocation . '" rel="stylesheet" />';
            }
        }

        return $return;
    }

/**
 * Generic function for adding a js in the HTML response.
 *
 * @param type  $localfile
 * @param type  $weblocation
 * @param mixed $defer
 *
 * @return string
     */
    public static function addJavascript($localfile, $weblocation = '', $defer = false)
    {
        $return='';

        if (is_file(dirname(__DIR__) . DS . 'assets' . DS . 'js' . DS . $localfile)) {
            $return='<script ' . (true == $defer ? 'defer="defer" ' : '') . 'type="text/javascript" src="../assets/js/' . $localfile . '"></script>';
        } else {
            if ('' != $weblocation) {
                $return='<script ' . (true == $defer ? 'defer="defer" ' : '') . 'type="text/javascript" src="' . $weblocation . '"></script>';
            }
        }

        return $return;
    }
}

function doIt($folder)
{
    // Be sure to have the folder separator
    $folder=rtrim($folder, DS) . DS;

    error_reporting(E_ALL);
    ini_set('display_errors', 'On');

    $return= '<h3>Suppression de tous les fichiers et sous-dossier de ' . $folder . '</h3>';

    $it    = new RecursiveDirectoryIterator($folder);
    $wCount=0;
    foreach (new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST) as $file) {
        if (($file->isDir()) && (!in_array($file->getFilename(), ['.', '..']))) {
            $return .= '<h4>Suppression du dossier ' . $file->getPathname() . '</h4>';
            @rmdir($file->getPathname());
        } else {
            if (($file->isFile()) && (__FILE__ != $file->getPathname())) {
                if (!(in_array($file->getPathname(), [$folder . '.htaccess', $folder . 'index.html', $folder . 'index.php']))) {
                    $return .= '<p>Suppression du fichier ' . $file->getPathname() . '</p>';
                    @unlink($file->getPathname());
                    ++$wCount;
                } else {
                    $return .= '<p class="text-success">Le fichier ' . $file->getPathname() . ' n\'a pas &eacute;t&eacute; supprim&eacute;.</p>';
                }
            }
        }
    }

    $return .= '<hr/><h2 class="text-success">Nettoyage termin&eacute;</h2>';
    $return .= '<p>' . $wCount . ' fichiers supprimés</p>';

    return $return;
}

// -------------------------------------------------
// ENTRY POINT
// -------------------------------------------------

// By default, scan the current directory.  In Expert mode, allow to use a session
// to store the name of the folder

$var=(DEBUG === true ? $_GET : $_POST);

// Folder to clean
$folder=dirname(__FILE__);

if (isset($var['task'])) {
    $task=$var['task'];
    if ('doIt' == $task) {
        $return = doIt($folder);
        echo $return;
        die();
    } elseif ('killMe' == $task) {
        chmod(__FILE__, octdec('644'));
        unlink(__FILE__);
        echo '<p class="text-success">Le script ' . __FILE__ . ' a &eacute;t&eacute; supprim&eacute; du serveur avec succ&egrave;s</p>';
        die();
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
   <head>
      <meta charset="utf-8"/>
      <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
      <meta name="robots" content="noindex, nofollow" />
      <meta name="author" content="Christophe Avonture" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" />
      <?php
         echo Helpers::addStylesheet('bootstrap.min.css', '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css');
      ?>
      <style type="text/css">
         .ajax_loading {
             display:inline-block;
             width:32px;
             height:32px;
             margin-right:20px;
         }
      </style>
   </head>
   <body>

      <div class="container">
         <div class="page-header"><h1>Clean folders</h1></div>
         <div class="container">
            <p style="font-size:2em;" class="text-danger">Ce script va supprimer tous les fichiers contenus dans le dossier <strong><?php echo $folder; ?></strong> &agrave; l'exception des fichiers /.htaccess et /index.(html|php).</p>
            <br/>
            <form>
               <button type="button" id="CleanFolder" class="btn btn-primary">Nettoyer</button>
               &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
               <button type="button" id="KillMe" class="btn btn-danger">Supprimer ce script</button>
            </form>
            <hr/>
            <div id="Result">&nbsp;</div>
         </div>
       </div>

        <?php
         echo Helpers::addJavascript('jquery.min.js', '//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js');
         echo Helpers::addJavascript('bootstrap.min.js', '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js');
        ?>

      <script type="text/javascript" defer="defer">

         $('#CleanFolder').click(function(e)  {

            e.stopImmediatePropagation();

            var $data = new Object;
            $data.task = "doIt"

            $.ajax({
               beforeSend: function() {
                  $('#Result').html('<div><span class="ajax_loading">&nbsp;</span><span style="font-style:italic;font-size:1.5em;">Un peu de patience svp...</span></div>');
                  $('#CleanFolder').prop("disabled", true);
                  $('#KillMe').prop("disabled", true);
               },
               async:true,
               type:"<?php echo DEBUG === true ? 'GET' : 'POST'; ?>",
               url: "<?php echo basename(__FILE__); ?>",
               data:$data,
               datatype:"html",
               success: function (data) {
                  $('#Result').html(data);
                  $('#CleanFolder').prop("disabled", false);
                  $('#KillMe').prop("disabled", false);
               },
               error: function(Request, textStatus, errorThrown) {
                  $('#CleanFolder').prop("disabled", false);
                  $('#KillMe').prop("disabled", false);
                  // Display an error message to inform the user about the problem
                  var $msg = '<div class="bg-danger text-danger img-rounded" style="margin-top:25px;padding:10px;">';
                  $msg = $msg + '<strong>An error has occured :</strong><br/>';
                  $msg = $msg + 'Internal status: '+textStatus+'<br/>';
                  $msg = $msg + 'HTTP Status: '+Request.status+' ('+Request.statusText+')<br/>';
                  $msg = $msg + 'XHR ReadyState: ' + Request.readyState + '<br/>';
                  $msg = $msg + 'Raw server response:<br/>'+Request.responseText+'<br/>';
                  $url='<?php echo basename(__FILE__); ?>?'+$data.toString();
                  $msg = $msg + 'URL that has returned the error : <a target="_blank" href="'+$url+'">'+$url+'</a><br/><br/>';
                  $msg = $msg + '</div>';
                  $('#Result').html($msg);
               }
            });
         });

         $('#KillMe').click(function(e)  {
            e.stopImmediatePropagation();
            $.ajax({
               beforeSend: function() {
                  $('#Result').empty();
                  $('#CleanFolder').prop("disabled", true);
                  $('#KillMe').prop("disabled", true);
               },
               async:true,
               type:"<?php echo DEBUG === true ? 'GET' : 'POST'; ?>",
               url: "<?php echo basename(__FILE__); ?>",
               data:"task=killMe",
               datatype:"html",
               success: function (data) {
                  $('#Result').html(data);
               }
            });
         });

      </script>

   </body>

</html>