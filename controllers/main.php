<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
* @class: Main
* @version: 7.2 
* @author: martin peter's
* @php: 7.4
* @revision: 2025-05-16
* @licence MIT
*/
class Main extends Core\Controller
{	
	function __construct()
	{
		parent::__construct(DEFAULTDATABASE,'php','main');	
		if(!isset($_SESSION['loggedin']))
		{
			header('Location:'.WEBROOT.'login');
			exit();
		}
	}
	function index()
	{
		parent::index();
	}	

	// api/data/:user.base.tX.lY.cZ

function getSmartObject($user, $tableId, $rowId, $colId) {
    $filename = "/base/{$user}.php";
    if (!file_exists($filename)) return errorJson("User file not found");

    include $filename; // loads $data
    if (!isset($data[$tableId][$rowId][$colId])) return errorJson("Cell not found");

    $value = $data[$tableId][$rowId][$colId];
    $render = renderSmart($value);

    return json_encode([
        "type" => detectType($value),
        "value" => $value,
        "render" => $render,
        "meta" => [
            "table" => $data[$tableId][0][1] ?? "unknown",
            "id" => $data[$tableId][$rowId][1] ?? null,
            "field" => $data[$tableId][0][$colId] ?? "unknown",
            "author" => $user,
            "timestamp" => date('c')
        ]
    ]);
}

function renderSmart($value) {
    $base = "/uploads/";

    if (preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $value)) {
        return '<img src="' . $base . $value . '" alt="image" style="max-height:80px; display:block; margin:auto;" />';
    }

    if (preg_match('/\.(mp4|webm)$/i', $value)) {
        return '<video src="' . $base . $value . '" controls style="max-width:100%; height:auto; display:block;"></video>';
    }

    if (preg_match('/\.(mp3|ogg)$/i', $value)) {
        return '<audio src="' . $base . $value . '" controls style="width:100%;"></audio>';
    }

    if (filter_var($value, FILTER_VALIDATE_URL)) {
        return '<a href="' . $value . '" target="_blank" style="text-decoration:underline; color:#3366cc;">' . $value . '</a>';
    }

    if (strpos($value, '@') !== false) {
        return '<a href="mailto:' . $value . '" style="color:#3366cc;">' . $value . '</a>';
    }

    return htmlspecialchars($value);
}

function detectType($value) {
    if (preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $value)) return "image";
    if (preg_match('/\.(mp4|webm)$/i', $value)) return "video";
    if (preg_match('/\.(mp3|ogg)$/i', $value)) return "audio";
    if (filter_var($value, FILTER_VALIDATE_URL)) return "link";
    if (strpos($value, '@') !== false) return "email";
    return "text";
}

function errorJson($msg) {
    return json_encode(["error" => $msg]);
}

}
?>