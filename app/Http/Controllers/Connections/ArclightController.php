<?php

namespace App\Http\Controllers\Connections;

use App\Http\Controllers\APIController;
use App\Models\Language\Language;
use App\Transformers\ArclightTransformer;
use Spatie\Fractalistic\ArraySerializer;

class ArclightController extends APIController
{

    protected $api_key;
    protected $base_url;

    /**
     * ArclightController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->api_key  = config('services.arclight.key');
        $this->base_url = 'https://api.arclight.org/v2/';
    }

    public function volumes($language = null)
    {
        $language = Language::where('iso',$language)->first();
        return \Cache::remember('media-languages', now()->addWeek(), function () use ($language) {
            $current_time = now();
            $languages = collect($this->fetch('media-languages')->mediaLanguages)->pluck('languageId', 'iso3')->toArray();

            if($language && isset($languages[$language->iso])) {
                $languages = [$language->iso => $languages[$language->iso]];
            }

            $language_names = Language::whereIn('iso', array_keys($languages))->get()->pluck('name','iso');

            foreach ($languages as $iso => $arclight_language_id) {
                $dam_id = strtoupper($iso).'JFVS2DV';
                if(!isset($language_names[$iso])) {
                    continue;
                }
                $jesusFilms[] = [
                    'dam_id'                  => $dam_id,
                    'fcbh_id'                 => $dam_id,
                    'volume_name'             => '',
                    'status'                  => 'live',
                    'dbp_agreement'           => 'true',
                    'expiration'              => '0000-00-00',
                    'language_code'           => strtoupper($iso),
                    'language_name'           => $language_names[$iso],
                    'language_english'        => $language_names[$iso],
                    'language_iso'            => $iso,
                    'language_iso_2B'         => '',
                    'language_iso_2T'         => '',
                    'language_iso_1'          => '',
                    'language_iso_name'       => $language_names[$iso],
                    'language_family_code'    => strtoupper($iso),
                    'language_family_name'    => $language_names[$iso],
                    'language_family_english' => $language_names[$iso],
                    'language_family_iso'     => $iso,
                    'language_family_iso_2B'  => '',
                    'language_family_iso_2T'  => '',
                    'language_family_iso_1'   => '',
                    'version_code'            => 'JFV',
                    'version_name'            => 'Jesus Film Video',
                    'version_english'         => 'Jesus Film Video',
                    'collection_code'         => 'AL',
                    'rich'                    => '0',
                    'collection_name'         => '',
                    'updated_on'              => $current_time->toDateTimeString(),
                    'created_on'              => '2010-01-01 01:01:01',
                    'right_to_left'           => 'false',
                    'num_art'                 => '0',
                    'num_sample_audio'        => '0',
                    'sku'                     => '',
                    'audio_zip_path'          => $dam_id.'/'.$dam_id.'.zip',
                    'font'                    => null,
                    'arclight_language_id'    => $arclight_language_id,
                    'media'                   => 'video',
                    'media_type'              => 'Drama',
                ];
            }
            return $jesusFilms;
        });

    }

    /**
     * Fetches and returns
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dam_id = checkParam('dam_id');
        $iso    = substr($dam_id, 0, 3);
        $platform = checkParam('platform') ?? 'ios';

        $chapters = \Cache::remember('arclight_'. strtolower($iso), now()->addDay(), function () use ($iso, $platform) {
            $languages = collect($this->fetch('media-languages')->mediaLanguages)->pluck('languageId', 'iso3');
            $language_id = $languages[strtolower($iso)];
            if (!$language_id) {
                return $this->setStatusCode(404)->replyWithError(trans('api.languages_errors_404'));
            }

            $ids = ['1_jf6101-0-0','1_jf6102-0-0','1_jf6103-0-0','1_jf6104-0-0','1_jf6105-0-0','1_jf6106-0-0','1_jf6107-0-0','1_jf6108-0-0','1_jf6109-0-0','1_jf6110-0-0','1_jf6111-0-0','1_jf6112-0-0','1_jf6113-0-0','1_jf6114-0-0','1_jf6115-0-0','1_jf6116-0-0','1_jf6117-0-0','1_jf6118-0-0','1_jf6119-0-0','1_jf6120-0-0','1_jf6121-0-0','1_jf6122-0-0','1_jf6123-0-0','1_jf6124-0-0','1_jf6125-0-0','1_jf6126-0-0','1_jf6127-0-0','1_jf6128-0-0','1_jf6129-0-0','1_jf6130-0-0','1_jf6131-0-0','1_jf6132-0-0','1_jf6133-0-0','1_jf6134-0-0','1_jf6135-0-0','1_jf6136-0-0','1_jf6137-0-0','1_jf6138-0-0','1_jf6139-0-0','1_jf6140-0-0','1_jf6141-0-0','1_jf6142-0-0','1_jf6143-0-0','1_jf6144-0-0','1_jf6145-0-0','1_jf6146-0-0','1_jf6147-0-0','1_jf6148-0-0','1_jf6149-0-0','1_jf6150-0-0','1_jf6151-0-0','1_jf6152-0-0','1_jf6153-0-0','1_jf6154-0-0','1_jf6155-0-0','1_jf6156-0-0','1_jf6157-0-0','1_jf6158-0-0','1_jf6159-0-0','1_jf6160-0-0','1_jf6161-0-0'];
            $components = $this->fetch('media-components/', ['platform' => $platform,'ids' => implode(',', $ids),'languageIds' => $language_id]);
            $components = $components->mediaComponents;

            foreach ($components as $key => $component) {
                $component->language_id = $language_id;
                $component->file_name = route('v2_api_jesusFilm_stream', [
                    'id'          => $component->mediaComponentId,
                    'language_id' => $language_id,
                    'v'           => $this->v,
                    'key'         => $this->key
                ]);
            }
            return $components;
        });

        return $this->reply(fractal($chapters, new ArclightTransformer(), new ArraySerializer()));
    }

    public function chapter($chapter_id)
    {
        $language_id = checkParam('language_id');
        $platform = checkParam('platform') ?? 'ios';

        $media_components = $this->fetch('media-components/'.$chapter_id.'/languages/'.$language_id, ['platform' => $platform]);

        $current_file = "#EXTM3U\n";
        $current_file .= "#EXTINF:\n".$media_components->streamingUrls->m3u8[0]->url;

        return response($current_file, 200)->header('Content-Disposition', 'attachment; filename="'.'"')->header('Content-Type', 'application/x-mpegURL');
    }

    public function sync()
    {
        if (!file_exists(storage_path('data/jfm/languages'))) {
            mkdir(storage_path('data/jfm/languages'), 0777, true);
        }
        if (!file_exists(storage_path('data/jfm/feature-films'))) {
            mkdir(storage_path('data/jfm/feature-films'), 0777, true);
        }

        $this->syncLanguages();
        $this->syncTypes();
    }

    private function syncTypes()
    {
        $media_components = $this->fetch('media-components');
        foreach ($media_components->mediaComponents as $component) {
            $output[$component->subType][$component->mediaComponentId] = $component->title;
        }
        file_put_contents(storage_path('/data/jfm/types.json'), json_encode(collect($output), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function syncLanguages()
    {
        $languages = collect($this->fetch('media-languages')->mediaLanguages)->pluck('languageId', 'iso3');
        file_put_contents(storage_path('/data/jfm/languages.json'), json_encode(collect($languages), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function fetch($path, array $params = [])
    {
        $paramString = '';
        foreach ($params as $key => $value) {
            $paramString .= '&' . $key . '=' . $value;
        }
        $path = $this->base_url . $path . '?_format=json&apiKey=' . $this->api_key . '&limit=3000' . $paramString;

        $results = json_decode(file_get_contents($path));
        if (isset($results->_embedded)) {
            return $results->_embedded;
        }
        return $results;
    }

    private function fetchLocal($path)
    {
        return json_decode(file_get_contents(storage_path("/data/jfm/$path")), true);
    }
}
