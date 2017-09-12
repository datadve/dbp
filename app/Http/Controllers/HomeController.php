<?php

namespace App\Http\Controllers;

use App\Models\Country\Country;
use App\Models\Language\Alphabet;
use App\Models\Language\Language;
use App\Models\Organization\Organization;
use App\Models\Bible\Bible;
use App\Helpers\AWS\Bucket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\Types\Resource;
use Parsedown;
class HomeController extends APIController
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
	    $user = \Auth::user();
        return view('home',compact('user'));
    }

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function welcome()
	{
		$count['languages'] = Language::count();
		$count['countries'] = Country::count();
		$count['alphabets'] = Alphabet::count();
		$count['organizations'] = Organization::count();
		$count['bibles'] = Bible::count();

		return view('welcome',compact('count'));
	}

	public function versions()
	{
		return $this->reply(["versions" => [2,4]]);
	}

	public function versionLatest()
	{
		$swagger = json_decode(file_get_contents(public_path('swagger.json')));
		return $this->reply([ "Version" => $swagger->info->version ]);
	}

	public function versionReplyFormats()
	{
		$versionReplies = [
			"2" => ["json", "jsonp", "html"],
			"4" => ["json", "jsonp", "xml", "html"]
		];
		return $this->reply($versionReplies[$this->v]);
	}

	public function libraryAsset()
	{
		return $this->reply(json_decode(file_get_contents(public_path('static/library_asset.json'))));
	}

	public function signedUrl()
	{
		$filename = $_GET['filename'] ?? "";
		$signer = $_GET['signer'] ?? 's3_fcbh';
		$bucket = $_GET['bucket'] ?? "dbp_dev";
		$expiry = $_GET['expiry'] ?? 5;
		return Bucket::signedUrl($filename,$signer,$bucket,$expiry);
	}
}
